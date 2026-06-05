<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Load configuration
require_once __DIR__ . '/config/config.php';

// Control display errors based on debug mode
ini_set('display_errors', DEBUG_MODE ? 1 : 0);

// Load PHPMailer
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// SMTP Configuration from config file
$smtp_config = [
    'primary' => [
        'host' => SMTP_HOST,
        'port' => SMTP_PORT,
        'username' => SMTP_USERNAME,
        'password' => SMTP_PASSWORD,
        'encryption' => PHPMailer::ENCRYPTION_SMTPS
    ],
    'backup' => [
        'host' => BACKUP_SMTP_HOST,
        'port' => BACKUP_SMTP_PORT,
        'username' => BACKUP_SMTP_USERNAME,
        'password' => BACKUP_SMTP_PASSWORD,
        'encryption' => PHPMailer::ENCRYPTION_STARTTLS
    ]
];

// Custom logging function
function writeLog($message, $type = 'info') {
    $logFile = __DIR__ . '/email_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$type] $message" . PHP_EOL;
    
    // Ensure log directory is writable
    if (!is_writable(dirname($logFile))) {
        // Try to make it writable
        @chmod(dirname($logFile), 0755);
    }
    
    // Write to log file
    @file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    
    // Also write to error log
    error_log($logMessage);
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Log incoming request
writeLog("Request received: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);

$input = json_decode(file_get_contents('php://input'), true);
writeLog("Input data: " . json_encode($input));

if (!$input || !isset($input['action'])) {
    writeLog("Invalid input data", 'error');
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

switch($input['action']) {
    case 'create_payment_intent':
        createPaymentIntent($input);
        break;
    
    case 'send_confirmation':
        sendConfirmationEmails($input);
        break;
    
    default:
        writeLog("Invalid action: " . $input['action'], 'error');
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function createPaymentIntent($input) {
    writeLog("Creating payment intent for amount: " . $input['amount']);
    
    $amount = intval(round($input['amount'] * 100)); // Convert to cents as integer
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . STRIPE_SECRET_KEY,
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'amount' => $amount,
        'currency' => 'usd',
        'description' => 'Direct Car Service - Booking ' . $input['bookingRef']
    ]));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        writeLog("Stripe API error: " . $error, 'error');
    } else {
        writeLog("Stripe API response (HTTP $httpCode): " . $response);
    }
    
    echo $response;
}

function sendConfirmationEmails($input) {
    global $smtp_config;
    
    writeLog("Starting email confirmation process");
    
    $bookingData = $input['bookingData'];
    
    // Format data
    $travelDate = date('l, F j, Y', strtotime($bookingData['date']));
    $timeSlots = [
        'morning' => '6:00 AM - 12:00 PM',
        'afternoon' => '12:00 PM - 6:00 PM',
        'evening' => '6:00 PM - 12:00 AM',
        'late' => '12:00 AM - 6:00 AM'
    ];
    $timeSlot = $timeSlots[$bookingData['timeSlot']] ?? $bookingData['timeSlot'];
    
    $vehicles = [
        'full_size_car' => 'Full Size Car (4 passengers)',
        'standard_suv' => 'Standard SUV (5 passengers)',
        'full_size_suv' => 'Full Size/Premium SUV (7-8 passengers)',
        'minivan' => 'Minivan (7 passengers)',
        'sprinter' => 'Sprinter (Large group)'
    ];
    $vehicleName = $vehicles[$bookingData['vehicleType']] ?? $bookingData['vehicleType'];
    
    $paymentStatus = $bookingData['paymentMethod'] === 'card' 
        ? 'Paid Online (Card)' 
        : 'Pay Driver (Cash/Card)';
    
    $totalAmount = $bookingData['paymentMethod'] === 'card' 
        ? $bookingData['totalWithFee'] 
        : $bookingData['totalPrice'];
    
    // Send emails
    writeLog("Sending customer email to: " . $bookingData['customerEmail']);
    $customerEmailSent = sendEmail(
        $bookingData['customerEmail'],
        'Booking Confirmation - ' . COMPANY_NAME . ' #' . $bookingData['bookingReference'],
        getCustomerEmailBody($bookingData, $travelDate, $timeSlot, $vehicleName, $paymentStatus, $totalAmount),
        $smtp_config
    );
    
    writeLog("Sending dispatch email to: " . DISPATCH_EMAIL);
    $dispatchEmailSent = sendEmail(
        DISPATCH_EMAIL,
        'New Booking - #' . $bookingData['bookingReference'] . ' - ' . $bookingData['customerName'],
        getDispatchEmailBody($bookingData, $travelDate, $timeSlot, $vehicleName, $paymentStatus, $totalAmount),
        $smtp_config
    );
    
    $response = [
        'success' => $customerEmailSent || $dispatchEmailSent,
        'customerEmail' => $customerEmailSent,
        'dispatchEmail' => $dispatchEmailSent,
        'message' => !$customerEmailSent || !$dispatchEmailSent 
            ? 'Some emails could not be sent, but booking was completed.' 
            : 'All emails sent successfully.',
        'debug' => DEBUG_MODE ? [
            'logFile' => __DIR__ . '/email_debug.log',
            'errorLogFile' => __DIR__ . '/php_errors.log'
        ] : null
    ];
    
    writeLog("Email send results: " . json_encode($response));
    echo json_encode($response);
}

function sendEmail($to, $subject, $body, $smtp_config) {
    $mail = new PHPMailer(true);
    
    // Enable debugging in debug mode
    if (DEBUG_MODE) {
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->Debugoutput = function($str, $level) {
            writeLog("PHPMailer Debug: $str", 'debug');
        };
    }
    
    // Try primary SMTP first
    try {
        writeLog("Attempting primary SMTP to $to");
        configureMailer($mail, $smtp_config['primary']);
        $mail->setFrom(DISPATCH_EMAIL, COMPANY_NAME);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body = $body;
        
        // Add text alternative
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));
        
        $mail->send();
        writeLog("Email sent successfully via primary SMTP to $to");
        return true;
    } catch (Exception $e) {
        // Log primary failure
        writeLog("Primary SMTP failed: " . $mail->ErrorInfo . " - Exception: " . $e->getMessage(), 'error');
        
        // Try backup Gmail SMTP
        $mail = new PHPMailer(true); // Create new instance
        
        if (DEBUG_MODE) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->Debugoutput = function($str, $level) {
                writeLog("PHPMailer Debug (Backup): $str", 'debug');
            };
        }
        
        try {
            writeLog("Attempting backup Gmail SMTP to $to");
            configureMailer($mail, $smtp_config['backup']);
            $mail->setFrom($smtp_config['backup']['username'], COMPANY_NAME . ' (Backup)');
            $mail->addAddress($to);
            $mail->addReplyTo(DISPATCH_EMAIL);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $body;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));
            
            $mail->send();
            writeLog("Email sent successfully via backup SMTP to $to");
            return true;
        } catch (Exception $e) {
            // Log backup failure
            writeLog("Backup SMTP also failed: " . $mail->ErrorInfo . " - Exception: " . $e->getMessage(), 'error');
            logFailedEmail($to, $subject, $body);
            return false;
        }
    }
}

function configureMailer($mail, $config) {
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->SMTPSecure = $config['encryption'];
    $mail->Port = $config['port'];
    $mail->CharSet = 'UTF-8';
    
    // Additional settings for better compatibility
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    // Timeout settings
    $mail->Timeout = 30;
}

function logFailedEmail($to, $subject, $body) {
    $logFile = __DIR__ . '/failed_emails.log';
    $logEntry = date('Y-m-d H:i:s') . " | To: $to | Subject: $subject\n";
    $logEntry .= "Body preview: " . substr(strip_tags($body), 0, 200) . "...\n";
    $logEntry .= str_repeat('-', 80) . "\n";
    
    @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    writeLog("Failed email logged to $logFile");
}

function getCustomerEmailBody($bookingData, $travelDate, $timeSlot, $vehicleName, $paymentStatus, $totalAmount) {
    return '<!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">
        <div style="max-width: 600px; margin: 0 auto; background: #ffffff;">
            <div style="background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; padding: 30px; text-align: center;">
                <h1 style="margin: 0; font-size: 24px;">Booking Confirmed!</h1>
                <p style="margin: 10px 0 0 0; font-size: 16px;">Reference: #' . htmlspecialchars($bookingData['bookingReference']) . '</p>
            </div>
            <div style="padding: 30px;">
                <p style="font-size: 16px;">Dear ' . htmlspecialchars($bookingData['customerName']) . ',</p>
                <p>Thank you for choosing ' . COMPANY_NAME . '. Your booking has been confirmed and our dispatch team will contact you shortly.</p>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;">
                    <h3 style="color: #4CAF50; margin-top: 0;">Trip Details</h3>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr><td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><strong>From:</strong></td><td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">' . htmlspecialchars($bookingData['pickup']) . '</td></tr>
                        <tr><td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><strong>To:</strong></td><td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">' . htmlspecialchars($bookingData['dropoff']) . '</td></tr>
                        <tr><td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><strong>Date:</strong></td><td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">' . htmlspecialchars($travelDate) . '</td></tr>
                        <tr><td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><strong>Time:</strong></td><td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">' . htmlspecialchars($timeSlot) . '</td></tr>
                        <tr><td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><strong>Vehicle:</strong></td><td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">' . htmlspecialchars($vehicleName) . '</td></tr>
                        <tr><td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;"><strong>Trip Type:</strong></td><td style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">' . htmlspecialchars($bookingData['tripType']) . '</td></tr>
                        <tr><td style="padding: 8px 0;"><strong>Payment:</strong></td><td style="padding: 8px 0;">' . htmlspecialchars($paymentStatus) . '</td></tr>
                    </table>
                </div>
                <div style="background: #4CAF50; color: white; padding: 15px; border-radius: 10px; text-align: center; margin: 20px 0;">
                    <span style="font-size: 14px;">Total Amount</span><br>
                    <span style="font-size: 28px; font-weight: bold;">$' . number_format($totalAmount, 2) . '</span>
                </div>
                <div style="background: #e8f5e9; padding: 15px; border-radius: 10px; margin: 20px 0;">
                    <h4 style="color: #2e7d32; margin-top: 0;">Need to Contact Us?</h4>
                    <p style="margin: 5px 0;"><strong>Phone:</strong> ' . COMPANY_PHONE_1 . ' | ' . COMPANY_PHONE_2 . '</p>
                    <p style="margin: 5px 0;"><strong>Email:</strong> ' . COMPANY_EMAIL . '</p>
                </div>
                <p style="font-size: 12px; color: #666;">Please save your booking reference for future correspondence.</p>
            </div>
            <div style="background: #2c3e50; color: white; padding: 20px; text-align: center;">
                <p style="margin: 0; font-size: 14px;">' . COMPANY_NAME . '</p>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #bbb;">Reliable & Professional Transportation</p>
            </div>
        </div>
    </body>
    </html>';
}

function getDispatchEmailBody($bookingData, $travelDate, $timeSlot, $vehicleName, $paymentStatus, $totalAmount) {
    $extraStops = isset($bookingData['extraStops']) && $bookingData['extraStops'] > 0
        ? $bookingData['extraStops'] . ' stop(s)'
        : 'None';

    return '<!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"></head>
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">
        <div style="max-width: 600px; margin: 0 auto; background: #ffffff;">
            <div style="background: #2c3e50; color: white; padding: 20px; text-align: center;">
                <h2 style="margin: 0;">NEW BOOKING</h2>
                <p style="margin: 5px 0 0 0; font-size: 18px;">#' . htmlspecialchars($bookingData['bookingReference']) . '</p>
            </div>
            <div style="padding: 20px;">
                <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <strong>Action Required:</strong> Contact customer within 15 minutes to confirm pickup.
                </div>
                <h3 style="color: #4CAF50; border-bottom: 2px solid #4CAF50; padding-bottom: 10px;">Customer Information</h3>
                <table style="width: 100%;">
                    <tr><td><strong>Name:</strong></td><td>' . htmlspecialchars($bookingData['customerName']) . '</td></tr>
                    <tr><td><strong>Phone:</strong></td><td><a href="tel:' . htmlspecialchars($bookingData['customerPhone']) . '">' . htmlspecialchars($bookingData['customerPhone']) . '</a></td></tr>
                    <tr><td><strong>Email:</strong></td><td><a href="mailto:' . htmlspecialchars($bookingData['customerEmail']) . '">' . htmlspecialchars($bookingData['customerEmail']) . '</a></td></tr>
                </table>
                <h3 style="color: #4CAF50; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; margin-top: 25px;">Trip Details</h3>
                <table style="width: 100%;">
                    <tr><td><strong>Pickup:</strong></td><td>' . htmlspecialchars($bookingData['pickup']) . '</td></tr>
                    <tr><td><strong>Dropoff:</strong></td><td>' . htmlspecialchars($bookingData['dropoff']) . '</td></tr>
                    <tr><td><strong>Date:</strong></td><td>' . htmlspecialchars($travelDate) . '</td></tr>
                    <tr><td><strong>Time:</strong></td><td>' . htmlspecialchars($timeSlot) . '</td></tr>
                    <tr><td><strong>Trip Type:</strong></td><td>' . htmlspecialchars($bookingData['tripType']) . '</td></tr>
                    <tr><td><strong>Vehicle:</strong></td><td>' . htmlspecialchars($vehicleName) . '</td></tr>
                    <tr><td><strong>Extra Stops:</strong></td><td>' . htmlspecialchars($extraStops) . '</td></tr>
                </table>
                <h3 style="color: #4CAF50; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; margin-top: 25px;">Payment</h3>
                <table style="width: 100%;">
                    <tr><td><strong>Status:</strong></td><td>' . htmlspecialchars($paymentStatus) . '</td></tr>
                    <tr><td><strong>Total:</strong></td><td style="font-size: 20px; font-weight: bold; color: #4CAF50;">$' . number_format($totalAmount, 2) . '</td></tr>
                </table>
                <p style="margin-top: 20px; font-size: 12px; color: #666;">Booking submitted: ' . date('Y-m-d H:i:s') . '</p>
            </div>
        </div>
    </body>
    </html>';
}
?>