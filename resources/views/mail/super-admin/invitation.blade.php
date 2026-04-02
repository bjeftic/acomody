@extends('layouts.email')

@section('title', 'Superadmin Invitation')
@section('header-title', 'Superadmin Invitation')

@section('content')
<p>Hi {{ $user->email }},</p>
<p>You have been invited to access the <strong>Acomody Superadmin</strong> panel.</p>
<p>Click the button below to set your password and activate your account.</p>

<div class="btn-wrap">
    <a href="{{ $setPasswordUrl }}" class="btn">Set your password</a>
</div>

<p class="note">This link expires in {{ config('auth.passwords.users.expire', 60) }} minutes.</p>
<p class="note">If you did not expect this invitation, you can safely ignore this email.</p>
<p class="note">Or copy this link into your browser:<br>
    <a href="{{ $setPasswordUrl }}" style="color: #E11D48; word-break: break-all;">{{ $setPasswordUrl }}</a>
</p>
@endsection
