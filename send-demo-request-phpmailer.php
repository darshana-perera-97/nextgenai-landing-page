<?php
/**
 * Demo Request Handler using PHPMailer
 * This script handles demo request form submissions and sends emails using PHPMailer
 */

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: demo-request.html');
    exit;
}

// Include configuration and email helper
require_once 'config.php';
require_once 'EmailHelper.php';

try {
    // Initialize email helper
    $emailHelper = new EmailHelper();
    
    // Get and validate form data
    $formData = [
        'firstName' => trim($_POST['firstName'] ?? ''),
        'lastName' => trim($_POST['lastName'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'company' => trim($_POST['company'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'website' => trim($_POST['website'] ?? ''),
        'message' => trim($_POST['message'] ?? '')
    ];
    
    // Validate required fields
    $requiredFields = ['firstName', 'lastName', 'email', 'company'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (empty($formData[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        header('Location: demo-request.html?error=missing_fields&fields=' . implode(',', $missingFields));
        exit;
    }
    
    // Validate email format
    if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        header('Location: demo-request.html?error=invalid_email');
        exit;
    }
    
    // Sanitize inputs
    foreach ($formData as $key => $value) {
        $formData[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    // Check rate limiting
    if (!checkRateLimit($formData['email'])) {
        header('Location: demo-request.html?error=rate_limit_exceeded');
        exit;
    }
    
    // Test SMTP connection first
    if (!$emailHelper->testConnection()) {
        throw new Exception('SMTP connection failed. Please check your email configuration.');
    }
    
    // Send all demo request emails
    $results = $emailHelper->sendDemoRequestEmails($formData);
    
    // Check if all emails were sent successfully
    if (count(array_filter($results)) === count($results)) {
        // All emails sent successfully
        header('Location: demo-request.html?success=1');
        exit;
    } else {
        // Some emails failed
        $errors = $emailHelper->getErrors();
        error_log('Demo request email failures: ' . implode(', ', $errors));
        header('Location: demo-request.html?error=email_partial_failure');
        exit;
    }
    
} catch (Exception $e) {
    // Log the error
    error_log('Demo request error: ' . $e->getMessage());
    
    // Redirect to error page
    header('Location: demo-request.html?error=system_error');
    exit;
}
?>
