<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accommodation Under Review</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; color: #333; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #2563eb; color: #fff; padding: 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .body { padding: 32px; }
        .notice { background: #fefce8; border-left: 4px solid #ca8a04; padding: 16px; margin: 20px 0; border-radius: 4px; }
        .cta { display: inline-block; padding: 12px 24px; background: #ca8a04; color: #fff; border-radius: 4px; text-decoration: none; font-weight: bold; margin-top: 12px; }
        .footer { background: #f9f9f9; padding: 20px 32px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>&#128338; Accommodation Under Review</h1>
    </div>
    <div class="body">
        <p>Hi {{ $draft->user->first_name ?? $draft->user->name }},</p>
        <p>
            Your accommodation <strong>{{ json_decode($draft->data, true)['title'] ?? 'your property' }}</strong>
            has been successfully submitted and is now under review by our team. We'll notify you once it's approved.
        </p>

        <div class="notice">
            <strong style="color:#92400e;">Complete your host profile to go live</strong>
            <p style="margin: 8px 0 0; color:#78350f;">
                Once your accommodation is approved, it won't appear in search results until your host profile
                is complete. Please add your display name, contact email, and phone number — it only takes a minute.
            </p>
            <a href="{{ config('app.url') }}/hosting/profile" class="cta">
                Complete host profile →
            </a>
        </div>

        <p style="color:#666; font-size:13px; margin-top:24px;">If you have any questions, please contact our support team.</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Acomody &mdash; All rights reserved.
    </div>
</div>
</body>
</html>
