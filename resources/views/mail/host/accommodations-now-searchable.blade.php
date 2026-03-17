<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Listings Are Now Live</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; color: #333; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #16a34a; color: #fff; padding: 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .body { padding: 32px; }
        .cta { display: inline-block; padding: 12px 24px; background: #16a34a; color: #fff; border-radius: 4px; text-decoration: none; font-weight: bold; margin-top: 16px; }
        .footer { background: #f9f9f9; padding: 20px 32px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>&#10003; Your Listings Are Now Live!</h1>
    </div>
    <div class="body">
        <p>Hi {{ $user->first_name ?? $user->name }},</p>
        <p>
            Your host profile is now complete — your
            {{ $accommodationCount === 1 ? 'listing is' : $accommodationCount . ' listings are' }}
            now searchable on Acomody and guests can start booking.
        </p>
        <p>
            <a href="{{ config('app.url') }}/hosting/dashboard" class="cta">Go to hosting dashboard →</a>
        </p>
        <p style="color:#666; font-size:13px; margin-top:24px;">If you have any questions, please contact our support team.</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Acomody &mdash; All rights reserved.
    </div>
</div>
</body>
</html>
