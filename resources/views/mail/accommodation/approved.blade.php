@extends('layouts.email')

@section('title', __('mail.accommodation_approved.title'))
@section('header-bg', '#16a34a')
@section('header-title', __('mail.accommodation_approved.title'))

@section('content')
<p>{{ __('mail.hi', ['name' => $draft->user->first_name ?? $draft->user->name]) }}</p>
<p>{{ __('mail.accommodation_approved.body', ['property' => json_decode($draft->data, true)['title'] ?? 'your property']) }}</p>

@if($hostProfileComplete)
    <p>{{ __('mail.accommodation_approved.now_live') }}</p>
@else
    <div class="alert alert-warning">
        <strong>{{ __('mail.accommodation_approved.warning_title') }}</strong>
        <p>{{ __('mail.accommodation_approved.warning_body') }}</p>
        <div style="margin-top: 12px;">
            <a href="{{ config('app.url') }}/hosting/profile" class="btn btn-amber btn-sm">
                {{ __('mail.accommodation_approved.warning_btn') }}
            </a>
        </div>
    </div>
@endif

<p class="note">{{ __('mail.support_note') }}</p>
@endsection
