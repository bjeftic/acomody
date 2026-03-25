@extends('layouts.email')

@section('title', __('mail.accommodation_rejected.title'))
@section('header-bg', '#dc2626')
@section('header-title', __('mail.accommodation_rejected.title'))

@section('content')
<p>{{ __('mail.hi', ['name' => $draft->user->first_name ?? $draft->user->name]) }}</p>
<p>{{ __('mail.accommodation_rejected.body1', ['property' => json_decode($draft->data, true)['title'] ?? 'your property']) }}</p>
<p>{{ __('mail.accommodation_rejected.body2') }}</p>

@if($reason)
<div class="alert alert-error">
    <strong>{{ __('mail.accommodation_rejected.reason_label') }}</strong>
    <p>{{ $reason }}</p>
</div>
@endif

<p>{{ __('mail.accommodation_rejected.resubmit') }}</p>
<p class="note">{{ __('mail.support_note') }}</p>
@endsection
