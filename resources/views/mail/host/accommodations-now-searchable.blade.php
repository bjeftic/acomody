@extends('layouts.email')

@section('title', 'Your Listings Are Now Live')
@section('header-bg', '#16a34a')
@section('header-title', '✓ Your Listings Are Now Live!')

@section('content')
<p>Hi {{ $user->first_name ?? $user->name }},</p>
<p>
    Your host profile is now complete — your
    {{ $accommodationCount === 1 ? 'listing is' : $accommodationCount . ' listings are' }}
    now searchable on Acomody and guests can start booking.
</p>

<div class="btn-wrap">
    <a href="{{ config('app.url') }}/hosting/dashboard" class="btn btn-success">Go to hosting dashboard &rarr;</a>
</div>

<p class="note">If you have any questions, please contact our support team.</p>
@endsection
