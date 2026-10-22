<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ mix('css/app.min.css') }}">
</head>

<body data-login-preview="{{ ($loginPreview ?? false) ? 'true' : 'false' }}" data-login-url="{{ route('login', [], false) }}" data-login-store-url="{{ route('login.store', [], false) }}" data-csrf-url="{{ route('sanctum.csrf-cookie', [], false) }}">
    <a class="sr-skip" href="#main-content">Skip to content</a>
    <div id="app">
        <p class="p-4" role="status">Checking your session...</p>
    </div>
    <noscript>
        <p>Enable JavaScript to open your workspace.</p>
    </noscript>
    <script defer src="{{ mix('js/main.min.js') }}"></script>
</body>

</html>