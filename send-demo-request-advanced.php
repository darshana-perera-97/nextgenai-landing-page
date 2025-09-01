<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: demo-request.html');
    exit;
}

// Check if PHPMailer is available
if (!file_exists('vendor/autoload.php')) {
    // Fallback to basic mail() function
    require_once 'send-demo-request.php';
    exit;
}

// Load PHPMailer
require_once 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Get form data
$firstName = $_POST['firstName'] ?? '';
$lastName = $_POST['lastName'] ?? '';
$email = $_POST['email'] ?? '';
$company = $_POST['company'] ?? '';
$phone = $_POST['phone'] ?? '';
$website = $_POST['website'] ?? '';
$preferredDate = $_POST['preferredDate'] ?? '';
$preferredTime = $_POST['preferredTime'] ?? '';
$message = $_POST['message'] ?? '';

// Validate required fields
if (empty($firstName) || empty($lastName) || empty($email) || empty($company) || empty($preferredDate) || empty($preferredTime)) {
    header('Location: demo-request.html?error=missing_fields');
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: demo-request.html?error=invalid_email');
    exit;
}

// Sanitize inputs
$firstName = htmlspecialchars(trim($firstName));
$lastName = htmlspecialchars(trim($firstName));
$email = htmlspecialchars(trim($email));
$company = htmlspecialchars(trim($company));
$phone = htmlspecialchars(trim($phone));
$website = htmlspecialchars(trim($website));
$preferredDate = htmlspecialchars(trim($preferredDate));
$preferredTime = htmlspecialchars(trim($preferredTime));
$message = htmlspecialchars(trim($message));

// Email configuration
$to_sales = 'sales@nexgenai.com';
$to_demo = 'demo@nexgenai.com';
$subject = 'New Demo Request from ' . $firstName . ' ' . $lastName;

// Create email content functions
function createSalesEmailContent($firstName, $lastName, $email, $company, $phone, $website, $preferredDate, $preferredTime, $message) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>New Demo Request</title>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #5B42F3; border-bottom: 2px solid #5B42F3; padding-bottom: 10px;'>
                New Demo Request Received
            </h2>
            
            <div style='background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='color: #333; margin-top: 0;'>Contact Information</h3>
                <p><strong>Name:</strong> {$firstName} {$lastName}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Company:</strong> {$company}</p>
                <p><strong>Phone:</strong> " . ($phone ?: 'Not provided') . "</p>
                <p><strong>Website:</strong> " . ($website ?: 'Not provided') . "</p>
            </div>
            
            <div style='background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='color: #333; margin-top: 0;'>Demo Preferences</h3>
                <p><strong>Preferred Date:</strong> {$preferredDate}</p>
                <p><strong>Preferred Time:</strong> {$preferredTime}</p>
            </div>
            
            " . ($message ? "
            <div style='background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='color: #333; margin-top: 0;'>Additional Requirements</h3>
                <p>" . nl2br($message) . "</p>
            </div>
            " : "") . "
            
            <div style='margin-top: 30px; padding: 20px; background: #5B42F3; color: white; border-radius: 8px; text-align: center;'>
                <p style='margin: 0;'><strong>Action Required:</strong> Please contact this prospect to schedule their demo.</p>
            </div>
            
            <div style='margin-top: 20px; text-align: center; color: #666; font-size: 12px;'>
                <p>This email was sent from the NexGenAI demo request form.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

function createDemoEmailContent($firstName, $lastName, $email, $company, $preferredDate, $preferredTime, $message) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>New Demo Request</title>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #5B42F3; border-bottom: 2px solid #5B42F3; padding-bottom: 10px;'>
                New Demo Request - Demo Team Notification
            </h2>
            
            <div style='background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='color: #333; margin-top: 0;'>Demo Details</h3>
                <p><strong>Client:</strong> {$firstName} {$lastName} from {$company}</p>
                <p><strong>Demo Date:</strong> {$preferredDate}</p>
                <p><strong>Demo Time:</strong> {$preferredTime}</p>
                <p><strong>Contact Email:</strong> {$email}</p>
            </div>
            
            " . ($message ? "
            <div style='background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='color: #333; margin-top: 0;'>Special Requirements</h3>
                <p>" . nl2br($message) . "</p>
            </div>
            " : "") . "
            
            <div style='margin-top: 30px; padding: 20px; background: #5B42F3; color: white; border-radius: 8px; text-align: center;'>
                <p style='margin: 0;'><strong>Note:</strong> Sales team has been notified and will handle initial contact.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

function createUserEmailContent($firstName, $company, $preferredDate, $preferredTime, $message) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Demo Request Confirmation</title>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #5B42F3; border-bottom: 2px solid #5B42F3; padding-bottom: 10px;'>
                Demo Request Confirmation
            </h2>
            
            <p>Dear {$firstName},</p>
            
            <p>Thank you for requesting a demo of NexGenAI's solar energy platform transformation tools. We have received your request and our sales team will contact you shortly to schedule your personalized demo.</p>
            
            <div style='background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='color: #333; margin-top: 0;'>Your Demo Request Details</h3>
                <p><strong>Company:</strong> {$company}</p>
                <p><strong>Preferred Date:</strong> {$preferredDate}</p>
                <p><strong>Preferred Time:</strong> {$preferredTime}</p>
                " . ($message ? "<p><strong>Additional Requirements:</strong> " . nl2br($message) . "</p>" : "") . "
            </div>
            
            <p><strong>What happens next?</strong></p>
            <ul>
                <li>Our sales team will review your request within 24 hours</li>
                <li>We'll contact you to confirm the demo time and date</li>
                <li>You'll receive a calendar invitation with meeting details</li>
                <li>Our team will prepare a personalized demo based on your requirements</li>
            </ul>
            
            <p>If you have any urgent questions, please don't hesitate to reply to this email.</p>
            
            <p>Best regards,<br>
            The NexGenAI Team</p>
            
            <div style='margin-top: 30px; padding: 20px; background: #f0f0f0; border-radius: 8px; text-align: center; color: #666; font-size: 12px;'>
                <p>This is an automated confirmation email. Please do not reply to this message.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

// Function to send email using PHPMailer
function sendEmailWithPHPMailer($to, $subject, $htmlBody, $fromEmail, $fromName = 'NexGenAI') {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Change to your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com'; // Change to your email
        $mail->Password = 'your-app-password'; // Change to your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);
        $mail->addReplyTo($fromEmail, $fromName);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Function to send email using basic PHP mail() as fallback
function sendEmailWithBasicMail($to, $subject, $htmlBody, $fromEmail) {
    $headers = array(
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $fromEmail,
        'Reply-To: ' . $fromEmail,
        'X-Mailer: PHP/' . phpversion()
    );
    
    $headerString = implode("\r\n", $headers);
    return mail($to, $subject, $htmlBody, $headerString);
}

// Create email content
$salesEmailContent = createSalesEmailContent($firstName, $lastName, $email, $company, $phone, $website, $preferredDate, $preferredTime, $message);
$demoEmailContent = createDemoEmailContent($firstName, $lastName, $email, $company, $preferredDate, $preferredTime, $message);
$userEmailContent = createUserEmailContent($firstName, $company, $preferredDate, $preferredTime, $message);

// Try to send emails using PHPMailer first, fallback to basic mail()
$salesEmailSent = false;
$demoEmailSent = false;
$userEmailSent = false;

// Try PHPMailer first
if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    $salesEmailSent = sendEmailWithPHPMailer($to_sales, $subject, $salesEmailContent, $email, $firstName . ' ' . $lastName);
    $demoEmailSent = sendEmailWithPHPMailer($to_demo, $subject, $demoEmailContent, $email, $firstName . ' ' . $lastName);
    $userEmailSent = sendEmailWithPHPMailer($email, 'Demo Request Confirmation - NexGenAI', $userEmailContent, 'noreply@nexgenai.com', 'NexGenAI');
}

// If PHPMailer fails, use basic mail() as fallback
if (!$salesEmailSent || !$demoEmailSent || !$userEmailSent) {
    $salesEmailSent = sendEmailWithBasicMail($to_sales, $subject, $salesEmailContent, $email);
    $demoEmailSent = sendEmailWithBasicMail($to_demo, $subject, $demoEmailContent, $email);
    $userEmailSent = sendEmailWithBasicMail($email, 'Demo Request Confirmation - NexGenAI', $userEmailContent, 'noreply@nexgenai.com');
}

// Check if emails were sent successfully
if ($salesEmailSent && $demoEmailSent && $userEmailSent) {
    // Redirect to success page
    header('Location: demo-request.html?success=1');
    exit;
} else {
    // Redirect to error page
    header('Location: demo-request.html?error=email_failed');
    exit;
}
?>
