@extends('layouts.email')

@section('title', __('mail.booking_cancelled_guest.title'))
@section('header-bg', '#dc2626')
@section('header-title', __('mail.booking_cancelled_guest.title'))

@section('content')
<p>{{ __('mail.hi', ['name' => $booking->guest->first_name ?? $booking->guest->name]) }}</p>
<p>{{ __('mail.booking_cancelled_guest.body', ['property' => $booking->accommodation->title]) }}</p>

<div class="details">
    <div class="detail-row">
        <span class="label">{{ __('mail.label_check_in') }}</span>
        <span class="value">{{ $booking->check_in->format('D, M j, Y') }}</span>
    </div>
    <div class="detail-row">
        <span class="label">{{ __('mail.label_check_out') }}</span>
        <span class="value">{{ $booking->check_out->format('D, M j, Y') }}</span>
    </div>
    @if($booking->cancellation_reason)
    <div class="detail-row">
        <span class="label">{{ __('mail.label_reason') }}</span>
        <span class="value">{{ $booking->cancellation_reason }}</span>
    </div>
    @endif
    @if($booking->refund_amount > 0)
    <div class="detail-row">
        <span class="label">{{ __('mail.label_refund') }}</span>
        <span class="value value-success">{{ strtoupper($booking->currency) }} {{ number_format($booking->refund_amount, 2) }}</span>
    </div>
    @else
    <div class="detail-row">
        <span class="label">{{ __('mail.label_refund') }}</span>
        <span class="value value-danger">{{ __('mail.no_refund') }}</span>
    </div>
    @endif
</div>

<p class="note">{{ __('mail.booking_cancelled_guest.note') }}</p>
@endsection
