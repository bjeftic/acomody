@extends('layouts.email')

@section('title', 'Accommodation Approved')
@section('header-bg', '#16a34a')
@section('header-title', '✓ Accommodation Approved')

@section('content')
<p>Hi {{ $draft->user->first_name ?? $draft->user->name }},</p>
<p>Great news! Your accommodation <strong>{{ json_decode($draft->data, true)['title'] ?? 'your property' }}</strong> has been reviewed and approved by our team.</p>

@if($hostProfileComplete)
    <p>Your listing is now live on Acomody and guests can start searching and booking.</p>
@else
    <div class="alert alert-warning">
        <strong>One more step to go live</strong>
        <p>
            Your listing has been approved but it won't appear in search results until you complete your host profile
            (display name, contact email, and phone number). It only takes a minute.
        </p>
        <div style="margin-top: 12px;">
            <a href="{{ config('app.url') }}/hosting/profile" class="btn btn-amber btn-sm">
                Complete host profile &rarr;
            </a>
        </div>
    </div>
@endif

<p class="note">If you have any questions, please contact our support team.</p>
@endsection
