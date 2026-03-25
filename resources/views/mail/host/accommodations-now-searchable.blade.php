@extends('layouts.email')

@section('title', __('mail.listings_live.title'))
@section('header-bg', '#16a34a')
@section('header-title', __('mail.listings_live.title'))

@section('content')
<p>{{ __('mail.hi', ['name' => $user->first_name ?? $user->name]) }}</p>
<p>
    {{ $accommodationCount === 1
        ? __('mail.listings_live.body_singular')
        : __('mail.listings_live.body_plural', ['count' => $accommodationCount]) }}
</p>

<div class="btn-wrap">
    <a href="{{ config('app.url') }}/hosting/dashboard" class="btn btn-success">{{ __('mail.listings_live.btn') }}</a>
</div>

<p class="note">{{ __('mail.support_note') }}</p>
@endsection
