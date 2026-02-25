<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; color: #333; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #16a34a; color: #fff; padding: 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .body { padding: 32px; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .detail-row:last-child { border-bottom: none; }
        .label { color: #666; font-size: 14px; }
        .value { font-weight: 600; font-size: 14px; }
        .total { font-size: 18px; font-weight: 700; color: #16a34a; }
        .footer { background: #f9f9f9; padding: 20px 32px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>✓ Booking Confirmed</h1>
    </div>
    <div class="body">
        <p>Hi {{ $booking->guest->first_name ?? $booking->guest->name }},</p>
        <p>Your booking has been confirmed. Here are the details:</p>

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
            <div class="detail-row">
                <span class="label">Nights</span>
                <span class="value">{{ $booking->nights }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Guests</span>
                <span class="value">{{ $booking->guests }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Booking ID</span>
                <span class="value" style="font-size:12px;">{{ $booking->id }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Total</span>
                <span class="value total">{{ strtoupper($booking->currency) }} {{ number_format($booking->total_price, 2) }}</span>
            </div>
        </div>

        @if($booking->guest_notes)
            <p><strong>Your notes:</strong> {{ $booking->guest_notes }}</p>
        @endif

        <p style="color:#666; font-size:13px;">If you have any questions, please contact the host directly through Acomody.</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Acomody &mdash; All rights reserved.
    </div>
</div>
</body>
</html>
