@extends('layouts.email')

@section('title', __('mail.draft_submitted.title'))
@section('header-title', __('mail.draft_submitted.title'))

@section('content')
<p>{{ __('mail.hi', ['name' => $draft->user->first_name ?? $draft->user->name]) }}</p>
<p>{{ __('mail.draft_submitted.body', ['property' => json_decode($draft->data, true)['title'] ?? 'your property']) }}</p>

<p>{{ __('mail.draft_submitted.next') }}</p>
<ul class="steps">
    <li>
        <span class="step-num">1</span>
        <span>{{ __('mail.draft_submitted.step1') }}</span>
    </li>
    <li>
        <span class="step-num">2</span>
        <span>{{ __('mail.draft_submitted.step2') }}</span>
    </li>
    <li>
        <span class="step-num">3</span>
        <span>{{ __('mail.draft_submitted.step3') }}</span>
    </li>
</ul>

<div class="btn-wrap">
    <a href="{{ config('app.url') }}/hosting/dashboard" class="btn">{{ __('mail.draft_submitted.btn') }}</a>
</div>

<p class="note">{{ __('mail.support_note') }}</p>
@endsection
