@extends('layouts.email')

@section('title', 'Accommodation Under Review')
@section('header-title', 'Accommodation Under Review')

@section('content')
<p>Hi {{ $draft->user->first_name ?? $draft->user->name }},</p>
<p>
    Your accommodation <strong>{{ json_decode($draft->data, true)['title'] ?? 'your property' }}</strong>
    has been successfully submitted and is now under review by our team.
</p>

<p>Here's what happens next:</p>
<ul class="steps">
    <li>
        <span class="step-num">1</span>
        <span>Our team reviews your listing — this usually takes 1–2 business days.</span>
    </li>
    <li>
        <span class="step-num">2</span>
        <span>Once approved, your listing will automatically become searchable and guests can start booking.</span>
    </li>
    <li>
        <span class="step-num">3</span>
        <span>You'll receive an email notification as soon as it goes live.</span>
    </li>
</ul>

<div class="btn-wrap">
    <a href="{{ config('app.url') }}/hosting/dashboard" class="btn">Go to hosting dashboard &rarr;</a>
</div>

<p class="note">If you have any questions, please contact our support team.</p>
@endsection
