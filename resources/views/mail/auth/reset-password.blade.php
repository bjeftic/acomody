@extends('layouts.email')

@section('title', __('mail.reset_password.title'))
@section('header-title', __('mail.reset_password.title'))

@section('content')
<p>{{ __('mail.hi', ['name' => $user->email]) }}</p>
<p>{{ __('mail.reset_password.body') }}</p>

<div class="btn-wrap">
    <a href="{{ $resetUrl }}" class="btn">{{ __('mail.reset_password.btn') }}</a>
</div>

<p class="note">{{ __('mail.reset_password.expire_note', ['minutes' => config('auth.passwords.users.expire', 60)]) }}</p>
<p class="note">{{ __('mail.reset_password.no_request_note') }}</p>
<p class="note">{{ __('mail.reset_password.fallback_note') }}<br>
    <a href="{{ $resetUrl }}" style="color: #E11D48; word-break: break-all;">{{ $resetUrl }}</a>
</p>
@endsection
