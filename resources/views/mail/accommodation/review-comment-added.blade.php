@extends('layouts.email')

@section('title', __('mail.review_comment.title'))
@section('header-title', __('mail.review_comment.title'))

@section('content')
<p>{{ __('mail.hi', ['name' => $draft->user->first_name ?? $draft->user->name]) }}</p>
<p>{{ __('mail.review_comment.body1', ['property' => (function() use ($draft) { $t = json_decode($draft->data, true)['title'] ?? null; return is_array($t) ? ($t['en'] ?? 'your property') : ($t ?: 'your property'); })()]) }}</p>

<div class="alert alert-info">
    <p>{{ $comment->body }}</p>
</div>

<p>{{ __('mail.review_comment.body2') }}</p>
<p class="note">{{ __('mail.support_note') }}</p>
@endsection
