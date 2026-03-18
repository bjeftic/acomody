@extends('layouts.email')

@section('title', 'Accommodation Not Approved')
@section('header-bg', '#dc2626')
@section('header-title', 'Accommodation Not Approved')

@section('content')
<p>Hi {{ $draft->user->first_name ?? $draft->user->name }},</p>
<p>Thank you for submitting your accommodation <strong>{{ json_decode($draft->data, true)['title'] ?? 'your property' }}</strong> for review.</p>
<p>After careful review, our team was unable to approve your submission at this time.</p>

@if($reason)
<div class="alert alert-error">
    <strong>Reason</strong>
    <p>{{ $reason }}</p>
</div>
@endif

<p>If you believe this was a mistake or have made the necessary changes, you are welcome to re-submit your accommodation for review.</p>
<p class="note">If you have any questions, please contact our support team.</p>
@endsection
