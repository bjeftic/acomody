@extends('layouts.email')

@section('title', 'Reset Your Password')
@section('header-title', 'Reset Your Password')

@section('content')
<p>Hi {{ $user->email }},</p>
<p>You are receiving this email because we received a password reset request for your account. Click the button below to reset your password.</p>

<div class="btn-wrap">
    <a href="{{ $resetUrl }}" class="btn">Reset Password</a>
</div>

<p class="note">This password reset link will expire in {{ config('auth.passwords.users.expire', 60) }} minutes.</p>
<p class="note">If you did not request a password reset, no further action is required.</p>
<p class="note">If the button above does not work, copy and paste the following link into your browser:<br>
    <a href="{{ $resetUrl }}" style="color: #E11D48; word-break: break-all;">{{ $resetUrl }}</a>
</p>
@endsection
