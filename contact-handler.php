<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/config/config.php';

require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

// Detect form type: off-list quote request vs generic contact
$isQuoteRequest = !empty($input['fromLocation']) || !empty($input['toLocation']);

// Both form types require name and email
if (empty($input['name']) || empty($input['email'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Name and email are required']);
    exit;
}

// Generic contact form also requires message
if (!$isQuoteRequest && empty($input['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Name, email, and message are required']);
    exit;
}

// Sanitize inputs
$name = htmlspecialchars($input['name']);
$email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
$phone = htmlspecialchars($input['phone'] ?? 'Not provided');
$message = htmlspecialchars($input['message'] ?? '');

// Quote-request specific fields
$fromLocation = htmlspecialchars($input['fromLocation'] ?? '');
$toLocation = htmlspecialchars($input['toLocation'] ?? '');
$requestedDate = htmlspecialchars($input['requestedDate'] ?? $input['date'] ?? '');
$requestedTime = htmlspecialchars($input['requestedTime'] ?? $input['timeSlot'] ?? '');
$tripType = htmlspecialchars($input['tripType'] ?? '');
$notes = htmlspecialchars($input['notes'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

// Build email subject and body based on form type
if ($isQuoteRequest) {
    $subject = 'Custom Quote Request - ' . $name;
    $routeRow = $fromLocation && $toLocation
        ? '<p><strong>From:</strong> ' . $fromLocation . '</p><p><strong>To:</strong> ' . $toLocation . '</p>'
        : '';
    $dateRow = $requestedDate ? '<p><strong>Date:</strong> ' . $requestedDate . '</p>' : '';
    $timeRow = $requestedTime ? '<p><strong>Time:</strong> ' . $requestedTime . '</p>' : '';
    $tripTypeRow = $tripType ? '<p><strong>Trip Type:</strong> ' . $tripType . '</p>' : '';
    $notesRow = $notes ? '<p><strong>Notes / Passenger count:</strong> ' . nl2br($notes) . '</p>' : '';

    $htmlBody = '
    <!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"></head>
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background-color: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;">
            <h1 style="color: white; margin: 0;">Custom Quote Request</h1>
            <p style="color: white; margin: 5px 0 0 0;">Direct Car Service Website</p>
        </div>
        <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;">
            <div style="background: #e3f2fd; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #2196F3;">
                <h3 style="margin-top: 0;">Contact Information</h3>
                <p><strong>Name:</strong> ' . $name . '</p>
                <p><strong>Email:</strong> ' . $email . '</p>
                <p><strong>Phone:</strong> ' . $phone . '</p>
            </div>
            <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4CAF50;">
                <h3 style="margin-top: 0;">Requested Route</h3>
                ' . $routeRow . $dateRow . $timeRow . $tripTypeRow . '
            </div>
            ' . ($notesRow ? '<div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #ff9800;">
                <h3 style="margin-top: 0;">Notes</h3>
                ' . $notesRow . '
            </div>' : '') . '
            <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; color: #666;">
                <p>Received: ' . date('M j, Y g:i A') . '</p>
            </div>
        </div>
    </body>
    </html>';

    $altBody = "Custom Quote Request\n\nName: $name\nEmail: $email\nPhone: $phone\n\nFrom: $fromLocation\nTo: $toLocation\nDate: $requestedDate\nTime: $requestedTime\nTrip Type: $tripType\nNotes: $notes";
} else {
    $subject = 'New Contact Form Message - ' . $name;
    $htmlBody = '
    <!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"></head>
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background-color: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;">
            <h1 style="color: white; margin: 0;">New Contact Message</h1>
            <p style="color: white; margin: 5px 0 0 0;">Direct Car Service Website</p>
        </div>
        <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;">
            <div style="background: #e3f2fd; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #2196F3;">
                <h3 style="margin-top: 0;">Contact Information</h3>
                <p><strong>Name:</strong> ' . $name . '</p>
                <p><strong>Email:</strong> ' . $email . '</p>
                <p><strong>Phone:</strong> ' . $phone . '</p>
            </div>
            <div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #4CAF50;">
                <h3 style="margin-top: 0;">Message</h3>
                <p>' . nl2br($message) . '</p>
            </div>
            <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; color: #666;">
                <p>Received: ' . date('M j, Y g:i A') . '</p>
            </div>
        </div>
    </body>
    </html>';

    $altBody = "New Contact Message\n\nName: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message";
}

try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = SMTP_PORT;
    $mail->CharSet = 'UTF-8';

    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    $mail->setFrom(COMPANY_EMAIL, COMPANY_NAME);
    $mail->addAddress(DISPATCH_EMAIL);
    $mail->addReplyTo($email, $name);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $htmlBody;
    $mail->AltBody = $altBody;

    $mail->send();

    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully'
    ]);

} catch (Exception $e) {
    error_log("Contact form email failed: " . $mail->ErrorInfo);
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to send message. Please try again or call us directly.'
    ]);
}
?>
