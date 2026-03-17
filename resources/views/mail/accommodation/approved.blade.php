<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accommodation Approved</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; color: #333; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #16a34a; color: #fff; padding: 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .body { padding: 32px; }
        .footer { background: #f9f9f9; padding: 20px 32px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>&#10003; Accommodation Approved</h1>
    </div>
    <div class="body">
        <p>Hi {{ $draft->user->first_name ?? $draft->user->name }},</p>
        <p>Great news! Your accommodation <strong>{{ json_decode($draft->data, true)['title'] ?? 'your property' }}</strong> has been reviewed and approved by our team.</p>

        @if($hostProfileComplete)
            <p>Your listing is now live on Acomody and guests can start searching and booking.</p>
        @else
            <div style="background:#fefce8; border-left:4px solid #ca8a04; padding:16px; margin:20px 0; border-radius:4px;">
                <strong style="color:#92400e;">One more step to go live</strong>
                <p style="margin:8px 0 0; color:#78350f;">
                    Your listing has been approved but it won't appear in search results until you complete your host profile
                    (display name, contact email, and phone number). It only takes a minute.
                </p>
                <p style="margin:12px 0 0;">
                    <a href="{{ config('app.url') }}/hosting/profile"
                       style="display:inline-block; padding:10px 20px; background:#ca8a04; color:#fff; border-radius:4px; text-decoration:none; font-weight:bold;">
                        Complete host profile →
                    </a>
                </p>
            </div>
        @endif

        <p style="color:#666; font-size:13px;">If you have any questions, please contact our support team.</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Acomody &mdash; All rights reserved.
    </div>
</div>
</body>
</html>
