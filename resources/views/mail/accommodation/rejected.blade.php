<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accommodation Not Approved</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; color: #333; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #dc2626; color: #fff; padding: 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .body { padding: 32px; }
        .reason-box { background: #fef2f2; border-left: 4px solid #dc2626; padding: 16px; margin: 20px 0; border-radius: 4px; }
        .footer { background: #f9f9f9; padding: 20px 32px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>&#10007; Accommodation Not Approved</h1>
    </div>
    <div class="body">
        <p>Hi {{ $draft->user->first_name ?? $draft->user->name }},</p>
        <p>Thank you for submitting your accommodation <strong>{{ json_decode($draft->data, true)['title'] ?? 'your property' }}</strong> for review.</p>
        <p>After careful review, our team was unable to approve your submission at this time.</p>

        @if($reason)
        <div class="reason-box">
            <strong>Reason:</strong>
            <p style="margin: 8px 0 0;">{{ $reason }}</p>
        </div>
        @endif

        <p>If you believe this was a mistake or have made the necessary changes, you are welcome to re-submit your accommodation for review.</p>
        <p style="color:#666; font-size:13px;">If you have any questions, please contact our support team.</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Acomody &mdash; All rights reserved.
    </div>
</div>
</body>
</html>
