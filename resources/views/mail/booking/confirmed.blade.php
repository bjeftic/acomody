@extends('layouts.email')

@section('title', 'Booking Confirmed')
@section('header-bg', '#16a34a')
@section('header-title', '✓ Booking Confirmed')

@section('content')
<p>Hi {{ $booking->guest->first_name ?? $booking->guest->name }},</p>
<p>Your booking has been confirmed. Here are the details:</p>

<div class="details">
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
        <span class="value" style="font-size: 12px;">{{ $booking->id }}</span>
    </div>
    <div class="detail-row">
        <span class="label">Total</span>
        <span class="value value-total">{{ strtoupper($booking->currency) }} {{ number_format($booking->total_price, 2) }}</span>
    </div>
</div>

@if($booking->guest_notes)
    <p><strong>Your notes:</strong> {{ $booking->guest_notes }}</p>
@endif

<p class="note">If you have any questions, please contact the host directly through Acomody.</p>
@endsection
