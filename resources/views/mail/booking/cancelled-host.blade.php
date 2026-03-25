@extends('layouts.email')

@section('title', __('mail.booking_cancelled_host.title'))
@section('header-bg', '#dc2626')
@section('header-title', __('mail.booking_cancelled_host.title'))

@section('content')
<p>{{ __('mail.hi', ['name' => $booking->host->first_name ?? $booking->host->name]) }}</p>
<p>{{ __('mail.booking_cancelled_host.body', ['property' => $booking->accommodation->title]) }}</p>

<div class="details">
    <div class="detail-row">
        <span class="label">{{ __('mail.label_guest') }}</span>
        <span class="value">{{ $booking->guest->first_name ?? $booking->guest->name }} {{ $booking->guest->last_name ?? '' }}</span>
    </div>
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
</div>
@endsection
