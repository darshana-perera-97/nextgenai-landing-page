const express = require('express');
const nodemailer = require('nodemailer');
const cors = require('cors');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static('.')); // Serve static files from current directory

// SMTP Configuration
const transporter = nodemailer.createTransporter({
    service: 'gmail', // You can change this to your preferred email service
    auth: {
        user: process.env.EMAIL_USER || 'your-email@gmail.com', // Your email
        pass: process.env.EMAIL_PASS || 'your-app-password' // Your app password
    }
});

// Alternative SMTP configuration for other services
/*
const transporter = nodemailer.createTransporter({
    host: 'smtp.gmail.com', // or your SMTP host
    port: 587,
    secure: false, // true for 465, false for other ports
    auth: {
        user: process.env.EMAIL_USER || 'your-email@gmail.com',
        pass: process.env.EMAIL_PASS || 'your-app-password'
    }
});
*/

// Demo request endpoint
app.post('/api/send-demo-request', async (req, res) => {
    try {
        const {
            firstName,
            lastName,
            email,
            company,
            phone,
            website,
            preferredDate,
            preferredTime,
            message
        } = req.body;

        // Email content
        const emailContent = `
            New Demo Request - NexGenAI
            
            Name: ${firstName} ${lastName}
            Email: ${email}
            Company: ${company}
            Phone: ${phone || 'Not provided'}
            Website: ${website || 'Not provided'}
            Preferred Date: ${preferredDate}
            Preferred Time: ${preferredTime}
            Additional Requirements: ${message || 'None'}
            
            Submitted at: ${new Date().toLocaleString()}
        `;

        // Send email to both addresses
        const emailPromises = [
            'sales@nexgenai.com',
            'demo@nexgenai.com'
        ].map(toEmail => 
            transporter.sendMail({
                from: process.env.EMAIL_USER || 'your-email@gmail.com',
                to: toEmail,
                subject: 'New Demo Request - NexGenAI',
                text: emailContent,
                html: `
                    <h2>New Demo Request - NexGenAI</h2>
                    <p><strong>Name:</strong> ${firstName} ${lastName}</p>
                    <p><strong>Email:</strong> ${email}</p>
                    <p><strong>Company:</strong> ${company}</p>
                    <p><strong>Phone:</strong> ${phone || 'Not provided'}</p>
                    <p><strong>Website:</strong> ${website || 'Not provided'}</p>
                    <p><strong>Preferred Date:</strong> ${preferredDate}</p>
                    <p><strong>Preferred Time:</strong> ${preferredTime}</p>
                    <p><strong>Additional Requirements:</strong> ${message || 'None'}</p>
                    <p><strong>Submitted at:</strong> ${new Date().toLocaleString()}</p>
                `
            })
        );

        // Wait for all emails to be sent
        await Promise.all(emailPromises);

        // Send confirmation email to the user
        await transporter.sendMail({
            from: process.env.EMAIL_USER || 'your-email@gmail.com',
            to: email,
            subject: 'Demo Request Confirmation - NexGenAI',
            text: `
                Thank you for requesting a demo of NexGenAI!
                
                We have received your request and our sales team will contact you asap to schedule your personalized demo.
                
                Request Details:
                - Name: ${firstName} ${lastName}
                - Company: ${company}
                - Preferred Date: ${preferredDate}
                - Preferred Time: ${preferredTime}
                
                If you have any questions, please don't hesitate to reach out to us.
                
                Best regards,
                The NexGenAI Team
            `,
            html: `
                <h2>Thank you for requesting a demo of NexGenAI!</h2>
                <p>We have received your request and our sales team will contact you asap to schedule your personalized demo.</p>
                
                <h3>Request Details:</h3>
                <ul>
                    <li><strong>Name:</strong> ${firstName} ${lastName}</li>
                    <li><strong>Company:</strong> ${company}</li>
                    <li><strong>Preferred Date:</strong> ${preferredDate}</li>
                    <li><strong>Preferred Time:</strong> ${preferredTime}</li>
                </ul>
                
                <p>If you have any questions, please don't hesitate to reach out to us.</p>
                
                <p>Best regards,<br>The NexGenAI Team</p>
            `
        });

        res.json({ 
            success: true, 
            message: 'Demo request submitted successfully' 
        });

    } catch (error) {
        console.error('Error sending demo request:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Failed to submit demo request' 
        });
    }
});

// Serve the demo request page
app.get('/demo-request', (req, res) => {
    res.sendFile(path.join(__dirname, 'demo-request.html'));
});

// Start server
app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
    console.log(`Demo request page: http://localhost:${PORT}/demo-request`);
    console.log(`API endpoint: http://localhost:${PORT}/api/send-demo-request`);
});

module.exports = app;
