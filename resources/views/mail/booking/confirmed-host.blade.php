@extends('layouts.email')

@section('title', __('mail.booking_confirmed_host.title'))
@section('header-bg', '#16a34a')
@section('header-title', __('mail.booking_confirmed_host.title'))

@section('content')
<p>{{ __('mail.hi', ['name' => $booking->host->first_name ?? $booking->host->name]) }}</p>
<p>{{ __('mail.booking_confirmed_host.body', ['property' => $booking->accommodation->title]) }}</p>

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
    <div class="detail-row">
        <span class="label">{{ __('mail.label_nights') }}</span>
        <span class="value">{{ $booking->nights }}</span>
    </div>
    <div class="detail-row">
        <span class="label">{{ __('mail.label_guests') }}</span>
        <span class="value">{{ $booking->guests }}</span>
    </div>
    <div class="detail-row">
        <span class="label">{{ __('mail.label_booking_id') }}</span>
        <span class="value" style="font-size: 12px;">{{ $booking->id }}</span>
    </div>
    <div class="detail-row">
        <span class="label">{{ __('mail.label_total') }}</span>
        <span class="value value-total">{{ strtoupper($booking->currency) }} {{ number_format($booking->total_price, 2) }}</span>
    </div>
</div>

@if($booking->guest_notes)
    <p><strong>{{ __('mail.guest_notes_label') }}</strong> {{ $booking->guest_notes }}</p>
@endif

<div class="btn-wrap">
    <a href="{{ config('app.frontend_url') }}/hosting/calendar" class="btn btn-success">{{ __('mail.booking_confirmed_host.btn') }}</a>
</div>
@endsection
