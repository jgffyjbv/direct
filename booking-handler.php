<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Load configuration
require_once __DIR__ . '/config/config.php';

// Load PHPMailer
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

// Function to send email using PHPMailer
function sendBookingEmail($to, $subject, $body, $isHtml = true) {
    $mail = new PHPMailer(true);

    try {
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
        $mail->addAddress($to);

        $mail->isHTML($isHtml);
        $mail->Subject = $subject;
        $mail->Body = $body;
        if ($isHtml) {
            $mail->AltBody = strip_tags($body);
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

// Generate customer confirmation email
function generateCustomerEmail($bookingData) {
    $paymentStatus = ($bookingData['paymentMethod'] === 'card') ? 'PAID' : 'PAY DRIVER';
    $paymentBadgeColor = ($bookingData['paymentMethod'] === 'card') ? '#28a745' : '#ffc107';

    $vehicleTypes = [
        'full_size_car' => 'Full Size Car (4 passengers)',
        'standard_suv' => 'Standard SUV (5 passengers)',
        'full_size_suv' => 'Full Size/Premium SUV (7/8 passengers)',
        'minivan' => 'Minivan (7 passengers)',
        'sprinter' => 'Sprinter (Large group)'
    ];

    $vehicleName = isset($vehicleTypes[$bookingData['vehicleType']]) ? $vehicleTypes[$bookingData['vehicleType']] : $bookingData['vehicleType'];
    $totalPrice = isset($bookingData['totalWithFee']) ? $bookingData['totalWithFee'] : $bookingData['totalPrice'];

    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .booking-ref { background: #4CAF50; color: white; padding: 15px; border-radius: 8px; text-align: center; margin: 20px 0; font-size: 18px; font-weight: bold; }
            .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
            .detail-item { background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #4CAF50; }
            .detail-label { font-weight: bold; color: #2c3e50; margin-bottom: 5px; }
            .detail-value { color: #666; }
            .payment-badge { display: inline-block; background: ' . $paymentBadgeColor . '; color: white; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; }
            .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; }
            @media (max-width: 600px) { .details-grid { grid-template-columns: 1fr; } }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header" style="background-color: #4CAF50; background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
                <h1 style="color: white; margin: 0 0 10px 0;">Booking Confirmation</h1>
                <p style="color: white; margin: 0;">Direct Car Service - Professional Transportation</p>
            </div>

            <div class="content">
                <div class="booking-ref" style="background-color: #4CAF50; color: white; padding: 15px; border-radius: 8px; text-align: center; margin: 20px 0; font-size: 18px; font-weight: bold;">
                    Booking Reference: ' . htmlspecialchars($bookingData['bookingReference']) . '
                </div>

                <p>Dear ' . htmlspecialchars($bookingData['customerName']) . ',</p>

                <p>Thank you for choosing Direct Car Service! Your reservation has been confirmed and our dispatch team has been notified.</p>

                <h3>Trip Details:</h3>
                <div class="details-grid">
                    <div class="detail-item">
                        <div class="detail-label">From</div>
                        <div class="detail-value">' . htmlspecialchars($bookingData['pickup']) . '</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">To</div>
                        <div class="detail-value">' . htmlspecialchars($bookingData['dropoff']) . '</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Date & Time</div>
                        <div class="detail-value">' . htmlspecialchars($bookingData['date']) . ' (' . ucfirst($bookingData['timeSlot']) . ')</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Vehicle</div>
                        <div class="detail-value">' . htmlspecialchars($vehicleName) . '</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Trip Type</div>
                        <div class="detail-value">' . ucfirst($bookingData['tripType']) . '</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Extra Stops</div>
                        <div class="detail-value">' . (isset($bookingData['extraStops']) ? $bookingData['extraStops'] : 0) . '</div>
                    </div>
                </div>

                ' . (isset($bookingData['distance']) ? '<div class="detail-item" style="margin: 20px 0;"><div class="detail-label">Distance & Duration</div><div class="detail-value">' . $bookingData['distance'] . ' miles - ' . $bookingData['duration'] . '</div></div>' : '') . '

                <h3>Payment Information:</h3>
                <div style="background: white; padding: 20px; border-radius: 8px; margin: 15px 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span>Payment Status:</span>
                        <span class="payment-badge">' . $paymentStatus . '</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: bold; color: #4CAF50;">
                        <span>Total Fare:</span>
                        <span>$' . number_format($totalPrice, 2) . '</span>
                    </div>
                </div>

                <h3>What Happens Next:</h3>
                <ul>
                    <li>Your booking has been confirmed</li>
                    <li>Your driver will contact you before the trip to confirm the details</li>
                    <li>Please be ready at your pickup location on time</li>
                    <li>Any changes? Call us at 845-642-1317</li>
                </ul>

                <div style="background: #e8f5e8; border-left: 4px solid #4CAF50; padding: 15px; margin: 20px 0;">
                    <strong>Important:</strong> Please save this booking reference number and be ready at your pickup location 5 minutes before the scheduled time.
                </div>

                <div class="footer">
                    <p><strong>Direct Car Service</strong><br>
                    845-642-1317 | 845-306-6055<br>
                    info@directcarservice.com<br>
                    Professional & Reliable Transportation</p>
                </div>
            </div>
        </div>
    </body>
    </html>';
}

// Generate dispatch notification email (casual style like customer email)
function generateDispatchEmail($bookingData) {
    $paymentStatus = ($bookingData['paymentMethod'] === 'card') ? 'PAID' : 'COLLECT FROM CUSTOMER';
    $paymentBadgeColor = ($bookingData['paymentMethod'] === 'card') ? '#28a745' : '#ffc107';

    $vehicleTypes = [
        'full_size_car' => 'Full Size Car (4 passengers)',
        'standard_suv' => 'Standard SUV (5 passengers)',
        'full_size_suv' => 'Full Size/Premium SUV (7/8 passengers)',
        'minivan' => 'Minivan (7 passengers)',
        'sprinter' => 'Sprinter (Large group)'
    ];

    $vehicleName = isset($vehicleTypes[$bookingData['vehicleType']]) ? $vehicleTypes[$bookingData['vehicleType']] : $bookingData['vehicleType'];
    $totalPrice = isset($bookingData['totalWithFee']) ? $bookingData['totalWithFee'] : $bookingData['totalPrice'];

    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .booking-ref { background: #4CAF50; color: white; padding: 15px; border-radius: 8px; text-align: center; margin: 20px 0; font-size: 18px; font-weight: bold; }
            .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
            .detail-item { background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #4CAF50; }
            .detail-label { font-weight: bold; color: #2c3e50; margin-bottom: 5px; }
            .detail-value { color: #666; }
            .payment-badge { display: inline-block; background: ' . $paymentBadgeColor . '; color: white; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; }
            .customer-section { background: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #2196F3; }
            .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; }
            @media (max-width: 600px) { .details-grid { grid-template-columns: 1fr; } }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header" style="background-color: #4CAF50; background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
                <h1 style="color: white; margin: 0 0 10px 0;">New Booking Received</h1>
                <p style="color: white; margin: 0;">Direct Car Service Dispatch</p>
            </div>

            <div class="content">
                <div class="booking-ref" style="background-color: #4CAF50; color: white; padding: 15px; border-radius: 8px; text-align: center; margin: 20px 0; font-size: 18px; font-weight: bold;">
                    Booking Reference: ' . htmlspecialchars($bookingData['bookingReference']) . '
                </div>

                <div class="customer-section" style="background-color: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #2196F3;">
                    <h3>Customer Information</h3>
                    <p><strong>Name:</strong> ' . htmlspecialchars($bookingData['customerName']) . '</p>
                    <p><strong>Phone:</strong> ' . htmlspecialchars($bookingData['customerPhone']) . '</p>
                    <p><strong>Email:</strong> ' . htmlspecialchars($bookingData['customerEmail']) . '</p>
                </div>

                <h3>Trip Details:</h3>
                <div class="details-grid">
                    <div class="detail-item">
                        <div class="detail-label">From</div>
                        <div class="detail-value">' . htmlspecialchars($bookingData['pickup']) . '</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">To</div>
                        <div class="detail-value">' . htmlspecialchars($bookingData['dropoff']) . '</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Date & Time</div>
                        <div class="detail-value">' . htmlspecialchars($bookingData['date']) . ' (' . ucfirst($bookingData['timeSlot']) . ')</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Vehicle</div>
                        <div class="detail-value">' . htmlspecialchars($vehicleName) . '</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Trip Type</div>
                        <div class="detail-value">' . ucfirst($bookingData['tripType']) . '</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Extra Stops</div>
                        <div class="detail-value">' . (isset($bookingData['extraStops']) ? $bookingData['extraStops'] : 0) . '</div>
                    </div>
                </div>

                ' . (isset($bookingData['distance']) ? '<div class="detail-item" style="margin: 20px 0;"><div class="detail-label">Distance & Duration</div><div class="detail-value">' . $bookingData['distance'] . ' miles - ' . $bookingData['duration'] . '</div></div>' : '') . '

                <h3>Payment Information:</h3>
                <div style="background: white; padding: 20px; border-radius: 8px; margin: 15px 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span>Payment Status:</span>
                        <span class="payment-badge">' . $paymentStatus . '</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: bold; color: #4CAF50;">
                        <span>Total Fare:</span>
                        <span>$' . number_format($totalPrice, 2) . '</span>
                    </div>
                    ' . ($bookingData['paymentMethod'] === 'card' ? '<p style="color: #28a745; margin-top: 10px;">Payment already processed - no collection needed</p>' : '<p style="color: #856404; margin-top: 10px;">Please collect $' . number_format($totalPrice, 2) . ' from customer</p>') . '
                </div>

                ' . (!empty($bookingData['pickupNotes']) ? '<div style="background: #e8f5e8; border-left: 4px solid #4CAF50; padding: 15px; margin: 20px 0;"><strong>Pickup Notes from Customer:</strong><p style="margin: 8px 0 0 0;">' . htmlspecialchars($bookingData['pickupNotes']) . '</p></div>' : '') . '

                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
                    <strong>Next Steps:</strong>
                    <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                        <li>Assign a driver to this booking</li>
                        <li>Contact customer to confirm details</li>
                    </ul>
                </div>

                <div class="footer">
                    <p><strong>Direct Car Service Dispatch</strong><br>
                    Booking received: ' . date('M j, Y g:i A') . '</p>
                </div>
            </div>
        </div>
    </body>
    </html>';
}

// Store booking data to file
function storeBooking($bookingData) {
    $bookingData['timestamp'] = date('Y-m-d H:i:s');
    $bookingData['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $bookingLine = date('Y-m-d H:i:s') . " | " .
                   $bookingData['bookingReference'] . " | " .
                   $bookingData['customerName'] . " | " .
                   $bookingData['customerPhone'] . " | " .
                   $bookingData['pickup'] . " -> " .
                   $bookingData['dropoff'] . " | " .
                   "$" . number_format($bookingData['totalPrice'], 2) . " | " .
                   $bookingData['paymentMethod'] . "\n";

    file_put_contents('bookings.log', $bookingLine, FILE_APPEND | LOCK_EX);
    file_put_contents('bookings/' . $bookingData['bookingReference'] . '.json', json_encode($bookingData, JSON_PRETTY_PRINT));
}

// Main processing
try {
    // Validate required fields
    $required_fields = ['bookingReference', 'customerName', 'customerEmail', 'customerPhone', 'pickup', 'dropoff', 'date', 'timeSlot', 'tripType', 'vehicleType', 'totalPrice', 'paymentMethod'];

    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Create bookings directory if it doesn't exist
    if (!file_exists('bookings')) {
        mkdir('bookings', 0755, true);
    }

    // Store booking
    storeBooking($input);

    // Generate emails
    $customerEmail = generateCustomerEmail($input);
    $dispatchEmail = generateDispatchEmail($input);

    // Send customer confirmation
    $customerSent = sendBookingEmail(
        $input['customerEmail'],
        'Booking Confirmation - Direct Car Service (Ref: ' . $input['bookingReference'] . ')',
        $customerEmail,
        true
    );

    // Send dispatch notification
    $dispatchSent = sendBookingEmail(
        DISPATCH_EMAIL,
        'New Booking - ' . $input['bookingReference'] . ' - $' . number_format($input['totalPrice'], 2),
        $dispatchEmail,
        true
    );

    // Response
    echo json_encode([
        'success' => true,
        'message' => 'Booking processed successfully',
        'bookingReference' => $input['bookingReference'],
        'emails' => [
            'customer' => $customerSent,
            'dispatch' => $dispatchSent
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Booking processing failed: ' . $e->getMessage()
    ]);
}
?>
