<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'BudgetBean') }}</title>

        <!-- Favicons -->
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <link rel="icon" type="image/png" sizes="16x16" href="/images/icons/favicon-16x16.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/images/icons/favicon-32x32.png">

        <!-- Apple Touch Icons -->
        <link rel="apple-touch-icon" href="/images/icons/apple-touch-icon.png">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="BudgetBean">

        <!-- Android Chrome Icons -->
        <link rel="icon" type="image/png" sizes="192x192" href="/images/icons/android-chrome-192x192.png">
        <link rel="icon" type="image/png" sizes="512x512" href="/images/icons/android-chrome-512x512.png">

        <!-- Web App Manifest -->
        <link rel="manifest" href="/site.webmanifest">

        <!-- Open Graph Meta Tags (for social media sharing) -->
        <meta property="og:title" content="BudgetBean">
        <meta property="og:description" content="Your budget management app">
        <meta property="og:image" content="{{ asset('images/BudgetBean.png') }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:type" content="website">

        <!-- Twitter Card Meta Tags -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="BudgetBean">
        <meta name="twitter:description" content="Your budget management app">
        <meta name="twitter:image" content="{{ asset('images/BudgetBean.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
