<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Booking Request</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; color: #333; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #7c3aed; color: #fff; padding: 32px; text-align: center; }
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
        <h1>📩 New Booking Request</h1>
    </div>
    <div class="body">
        <p>Hi {{ $booking->host->first_name ?? $booking->host->name }},</p>
        <p>You have a new booking request for <strong>{{ $booking->accommodation->title }}</strong>. Please log in to confirm or decline.</p>

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
            <div class="detail-row">
                <span class="label">Nights</span>
                <span class="value">{{ $booking->nights }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Guests</span>
                <span class="value">{{ $booking->guests }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Total</span>
                <span class="value">{{ strtoupper($booking->currency) }} {{ number_format($booking->total_price, 2) }}</span>
            </div>
        </div>

        @if($booking->guest_notes)
            <p><strong>Guest notes:</strong> {{ $booking->guest_notes }}</p>
        @endif

        <p style="color:#666; font-size:13px;">Log in to your Acomody host dashboard to respond to this request.</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Acomody &mdash; All rights reserved.
    </div>
</div>
</body>
</html>
