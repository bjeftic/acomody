@extends('layouts.email')

@section('title', __('mail.booking_declined.title'))
@section('header-bg', '#dc2626')
@section('header-title', __('mail.booking_declined.title'))

@section('content')
<p>{{ __('mail.hi', ['name' => $booking->guest->first_name ?? $booking->guest->name]) }}</p>
<p>{{ __('mail.booking_declined.body', ['property' => $booking->accommodation->title]) }}</p>

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
    @if($booking->decline_reason)
    <div class="detail-row">
        <span class="label">{{ __('mail.label_reason') }}</span>
        <span class="value">{{ $booking->decline_reason }}</span>
    </div>
    @endif
</div>

<p>{{ __('mail.booking_declined.footer') }}</p>
@endsection
