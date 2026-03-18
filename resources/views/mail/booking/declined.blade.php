@extends('layouts.email')

@section('title', 'Booking Request Declined')
@section('header-bg', '#dc2626')
@section('header-title', 'Booking Request Declined')

@section('content')
<p>Hi {{ $booking->guest->first_name ?? $booking->guest->name }},</p>
<p>Unfortunately, the host has declined your booking request for <strong>{{ $booking->accommodation->title }}</strong>.</p>

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
    @if($booking->decline_reason)
    <div class="detail-row">
        <span class="label">Reason</span>
        <span class="value">{{ $booking->decline_reason }}</span>
    </div>
    @endif
</div>

<p>No payment has been taken. You can search for other available properties on Acomody.</p>
@endsection
