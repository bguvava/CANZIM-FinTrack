<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - CANZIM FinTrack</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            background-color: #1E40AF;
            padding: 30px 20px;
            text-align: center;
        }

        .email-header img {
            max-width: 200px;
            height: auto;
        }

        .email-body {
            padding: 40px 30px;
        }

        .email-body h1 {
            color: #1E40AF;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .email-body p {
            margin-bottom: 15px;
            font-size: 16px;
        }

        .reset-button {
            display: inline-block;
            background-color: #1E40AF;
            color: #ffffff;
            text-decoration: none;
            padding: 14px 30px;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
            transition: background-color 0.3s ease;
        }

        .reset-button:hover {
            background-color: #1E3A8A;
        }

        .expiry-notice {
            background-color: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .expiry-notice p {
            margin: 0;
            color: #92400E;
            font-size: 14px;
        }

        .reset-link-container {
            background-color: #F3F4F6;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            word-break: break-all;
        }

        .reset-link-container p {
            margin: 5px 0;
            font-size: 12px;
            color: #6B7280;
        }

        .reset-link-container code {
            color: #1E40AF;
            font-size: 13px;
        }

        .email-footer {
            background-color: #F9FAFB;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #E5E7EB;
        }

        .email-footer p {
            margin: 5px 0;
            font-size: 13px;
            color: #6B7280;
        }

        .email-footer a {
            color: #1E40AF;
            text-decoration: none;
        }

        .security-notice {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            font-size: 14px;
            color: #6B7280;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header with Logo -->
        <div class="email-header">
            <img src="{{ asset('images/logo/canzim_white.png') }}" alt="CANZIM Logo">
        </div>

        <!-- Email Body -->
        <div class="email-body">
            <h1>Password Reset Request</h1>

            <p>Hello,</p>

            <p>You are receiving this email because we received a password reset request for your CANZIM FinTrack
                account.</p>

            <p>Click the button below to reset your password:</p>

            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="reset-button">Reset Password</a>
            </div>

            <!-- Expiry Notice -->
            <div class="expiry-notice">
                <p><strong>⏰ Important:</strong> This password reset link will expire in 1 hour for security reasons.
                </p>
            </div>

            <!-- Alternative Link -->
            <div class="reset-link-container">
                <p><strong>If the button doesn't work, copy and paste this link into your browser:</strong></p>
                <code>{{ $resetUrl }}</code>
            </div>

            <!-- Security Notice -->
            <div class="security-notice">
                <p><strong>Didn't request a password reset?</strong></p>
                <p>If you did not request a password reset, no further action is required. Your password will remain
                    unchanged. Please contact support if you have concerns about your account security.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>Climate Action Network Zimbabwe</strong></p>
            <p>Financial Management & Accounting System</p>
            <p style="margin-top: 15px;">
                This is an automated message. Please do not reply to this email.
            </p>
            <p style="margin-top: 10px;">
                <a href="https://www.canzimbabwe.org.zw/">www.canzimbabwe.org.zw</a>
            </p>
            <p style="margin-top: 15px; font-size: 12px;">
                Developed with ❤️ by <a href="https://bguvava.com">bguvava (bguvava.com)</a>
            </p>
            <p style="font-size: 11px; color: #9CA3AF;">
                &copy; {{ date('Y') }} Climate Action Network Zimbabwe. All rights reserved.
            </p>
        </div>
    </div>
</body>

</html>
