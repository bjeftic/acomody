<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Cancelled</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; color: #333; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #dc2626; color: #fff; padding: 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .body { padding: 32px; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .detail-row:last-child { border-bottom: none; }
        .label { color: #666; font-size: 14px; }
        .value { font-weight: 600; font-size: 14px; }
        .footer { background: #f9f9f9; padding: 20px 32px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>✕ Booking Cancelled by Guest</h1>
    </div>
    <div class="body">
        <p>Hi {{ $booking->host->first_name ?? $booking->host->name }},</p>
        <p>A guest has cancelled their booking for <strong>{{ $booking->accommodation->title }}</strong>. The dates are now available again.</p>

        <div style="margin: 24px 0;">
            <div class="detail-row">
                <span class="label">Guest</span>
                <span class="value">{{ $booking->guest->first_name ?? $booking->guest->name }} {{ $booking->guest->last_name ?? '' }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Check-in</span>
                <span class="value">{{ $booking->check_in->format('D, M j, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Check-out</span>
                <span class="value">{{ $booking->check_out->format('D, M j, Y') }}</span>
            </div>
            @if($booking->cancellation_reason)
            <div class="detail-row">
                <span class="label">Reason</span>
                <span class="value">{{ $booking->cancellation_reason }}</span>
            </div>
            @endif
        </div>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Acomody &mdash; All rights reserved.
    </div>
</div>
</body>
</html>
