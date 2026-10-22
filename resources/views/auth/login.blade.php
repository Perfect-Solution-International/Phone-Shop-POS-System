<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in ? {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ mix('css/app.min.css') }}">
</head>
<body>
    <div id="login"><p class="p-4" role="status">Loading sign in?</p></div>
    <noscript><p>Enable JavaScript to sign in.</p></noscript>
    <script defer src="{{ mix('js/login.min.js') }}"></script>
</body>
</html>
