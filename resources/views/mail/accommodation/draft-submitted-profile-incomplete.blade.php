@extends('layouts.email')

@section('title', __('mail.draft_submitted_incomplete.title'))
@section('header-title', __('mail.draft_submitted_incomplete.title'))

@section('content')
<p>{{ __('mail.hi', ['name' => $draft->user->first_name ?? $draft->user->name]) }}</p>
<p>{{ __('mail.draft_submitted_incomplete.body', ['property' => (function() use ($draft) { $t = json_decode($draft->data, true)['title'] ?? null; return is_array($t) ? ($t['en'] ?? 'your property') : ($t ?: 'your property'); })()]) }}</p>

<div class="alert alert-warning">
    <strong>{{ __('mail.draft_submitted_incomplete.warning_title') }}</strong>
    <p>{{ __('mail.draft_submitted_incomplete.warning_body') }}</p>
    <div style="margin-top: 12px;">
        <a href="{{ config('app.url') }}/hosting/profile" class="btn btn-amber btn-sm">
            {{ __('mail.draft_submitted_incomplete.warning_btn') }}
        </a>
    </div>
</div>

<p class="note">{{ __('mail.support_note') }}</p>
@endsection
