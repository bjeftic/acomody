@extends('layouts.email')

@section('title', 'New Booking Request')
@section('header-title', 'New Booking Request')

@section('content')
<p>Hi {{ $booking->host->first_name ?? $booking->host->name }},</p>
<p>You have a new booking request for <strong>{{ $booking->accommodation->title }}</strong>. Please log in to confirm or decline.</p>

<div class="details">
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

<div class="btn-wrap">
    <a href="{{ config('app.frontend_url') }}/hosting/calendar" class="btn">Review Request</a>
</div>

<p class="note" style="text-align: center;">
    Or log in to your <a href="{{ config('app.frontend_url') }}/hosting/calendar" style="color: #E11D48;">host dashboard</a> to confirm or decline.
</p>
@endsection
