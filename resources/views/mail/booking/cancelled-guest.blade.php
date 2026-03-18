@extends('layouts.email')

@section('title', 'Booking Cancelled')
@section('header-bg', '#dc2626')
@section('header-title', 'Booking Cancelled')

@section('content')
<p>Hi {{ $booking->guest->first_name ?? $booking->guest->name }},</p>
<p>Your booking for <strong>{{ $booking->accommodation->title }}</strong> has been cancelled.</p>

<div class="details">
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
    @if($booking->refund_amount > 0)
    <div class="detail-row">
        <span class="label">Refund</span>
        <span class="value value-success">{{ strtoupper($booking->currency) }} {{ number_format($booking->refund_amount, 2) }}</span>
    </div>
    @else
    <div class="detail-row">
        <span class="label">Refund</span>
        <span class="value value-danger">No refund (non-refundable policy)</span>
    </div>
    @endif
</div>

<p class="note">If you believe this is an error, please contact us at Acomody.</p>
@endsection
