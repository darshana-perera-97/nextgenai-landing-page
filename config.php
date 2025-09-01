<?php
// SMTP Configuration
// Update these settings with your actual SMTP server details

// Gmail SMTP Configuration (Example)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // Change to your email
define('SMTP_PASSWORD', 'your-app-password'); // Change to your app password
define('SMTP_SECURE', 'tls'); // or 'ssl'

// Alternative SMTP servers you can use:
// Outlook/Hotmail: smtp-mail.outlook.com, Port 587, TLS
// Yahoo: smtp.mail.yahoo.com, Port 587, TLS
// Custom SMTP: your-smtp-server.com, Port 25/587/465, TLS/SSL

// Email addresses
define('SALES_EMAIL', 'sales@nexgenai.com');
define('DEMO_EMAIL', 'demo@nexgenai.com');
define('NOREPLY_EMAIL', 'noreply@nexgenai.com');

// Company information
define('COMPANY_NAME', 'NexGenAI');
define('COMPANY_WEBSITE', 'https://nexgenai.com');

// Email settings
define('EMAIL_CHARSET', 'UTF-8');
define('EMAIL_ENCODING', '8bit');

// PHPMailer specific settings
define('MAILER_DEBUG', false); // Set to true for debugging
define('MAILER_TIMEOUT', 30); // SMTP timeout in seconds
define('MAILER_VERIFY_PEER', true); // Verify SSL certificate
define('MAILER_VERIFY_PEER_NAME', true); // Verify peer name

// Security settings
define('ENABLE_CSRF_PROTECTION', true);
define('MAX_EMAILS_PER_HOUR', 10);

// Debug settings (set to false in production)
define('DEBUG_MODE', true);
define('LOG_EMAILS', true);

// Function to get SMTP configuration
function getSMTPConfig() {
    return [
        'host' => SMTP_HOST,
        'port' => SMTP_PORT,
        'username' => SMTP_USERNAME,
        'password' => SMTP_PASSWORD,
        'secure' => SMTP_SECURE
    ];
}

// Function to validate email configuration
function validateEmailConfig() {
    $config = getSMTPConfig();
    
    $errors = [];
    
    if (empty($config['host'])) {
        $errors[] = 'SMTP host is not configured';
    }
    
    if (empty($config['username'])) {
        $errors[] = 'SMTP username is not configured';
    }
    
    if (empty($config['password'])) {
        $errors[] = 'SMTP password is not configured';
    }
    
    if (empty(SALES_EMAIL) || empty(DEMO_EMAIL)) {
        $errors[] = 'Sales or Demo email addresses are not configured';
    }
    
    return $errors;
}

// Function to log email activity
function logEmailActivity($to, $subject, $status, $error = null) {
    if (!LOG_EMAILS) {
        return;
    }
    
    $logEntry = date('Y-m-d H:i:s') . " | To: $to | Subject: $subject | Status: $status";
    if ($error) {
        $logEntry .= " | Error: $error";
    }
    
    $logFile = 'logs/email_activity.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
}

// Function to check rate limiting
function checkRateLimit($email) {
    if (!ENABLE_CSRF_PROTECTION) {
        return true;
    }
    
    $logFile = 'logs/rate_limit.log';
    $currentTime = time();
    $oneHourAgo = $currentTime - 3600;
    
    if (file_exists($logFile)) {
        $lines = file($logFile, FILE_IGNORE_NEW_LINES);
        $emailCount = 0;
        
        foreach ($lines as $line) {
            $parts = explode('|', $line);
            if (count($parts) >= 2) {
                $timestamp = (int)trim($parts[0]);
                $emailAddress = trim($parts[1]);
                
                if ($timestamp > $oneHourAgo && $emailAddress === $email) {
                    $emailCount++;
                }
            }
        }
        
        if ($emailCount >= MAX_EMAILS_PER_HOUR) {
            return false;
        }
    }
    
    // Log this email request
    $logEntry = $currentTime . " | " . $email;
    file_put_contents($logFile, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
    
    return true;
}

// Function to generate CSRF token
function generateCSRFToken() {
    if (!ENABLE_CSRF_PROTECTION) {
        return '';
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

// Function to validate CSRF token
function validateCSRFToken($token) {
    if (!ENABLE_CSRF_PROTECTION) {
        return true;
    }
    
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Start session for CSRF protection
if (ENABLE_CSRF_PROTECTION && session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
