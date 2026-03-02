<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email Address</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; color: #333; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #2563eb; color: #fff; padding: 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .body { padding: 32px; }
        .btn { display: inline-block; margin: 24px 0; padding: 14px 32px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: 600; }
        .note { color: #666; font-size: 13px; margin-top: 24px; }
        .footer { background: #f9f9f9; padding: 20px 32px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Verify Your Email Address</h1>
    </div>
    <div class="body">
        <p>Hi {{ $user->email }},</p>
        <p>Thank you for registering with {{ config('app.name') }}. Please verify your email address by clicking the button below.</p>

        <div style="text-align: center;">
            <a href="{{ $verificationUrl }}" class="btn">Verify Email Address</a>
        </div>

        <p class="note">This link will expire in {{ config('auth.verification.expire', 60) }} minutes.</p>
        <p class="note">If you did not create an account, no further action is required.</p>
        <p class="note">If the button above does not work, copy and paste the following link into your browser:<br>
            <a href="{{ $verificationUrl }}" style="color: #2563eb; word-break: break-all;">{{ $verificationUrl }}</a>
        </p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} {{ config('app.name') }} &mdash; All rights reserved.
    </div>
</div>
</body>
</html>
