@extends('layouts.email')

@section('title', 'Verify Your Email Address')
@section('header-title', 'Verify Your Email Address')

@section('content')
<p>Hi {{ $user->email }},</p>
<p>Thank you for registering with {{ config('app.name') }}. Please verify your email address by clicking the button below.</p>

<div class="btn-wrap">
    <a href="{{ $verificationUrl }}" class="btn">Verify Email Address</a>
</div>

<p class="note">This link will expire in {{ config('auth.verification.expire', 60) }} minutes.</p>
<p class="note">If you did not create an account, no further action is required.</p>
<p class="note">If the button above does not work, copy and paste the following link into your browser:<br>
    <a href="{{ $verificationUrl }}" style="color: #E11D48; word-break: break-all;">{{ $verificationUrl }}</a>
</p>
@endsection
