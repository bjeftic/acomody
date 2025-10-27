<!-- General purpose meta tags -->
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">

@php
  $constants = \App\Helpers\RuntimeConstants::get();
@endphp

@foreach($constants as $name => $constant)
  @php
    $isJson = is_object($constant) || is_array($constant) || is_bool($constant) || is_null($constant)
  @endphp
  <meta
    name="{{ $name }}"
    content="{{ $isJson ? json_encode($constant) : $constant }}"
    {{ $isJson ? 'data-is-json' : '' }}
  >
@endforeach

<!-- Favicon images -->
<!-- <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="64x64" href="/favicons/favicon-64x64.png">
<link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="192x192" href="/favicons/android-chrome-192x192.png">
<link rel="icon" type="image/png" sizes="512x512" href="/favicons/android-chrome-512x512.png"> -->

