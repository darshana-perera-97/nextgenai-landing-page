# NexGenAI Demo Request System

A complete demo request system for NexGenAI with SMTP email integration and a beautiful, responsive web interface.

## Features

- **Professional Demo Request Form**: Collect comprehensive information from potential clients
- **SMTP Email Integration**: Automatically send demo requests to your sales team
- **Dual Email Notifications**: Sends requests to both `sales@nexgenai.com` and `demo@nexgenai.com`
- **User Confirmation**: Sends confirmation emails to users who submit requests
- **Success Popup**: Shows "Our sales team will contact you asap" message after submission
- **Responsive Design**: Works perfectly on all devices
- **Consistent Branding**: Matches your existing NexGenAI website design

## Pages Updated

The following pages now include a "Demo Request" navigation link:
- `index.html` (Home)
- `product.html`
- `pricing.html`
- `changelog.html`
- `blog.html`
- `company.html`
- `blogs/single-blog-post.html`

## Setup Instructions

### 1. Install Dependencies

```bash
npm install
```

### 2. Configure Email Settings

Create a `.env` file in the root directory:

```env
EMAIL_USER=your-email@gmail.com
EMAIL_PASS=your-app-password
PORT=3000
```

**Important**: For Gmail, you'll need to:
1. Enable 2-factor authentication
2. Generate an "App Password" (not your regular password)
3. Use the app password in the `EMAIL_PASS` variable

### 3. Update Email Addresses

In `server.js`, update the email addresses where demo requests should be sent:

```javascript
const emailPromises = [
    'sales@nexgenai.com',    // Change this to your sales email
    'demo@nexgenai.com'      // Change this to your demo email
].map(toEmail => // ... rest of the code
```

### 4. Start the Server

```bash
# Development mode (with auto-restart)
npm run dev

# Production mode
npm start
```

The server will start on `http://localhost:3000`

## File Structure

```
├── demo-request.html          # Main demo request page
├── server.js                  # Node.js backend server
├── package.json               # Dependencies and scripts
├── README.md                  # This file
├── index.html                 # Home page (updated with nav link)
├── product.html               # Product page (updated with nav link)
├── pricing.html               # Pricing page (updated with nav link)
├── changelog.html             # Changelog page (updated with nav link)
├── blog.html                  # Blog page (updated with nav link)
├── company.html               # Company page (updated with nav link)
└── blogs/
    └── single-blog-post.html  # Blog template (updated with nav link)
```

## Form Fields

The demo request form collects:

- **Required Fields**:
  - First Name
  - Last Name
  - Email Address
  - Company Name
  - Preferred Demo Date
  - Preferred Demo Time

- **Optional Fields**:
  - Phone Number
  - Current Website URL
  - Additional Requirements

## Email Templates

### Sales Team Notification
- **Subject**: "New Demo Request - NexGenAI"
- **Content**: Complete form details with submission timestamp
- **Recipients**: Your configured sales and demo email addresses

### User Confirmation
- **Subject**: "Demo Request Confirmation - NexGenAI"
- **Content**: Thank you message with request details
- **Recipient**: The email address provided in the form

## Customization

### Styling
- The form uses Tailwind CSS classes that match your existing design
- Colors can be customized in the CSS variables
- The glassmorphism effect can be adjusted in the `.glass-effect` class

### Email Content
- Modify email templates in `server.js`
- Add additional fields to the form and update the email content accordingly
- Customize the confirmation message

### SMTP Provider
- Currently configured for Gmail
- Can be easily changed to other providers (SendGrid, AWS SES, etc.)
- Update the transporter configuration in `server.js`

## Security Notes

- **Environment Variables**: Never commit your email credentials to version control
- **Rate Limiting**: Consider adding rate limiting for production use
- **Input Validation**: The form includes basic HTML5 validation
- **CORS**: Configured for development; adjust for production

## Troubleshooting

### Common Issues

1. **Email not sending**:
   - Check your email credentials
   - Verify 2FA is enabled for Gmail
   - Use app password, not regular password

2. **Form submission fails**:
   - Check browser console for errors
   - Verify server is running
   - Check network tab for API calls

3. **Styling issues**:
   - Ensure Tailwind CSS is loading
   - Check for CSS conflicts

### Debug Mode

Enable debug logging by adding this to `server.js`:

```javascript
// Add before creating the transporter
transporter.verify(function(error, success) {
    if (error) {
        console.log('SMTP connection error:', error);
    } else {
        console.log('SMTP server is ready to send emails');
    }
});
```

## Support

For technical support or customization requests, please contact your development team.

---

**NexGenAI** - Turn your website into the #1 Solar Site in Sri Lanka
