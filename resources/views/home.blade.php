<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Phone shop operations and employee management from Perfect Solution International.">
    <link rel="canonical" href="{{ route('home') }}">
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ mix('css/app.min.css') }}">
</head>
<body>
    <main class="container py-5">
        <p class="text-uppercase text-muted">Perfect Solution International</p>
        <h1>{{ config('app.name') }}</h1>
        <p class="lead my-4">A workspace for your team and daily operations.</p>
        <a class="btn btn-primary" href="{{ route('login') }}">Sign in to your workspace</a>
    </main>
</body>
</html>
