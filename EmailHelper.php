<?php
/**
 * EmailHelper Class
 * Provides a clean interface for sending emails using PHPMailer
 */

require_once 'config.php';

// Check if PHPMailer is available
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
}

class EmailHelper {
    private $mailer;
    private $config;
    private $errors = [];
    
    public function __construct() {
        $this->config = getSMTPConfig();
        $this->initializeMailer();
    }
    
    /**
     * Initialize PHPMailer with configuration
     */
    private function initializeMailer() {
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            throw new Exception('PHPMailer not found. Please run: composer install');
        }
        
        $this->mailer = new PHPMailer(true);
        
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['username'];
            $this->mailer->Password = $this->config['password'];
            $this->mailer->SMTPSecure = $this->config['secure'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $this->mailer->Port = $this->config['port'];
            
            // Additional settings
            $this->mailer->SMTPDebug = MAILER_DEBUG ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;
            $this->mailer->Timeout = MAILER_TIMEOUT;
            $this->mailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => MAILER_VERIFY_PEER,
                    'verify_peer_name' => MAILER_VERIFY_PEER_NAME,
                ]
            ];
            
            // Default settings
            $this->mailer->CharSet = EMAIL_CHARSET;
            $this->mailer->Encoding = EMAIL_ENCODING;
            $this->mailer->isHTML(true);
            
        } catch (Exception $e) {
            $this->errors[] = "Mailer initialization failed: " . $e->getMessage();
            throw $e;
        }
    }
    
    /**
     * Send an email using PHPMailer
     */
    public function sendEmail($to, $subject, $htmlBody, $fromEmail = null, $fromName = null, $replyTo = null) {
        try {
            // Reset mailer for new email
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Set from address
            $fromEmail = $fromEmail ?: NOREPLY_EMAIL;
            $fromName = $fromName ?: COMPANY_NAME;
            $this->mailer->setFrom($fromEmail, $fromName);
            
            // Add recipient
            $this->mailer->addAddress($to);
            
            // Set reply-to if provided
            if ($replyTo) {
                $this->mailer->addReplyTo($replyTo);
            }
            
            // Set content
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $this->stripHtml($htmlBody);
            
            // Send email
            $result = $this->mailer->send();
            
            // Log successful email
            if ($result) {
                logEmailActivity($to, $subject, 'SUCCESS');
            }
            
            return $result;
            
        } catch (Exception $e) {
            $errorMsg = "Email sending failed: " . $e->getMessage();
            $this->errors[] = $errorMsg;
            logEmailActivity($to, $subject, 'FAILED', $errorMsg);
            return false;
        }
    }
    
    /**
     * Send multiple emails in batch
     */
    public function sendBatchEmails($emails) {
        $results = [];
        
        foreach ($emails as $email) {
            $results[] = $this->sendEmail(
                $email['to'],
                $email['subject'],
                $email['htmlBody'],
                $email['fromEmail'] ?? null,
                $email['fromName'] ?? null,
                $email['replyTo'] ?? null
            );
        }
        
        return $results;
    }
    
    /**
     * Send demo request emails (sales, demo team, and user confirmation)
     */
    public function sendDemoRequestEmails($formData) {
        $emails = [];
        
        // Sales team email
        $emails[] = [
            'to' => SALES_EMAIL,
            'subject' => 'New Demo Request from ' . $formData['firstName'] . ' ' . $formData['lastName'],
            'htmlBody' => $this->createSalesEmailContent($formData),
            'fromEmail' => $formData['email'],
            'fromName' => $formData['firstName'] . ' ' . $formData['lastName']
        ];
        
        // Demo team email
        $emails[] = [
            'to' => DEMO_EMAIL,
            'subject' => 'New Demo Request - Demo Team Notification',
            'htmlBody' => $this->createDemoEmailContent($formData),
            'fromEmail' => $formData['email'],
            'fromName' => $formData['firstName'] . ' ' . $formData['lastName']
        ];
        
        // User confirmation email
        $emails[] = [
            'to' => $formData['email'],
            'subject' => 'Demo Request Confirmation - ' . COMPANY_NAME,
            'htmlBody' => $this->createUserEmailContent($formData),
            'fromEmail' => NOREPLY_EMAIL,
            'fromName' => COMPANY_NAME
        ];
        
        return $this->sendBatchEmails($emails);
    }
    
    /**
     * Create sales team email content
     */
    private function createSalesEmailContent($data) {
        return $this->getEmailTemplate('sales', $data);
    }
    
    /**
     * Create demo team email content
     */
    private function createDemoEmailContent($data) {
        return $this->getEmailTemplate('demo', $data);
    }
    
    /**
     * Create user confirmation email content
     */
    private function createUserEmailContent($data) {
        return $this->getEmailTemplate('user', $data);
    }
    
    /**
     * Get email template with data
     */
    private function getEmailTemplate($type, $data) {
        $templates = [
            'sales' => $this->getSalesEmailTemplate(),
            'demo' => $this->getDemoEmailTemplate(),
            'user' => $this->getUserEmailTemplate()
        ];
        
        $template = $templates[$type] ?? '';
        
        // Replace placeholders with actual data
        foreach ($data as $key => $value) {
            $template = str_replace('{{' . $key . '}}', htmlspecialchars($value), $template);
        }
        
        return $template;
    }
    
    /**
     * Get sales email template
     */
    private function getSalesEmailTemplate() {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>New Demo Request</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #5B42F3; border-bottom: 2px solid #5B42F3; padding-bottom: 10px;">
                    New Demo Request Received
                </h2>
                
                <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <h3 style="color: #333; margin-top: 0;">Contact Information</h3>
                    <p><strong>Name:</strong> {{firstName}} {{lastName}}</p>
                    <p><strong>Email:</strong> {{email}}</p>
                    <p><strong>Company:</strong> {{company}}</p>
                    <p><strong>Phone:</strong> {{phone}}</p>
                    <p><strong>Website:</strong> {{website}}</p>
                </div>
                
                <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <h3 style="color: #333; margin-top: 0;">Demo Preferences</h3>
                    <p><strong>Preferred Date:</strong> {{preferredDate}}</p>
                    <p><strong>Preferred Time:</strong> {{preferredTime}}</p>
                </div>
                
                {{#if message}}
                <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <h3 style="color: #333; margin-top: 0;">Additional Requirements</h3>
                    <p>{{message}}</p>
                </div>
                {{/if}}
                
                <div style="margin-top: 30px; padding: 20px; background: #5B42F3; color: white; border-radius: 8px; text-align: center;">
                    <p style="margin: 0;"><strong>Action Required:</strong> Please contact this prospect to schedule their demo.</p>
                </div>
                
                <div style="margin-top: 20px; text-align: center; color: #666; font-size: 12px;">
                    <p>This email was sent from the ' . COMPANY_NAME . ' demo request form.</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Get demo team email template
     */
    private function getDemoEmailTemplate() {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>New Demo Request</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #5B42F3; border-bottom: 2px solid #5B42F3; padding-bottom: 10px;">
                    New Demo Request - Demo Team Notification
                </h2>
                
                <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <h3 style="color: #333; margin-top: 0;">Demo Details</h3>
                    <p><strong>Client:</strong> {{firstName}} {{lastName}} from {{company}}</p>
                    <p><strong>Demo Date:</strong> {{preferredDate}}</p>
                    <p><strong>Demo Time:</strong> {{preferredTime}}</p>
                    <p><strong>Contact Email:</strong> {{email}}</p>
                </div>
                
                {{#if message}}
                <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <h3 style="color: #333; margin-top: 0;">Special Requirements</h3>
                    <p>{{message}}</p>
                </div>
                {{/if}}
                
                <div style="margin-top: 30px; padding: 20px; background: #5B42F3; color: white; border-radius: 8px; text-align: center;">
                    <p style="margin: 0;"><strong>Note:</strong> Sales team has been notified and will handle initial contact.</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Get user confirmation email template
     */
    private function getUserEmailTemplate() {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Demo Request Confirmation</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #5B42F3; border-bottom: 2px solid #5B42F3; padding-bottom: 10px;">
                    Demo Request Confirmation
                </h2>
                
                <p>Dear {{firstName}},</p>
                
                <p>Thank you for requesting a demo of ' . COMPANY_NAME . '\'s solar energy platform transformation tools. We have received your request and our sales team will contact you shortly to schedule your personalized demo.</p>
                
                <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <h3 style="color: #333; margin-top: 0;">Your Demo Request Details</h3>
                    <p><strong>Company:</strong> {{company}}</p>
                    <p><strong>Preferred Date:</strong> {{preferredDate}}</p>
                    <p><strong>Preferred Time:</strong> {{preferredTime}}</p>
                    {{#if message}}<p><strong>Additional Requirements:</strong> {{message}}</p>{{/if}}
                </div>
                
                <p><strong>What happens next?</strong></p>
                <ul>
                    <li>Our sales team will review your request within 24 hours</li>
                    <li>We\'ll contact you to confirm the demo time and date</li>
                    <li>You\'ll receive a calendar invitation with meeting details</li>
                    <li>Our team will prepare a personalized demo based on your requirements</li>
                </ul>
                
                <p>If you have any urgent questions, please don\'t hesitate to reply to this email.</p>
                
                <p>Best regards,<br>
                The ' . COMPANY_NAME . ' Team</p>
                
                <div style="margin-top: 30px; padding: 20px; background: #f0f0f0; border-radius: 8px; text-align: center; color: #666; font-size: 12px;">
                    <p>This is an automated confirmation email. Please do not reply to this message.</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Strip HTML tags for plain text version
     */
    private function stripHtml($html) {
        return strip_tags($html);
    }
    
    /**
     * Get any errors that occurred
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Check if there are any errors
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    /**
     * Clear errors
     */
    public function clearErrors() {
        $this->errors = [];
    }
    
    /**
     * Test SMTP connection
     */
    public function testConnection() {
        try {
            $this->mailer->smtpConnect();
            return true;
        } catch (Exception $e) {
            $this->errors[] = "SMTP connection test failed: " . $e->getMessage();
            return false;
        }
    }
}
?>
