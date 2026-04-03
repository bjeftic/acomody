@extends('layouts.email')

@section('title', 'Superadmin Password Reset')
@section('header-title', 'Superadmin Password Reset')

@section('content')
<p>Hi {{ $user->email }},</p>
<p>You requested a password reset for your <strong>Acomody Superadmin</strong> account.</p>
<p>Click the button below to set a new password.</p>

<div class="btn-wrap">
    <a href="{{ $resetUrl }}" class="btn">Reset your password</a>
</div>

<p class="note">This link expires in {{ config('auth.passwords.users.expire', 60) }} minutes.</p>
<p class="note">If you did not request a password reset, no action is needed.</p>
<p class="note">Or copy this link into your browser:<br>
    <a href="{{ $resetUrl }}" style="color: #E11D48; word-break: break-all;">{{ $resetUrl }}</a>
</p>
@endsection
