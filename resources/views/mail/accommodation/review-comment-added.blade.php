<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviewer Comment</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; color: #333; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #2563eb; color: #fff; padding: 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .body { padding: 32px; }
        .comment-box { background: #eff6ff; border-left: 4px solid #2563eb; padding: 16px; margin: 20px 0; border-radius: 4px; }
        .footer { background: #f9f9f9; padding: 20px 32px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>&#128172; Reviewer Comment</h1>
    </div>
    <div class="body">
        <p>Hi {{ $draft->user->first_name ?? $draft->user->name }},</p>
        <p>Our review team has left a comment on your accommodation submission <strong>{{ json_decode($draft->data, true)['title'] ?? 'your property' }}</strong>.</p>

        <div class="comment-box">
            <p style="margin: 0;">{{ $comment->body }}</p>
        </div>

        <p>Please review the comment and make any necessary updates to your submission.</p>
        <p style="color:#666; font-size:13px;">If you have any questions, please contact our support team.</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Acomody &mdash; All rights reserved.
    </div>
</div>
</body>
</html>
