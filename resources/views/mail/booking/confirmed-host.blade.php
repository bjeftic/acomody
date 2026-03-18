@extends('layouts.email')

@section('title', 'New Booking Confirmed')
@section('header-bg', '#16a34a')
@section('header-title', '✓ New Booking Confirmed')

@section('content')
<p>Hi {{ $booking->host->first_name ?? $booking->host->name }},</p>
<p>A new booking for <strong>{{ $booking->accommodation->title }}</strong> has been confirmed. Here are the details:</p>

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
        <span class="label">Booking ID</span>
        <span class="value" style="font-size: 12px;">{{ $booking->id }}</span>
    </div>
    <div class="detail-row">
        <span class="label">Total</span>
        <span class="value value-total">{{ strtoupper($booking->currency) }} {{ number_format($booking->total_price, 2) }}</span>
    </div>
</div>

@if($booking->guest_notes)
    <p><strong>Guest notes:</strong> {{ $booking->guest_notes }}</p>
@endif

<div class="btn-wrap">
    <a href="{{ config('app.frontend_url') }}/hosting/calendar" class="btn btn-success">View in Dashboard</a>
</div>
@endsection
