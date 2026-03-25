@extends('layouts.email')

@section('title', __('mail.booking_requested_guest.title'))
@section('header-title', __('mail.booking_requested_guest.title'))

@section('content')
<p>{{ __('mail.hi', ['name' => $booking->guest->first_name ?? $booking->guest->name]) }}</p>
<p>{{ __('mail.booking_requested_guest.body') }}</p>

<div class="details">
    <div class="detail-row">
        <span class="label">{{ __('mail.label_property') }}</span>
        <span class="value">{{ $booking->accommodation->title }}</span>
    </div>
    <div class="detail-row">
        <span class="label">{{ __('mail.label_check_in') }}</span>
        <span class="value">{{ $booking->check_in->format('D, M j, Y') }}</span>
    </div>
    <div class="detail-row">
        <span class="label">{{ __('mail.label_check_out') }}</span>
        <span class="value">{{ $booking->check_out->format('D, M j, Y') }}</span>
    </div>
    <div class="detail-row">
        <span class="label">{{ __('mail.label_nights') }}</span>
        <span class="value">{{ $booking->nights }}</span>
    </div>
    <div class="detail-row">
        <span class="label">{{ __('mail.label_guests') }}</span>
        <span class="value">{{ $booking->guests }}</span>
    </div>
    <div class="detail-row">
        <span class="label">{{ __('mail.label_estimated_total') }}</span>
        <span class="value">{{ strtoupper($booking->currency) }} {{ number_format($booking->total_price, 2) }}</span>
    </div>
</div>

<p class="note">{{ __('mail.booking_requested_guest.note') }}</p>
@endsection
