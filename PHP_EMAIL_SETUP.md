# PHP SMTP Email Setup for NexGenAI Demo Request Form

This guide will help you set up PHP SMTP email functionality to replace the JavaScript email handling in your demo request form.

## Files Created

1. **`send-demo-request.php`** - Basic PHP email handling using PHP's built-in `mail()` function
2. **`send-demo-request-advanced.php`** - Advanced email handling using PHPMailer with SMTP
3. **`config.php`** - Configuration file for SMTP settings
4. **`composer.json`** - Dependency management for PHPMailer
5. **`PHP_EMAIL_SETUP.md`** - This setup guide

## Prerequisites

- PHP 7.4 or higher
- Web server with PHP support (Apache, Nginx, etc.)
- SMTP server credentials (Gmail, Outlook, custom SMTP, etc.)

## Setup Options

### Option 1: Basic PHP mail() Function (Recommended for beginners)

This option uses PHP's built-in `mail()` function and doesn't require additional libraries.

**Steps:**
1. Upload `send-demo-request.php` to your web server
2. Update the email addresses in the file:
   ```php
   $to_sales = 'sales@nexgenai.com';  // Change to your sales email
   $to_demo = 'demo@nexgenai.com';    // Change to your demo team email
   ```
3. Ensure your server's PHP mail configuration is working

**Pros:**
- No additional dependencies
- Simple setup
- Works on most hosting providers

**Cons:**
- Limited SMTP control
- May go to spam folders
- Less reliable delivery

### Option 2: Advanced PHPMailer with SMTP (Recommended for production)

This option provides better email delivery, SMTP control, and professional features.

**Steps:**

1. **Install Composer** (if not already installed):
   ```bash
   # Windows
   # Download composer-setup.exe from https://getcomposer.org/download/
   
   # Linux/Mac
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer
   ```

2. **Install PHPMailer**:
   ```bash
   composer install
   ```

3. **Configure SMTP settings** in `config.php`:
   ```php
   // Gmail Example
   define('SMTP_HOST', 'smtp.gmail.com');
   define('SMTP_PORT', 587);
   define('SMTP_USERNAME', 'your-email@gmail.com');
   define('SMTP_PASSWORD', 'your-app-password');
   define('SMTP_SECURE', 'tls');
   
   // Update email addresses
   define('SALES_EMAIL', 'sales@nexgenai.com');
   define('DEMO_EMAIL', 'demo@nexgenai.com');
   define('NOREPLY_EMAIL', 'noreply@nexgenai.com');
   ```

4. **Update the form action** in `demo-request.html`:
   ```html
   <form action="send-demo-request-advanced.php" method="POST" class="space-y-6">
   ```

## SMTP Server Configurations

### Gmail SMTP
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
```
**Note:** You need to enable 2-factor authentication and generate an App Password.

### Outlook/Hotmail SMTP
```php
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
```

### Yahoo SMTP
```php
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
```

### Custom SMTP Server
```php
define('SMTP_HOST', 'your-smtp-server.com');
define('SMTP_PORT', 587); // or 25, 465
define('SMTP_SECURE', 'tls'); // or 'ssl'
```

## Gmail App Password Setup

1. Go to your Google Account settings
2. Enable 2-Step Verification
3. Go to Security → App passwords
4. Generate a new app password for "Mail"
5. Use this password in your SMTP configuration

## Testing the Setup

1. **Test basic functionality:**
   - Fill out the demo request form
   - Submit the form
   - Check if you receive the success popup
   - Check if emails are sent to the configured addresses

2. **Check email delivery:**
   - Check your sales and demo email inboxes
   - Check the user's email for confirmation
   - Check spam/junk folders if emails don't appear

3. **Check server logs:**
   - Look for PHP error logs
   - Check the email activity logs (if enabled)
   - Check SMTP server logs

## Troubleshooting

### Common Issues

1. **Emails not sending:**
   - Check SMTP credentials
   - Verify SMTP server settings
   - Check firewall/port restrictions
   - Verify PHP mail configuration

2. **Emails going to spam:**
   - Configure SPF, DKIM, and DMARC records
   - Use a reputable SMTP service
   - Avoid spam trigger words
   - Set proper "From" addresses

3. **PHPMailer errors:**
   - Check if Composer dependencies are installed
   - Verify SMTP configuration
   - Check PHP version compatibility

### Debug Mode

Enable debug mode in `config.php`:
```php
define('DEBUG_MODE', true);
define('LOG_EMAILS', true);
```

This will create log files in the `logs/` directory.

### Error Logs

Check your server's error logs:
- **Apache:** `/var/log/apache2/error.log`
- **Nginx:** `/var/log/nginx/error.log`
- **PHP:** Check `php.ini` for `error_log` setting

## Security Considerations

1. **CSRF Protection:** Enabled by default in advanced version
2. **Rate Limiting:** Prevents spam abuse
3. **Input Validation:** All form inputs are sanitized
4. **Email Logging:** Tracks email activity for monitoring

## Production Deployment

1. **Disable debug mode:**
   ```php
   define('DEBUG_MODE', false);
   define('LOG_EMAILS', false);
   ```

2. **Use environment variables** for sensitive data:
   ```php
   define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD']);
   ```

3. **Set up proper SSL certificates** for your domain

4. **Configure email authentication** (SPF, DKIM, DMARC)

5. **Monitor email delivery** and bounce rates

## File Structure

```
your-project/
├── demo-request.html          # Updated form with PHP action
├── send-demo-request.php      # Basic PHP email handler
├── send-demo-request-advanced.php  # Advanced PHPMailer handler
├── config.php                 # SMTP configuration
├── composer.json              # PHP dependencies
├── vendor/                    # Composer packages (after install)
├── logs/                      # Email activity logs
└── PHP_EMAIL_SETUP.md        # This setup guide
```

## Support

If you encounter issues:

1. Check the troubleshooting section above
2. Verify your SMTP server settings
3. Test with a simple PHP mail script
4. Check your hosting provider's email policies
5. Consult your hosting provider's support

## Next Steps

After successful setup:

1. Test the form thoroughly
2. Monitor email delivery
3. Set up email templates if needed
4. Configure email analytics
5. Set up bounce handling
6. Implement email tracking

---

**Note:** Always test in a development environment before deploying to production. Keep your SMTP credentials secure and never commit them to version control.
