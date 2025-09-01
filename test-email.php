<?php
/**
 * Email Configuration Test Script
 * Use this to test your PHPMailer setup and SMTP connection
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PHPMailer Configuration Test</h1>";

// Check if PHPMailer is installed
if (!file_exists('vendor/autoload.php')) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
    echo "<strong>Error:</strong> PHPMailer not found. Please run: <code>composer install</code>";
    echo "</div>";
    exit;
}

// Include required files
require_once 'config.php';
require_once 'EmailHelper.php';

echo "<h2>Configuration Check</h2>";

// Check SMTP configuration
$config = getSMTPConfig();
echo "<div style='background: #f5f5f5; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
echo "<strong>SMTP Configuration:</strong><br>";
echo "Host: " . $config['host'] . "<br>";
echo "Port: " . $config['port'] . "<br>";
echo "Username: " . $config['username'] . "<br>";
echo "Secure: " . $config['secure'] . "<br>";
echo "</div>";

// Validate configuration
$errors = validateEmailConfig();
if (!empty($errors)) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
    echo "<strong>Configuration Errors:</strong><br>";
    foreach ($errors as $error) {
        echo "- " . $error . "<br>";
    }
    echo "</div>";
} else {
    echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
    echo "<strong>✓ Configuration is valid</strong>";
    echo "</div>";
}

echo "<h2>SMTP Connection Test</h2>";

try {
    $emailHelper = new EmailHelper();
    
    if ($emailHelper->testConnection()) {
        echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
        echo "<strong>✓ SMTP connection successful!</strong>";
        echo "</div>";
    } else {
        echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
        echo "<strong>✗ SMTP connection failed</strong><br>";
        $errors = $emailHelper->getErrors();
        foreach ($errors as $error) {
            echo "- " . $error . "<br>";
        }
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
    echo "<strong>✗ Error initializing EmailHelper:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<h2>Test Email Sending</h2>";

// Only show test form if configuration is valid and connection works
if (empty($errors)) {
    echo "<form method='post' style='background: #f9f9f9; padding: 20px; border-radius: 5px;'>";
    echo "<p><strong>Send a test email to verify everything works:</strong></p>";
    echo "<p>To: <input type='email' name='test_email' placeholder='your-email@example.com' required style='width: 300px; padding: 5px;'></p>";
    echo "<p><input type='submit' name='send_test' value='Send Test Email' style='background: #5B42F3; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'></p>";
    echo "</form>";
    
    // Handle test email
    if (isset($_POST['send_test']) && !empty($_POST['test_email'])) {
        $testEmail = $_POST['test_email'];
        
        try {
            $emailHelper = new EmailHelper();
            
            $testResult = $emailHelper->sendEmail(
                $testEmail,
                'PHPMailer Test Email - ' . COMPANY_NAME,
                '<h2>Test Email Successful!</h2><p>This is a test email to verify your PHPMailer configuration is working correctly.</p><p>If you received this email, your email setup is working properly!</p>',
                NOREPLY_EMAIL,
                COMPANY_NAME
            );
            
            if ($testResult) {
                echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
                echo "<strong>✓ Test email sent successfully to " . htmlspecialchars($testEmail) . "</strong>";
                echo "</div>";
            } else {
                echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
                echo "<strong>✗ Test email failed</strong><br>";
                $errors = $emailHelper->getErrors();
                foreach ($errors as $error) {
                    echo "- " . $error . "<br>";
                }
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
            echo "<strong>✗ Test email error:</strong> " . $e->getMessage();
            echo "</div>";
        }
    }
} else {
    echo "<div style='color: orange; padding: 10px; border: 1px solid orange; margin: 10px 0;'>";
    echo "<strong>⚠ Configuration issues must be resolved before testing email sending</strong>";
    echo "</div>";
}

echo "<h2>Next Steps</h2>";
echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; border-left: 4px solid #2196F3;'>";
echo "<ol>";
echo "<li>Update your SMTP credentials in <code>config.php</code></li>";
echo "<li>Make sure your email provider allows SMTP access</li>";
echo "<li>For Gmail, use an App Password instead of your regular password</li>";
echo "<li>Test the connection using this script</li>";
echo "<li>Update your demo request form to use <code>send-demo-request-phpmailer.php</code></li>";
echo "</ol>";
echo "</div>";

echo "<h2>Common SMTP Settings</h2>";
echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
echo "<h3>Gmail</h3>";
echo "<ul>";
echo "<li>Host: smtp.gmail.com</li>";
echo "<li>Port: 587</li>";
echo "<li>Security: TLS</li>";
echo "<li>Username: your-email@gmail.com</li>";
echo "<li>Password: App Password (not your regular password)</li>";
echo "</ul>";

echo "<h3>Outlook/Hotmail</h3>";
echo "<ul>";
echo "<li>Host: smtp-mail.outlook.com</li>";
echo "<li>Port: 587</li>";
echo "<li>Security: TLS</li>";
echo "<li>Username: your-email@outlook.com</li>";
echo "<li>Password: Your account password</li>";
echo "</ul>";

echo "<h3>Yahoo</h3>";
echo "<ul>";
echo "<li>Host: smtp.mail.yahoo.com</li>";
echo "<li>Port: 587</li>";
echo "<li>Security: TLS</li>";
echo "<li>Username: your-email@yahoo.com</li>";
echo "<li>Password: App Password</li>";
echo "</ul>";
echo "</div>";
?>
