<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Digital Yug Panel') }} - Login</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card fade-in-up">
            <div class="auth-header">
                <img src="{{ asset('images/logo-white.png') }}" alt="Digital Yug" class="auth-logo" onerror="this.src='{{ asset('logo.webp') }}'">
                <h3>Digital Yug</h3>
                <p>Employee Management Panel</p>
            </div>

            {{ $slot }}

            <div class="auth-footer">
                <p class="text-white-50 small">&copy; {{ date('Y') }} Digital Yug. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
