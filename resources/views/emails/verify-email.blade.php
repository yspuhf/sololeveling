<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
    <style>
        body {
            font-family: 'Outfit', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #0f172a;
            color: #d1d5db;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .logo {
            text-align: center;
            font-family: 'Orbitron', sans-serif;
            font-size: 24px;
            font-weight: 900;
            color: #ffffff;
            margin-bottom: 24px;
            letter-spacing: 0.1em;
        }
        p {
            font-size: 16px;
            line-height: 24px;
            margin-top: 0;
            margin-bottom: 16px;
            color: #9ca3af;
        }
        .btn-container {
            text-align: center;
            margin: 32px 0;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #45f3ff, #8a2be2);
            color: #0f172a;
            font-weight: bold;
            text-decoration: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-size: 16px;
            box-shadow: 0 0 15px rgba(69, 243, 255, 0.4);
        }
        .footer {
            margin-top: 32px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 16px;
            font-size: 14px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            ΛRISE
        </div>
        <p>Hello {{ $name }},</p>
        <p>Thank you for registering.</p>
        <p>Please click the button below to verify your email address and activate your account.</p>
        <div class="btn-container">
            <a href="{{ $url }}" class="btn" style="color: #0f172a !important;">Verify My Email</a>
        </div>
        <p>This link will expire in 24 hours.</p>
        <p>If you did not create this account, please ignore this email.</p>
        <div class="footer">
            Regards,<br>
            Website Team
        </div>
    </div>
</body>
</html>
