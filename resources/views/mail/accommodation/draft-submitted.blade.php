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
        .steps { margin: 20px 0; padding: 0; list-style: none; }
        .steps li { display: flex; align-items: flex-start; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
        .steps li:last-child { border-bottom: none; }
        .step-num { flex-shrink: 0; width: 24px; height: 24px; background: #2563eb; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; }
        .cta { display: inline-block; padding: 12px 24px; background: #2563eb; color: #fff; border-radius: 4px; text-decoration: none; font-weight: bold; margin-top: 16px; }
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
            has been successfully submitted and is now under review by our team.
        </p>

        <p>Here's what happens next:</p>
        <ul class="steps">
            <li>
                <span class="step-num">1</span>
                <span>Our team reviews your listing — this usually takes 1–2 business days.</span>
            </li>
            <li>
                <span class="step-num">2</span>
                <span>Once approved, your listing will automatically become searchable and guests can start booking.</span>
            </li>
            <li>
                <span class="step-num">3</span>
                <span>You'll receive an email notification as soon as it goes live.</span>
            </li>
        </ul>

        <p>
            <a href="{{ config('app.url') }}/hosting/dashboard" class="cta">Go to hosting dashboard &rarr;</a>
        </p>

        <p style="color:#666; font-size:13px; margin-top:24px;">If you have any questions, please contact our support team.</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Acomody &mdash; All rights reserved.
    </div>
</div>
</body>
</html>
