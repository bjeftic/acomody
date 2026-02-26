<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Request Declined</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; color: #333; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #f97316; color: #fff; padding: 32px; text-align: center; }
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
        <h1>Booking Request Declined</h1>
    </div>
    <div class="body">
        <p>Hi {{ $booking->guest->first_name ?? $booking->guest->name }},</p>
        <p>Unfortunately, the host has declined your booking request for <strong>{{ $booking->accommodation->title }}</strong>.</p>

        <div style="margin: 24px 0;">
            <div class="detail-row">
                <span class="label">Property</span>
                <span class="value">{{ $booking->accommodation->title }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Check-in</span>
                <span class="value">{{ $booking->check_in->format('D, M j, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Check-out</span>
                <span class="value">{{ $booking->check_out->format('D, M j, Y') }}</span>
            </div>
            @if($booking->decline_reason)
            <div class="detail-row">
                <span class="label">Reason</span>
                <span class="value">{{ $booking->decline_reason }}</span>
            </div>
            @endif
        </div>

        <p>No payment has been taken. You can search for other available properties on Acomody.</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Acomody &mdash; All rights reserved.
    </div>
</div>
</body>
</html>
