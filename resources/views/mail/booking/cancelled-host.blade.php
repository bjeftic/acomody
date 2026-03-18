@extends('layouts.email')

@section('title', 'Booking Cancelled by Guest')
@section('header-bg', '#dc2626')
@section('header-title', 'Booking Cancelled by Guest')

@section('content')
<p>Hi {{ $booking->host->first_name ?? $booking->host->name }},</p>
<p>A guest has cancelled their booking for <strong>{{ $booking->accommodation->title }}</strong>. The dates are now available again.</p>

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
    @if($booking->cancellation_reason)
    <div class="detail-row">
        <span class="label">Reason</span>
        <span class="value">{{ $booking->cancellation_reason }}</span>
    </div>
    @endif
</div>
@endsection
