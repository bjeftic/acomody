@extends('layouts.email')

@section('title', 'Booking Request Submitted')
@section('header-title', 'Booking Request Submitted')

@section('content')
<p>Hi {{ $booking->guest->first_name ?? $booking->guest->name }},</p>
<p>Your booking request has been sent to the host. You will receive a confirmation once they respond.</p>

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
        <span class="label">Estimated Total</span>
        <span class="value">{{ strtoupper($booking->currency) }} {{ number_format($booking->total_price, 2) }}</span>
    </div>
</div>

<p class="note">Dates will not be blocked until the host confirms your request.</p>
@endsection
