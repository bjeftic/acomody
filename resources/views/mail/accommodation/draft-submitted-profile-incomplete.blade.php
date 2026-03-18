@extends('layouts.email')

@section('title', 'Accommodation Under Review')
@section('header-title', 'Accommodation Under Review')

@section('content')
<p>Hi {{ $draft->user->first_name ?? $draft->user->name }},</p>
<p>
    Your accommodation <strong>{{ json_decode($draft->data, true)['title'] ?? 'your property' }}</strong>
    has been successfully submitted and is now under review by our team. We'll notify you once it's approved.
</p>

<div class="alert alert-warning">
    <strong>Complete your host profile to go live</strong>
    <p>
        Once your accommodation is approved, it won't appear in search results until your host profile
        is complete. Please add your display name, contact email, and phone number — it only takes a minute.
    </p>
    <div style="margin-top: 12px;">
        <a href="{{ config('app.url') }}/hosting/profile" class="btn btn-amber btn-sm">
            Complete host profile &rarr;
        </a>
    </div>
</div>

<p class="note">If you have any questions, please contact our support team.</p>
@endsection
