@extends('layouts.email')

@section('title', __('mail.verify_email.title'))
@section('header-title', __('mail.verify_email.title'))

@section('content')
<p>{{ __('mail.hi', ['name' => $user->email]) }}</p>
<p>{{ __('mail.verify_email.body', ['app' => config('app.name')]) }}</p>

<div class="btn-wrap">
    <a href="{{ $verificationUrl }}" class="btn">{{ __('mail.verify_email.btn') }}</a>
</div>

<p class="note">{{ __('mail.verify_email.expire_note', ['minutes' => config('auth.verification.expire', 60)]) }}</p>
<p class="note">{{ __('mail.verify_email.no_account_note') }}</p>
<p class="note">{{ __('mail.verify_email.fallback_note') }}<br>
    <a href="{{ $verificationUrl }}" style="color: #E11D48; word-break: break-all;">{{ $verificationUrl }}</a>
</p>
@endsection
