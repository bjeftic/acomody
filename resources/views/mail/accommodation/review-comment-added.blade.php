@extends('layouts.email')

@section('title', 'Reviewer Comment')
@section('header-title', 'Reviewer Comment')

@section('content')
<p>Hi {{ $draft->user->first_name ?? $draft->user->name }},</p>
<p>Our review team has left a comment on your accommodation submission <strong>{{ json_decode($draft->data, true)['title'] ?? 'your property' }}</strong>.</p>

<div class="alert alert-info">
    <p>{{ $comment->body }}</p>
</div>

<p>Please review the comment and make any necessary updates to your submission.</p>
<p class="note">If you have any questions, please contact our support team.</p>
@endsection
