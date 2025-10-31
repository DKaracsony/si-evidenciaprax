<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Evidencia praxí') }}</title>
    <meta name="description" content="Platforma na evidenciu študentských praxí.">

    <link rel="icon" href="{{ asset('storage/favicon.ico') }}">

    <!-- Basic Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ config('app.name', 'Evidencia praxí') }}">
    <meta property="og:description" content="Platforma na evidenciu študentských praxí.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('og-image.png') }}">

    @vite(['resources/js/app.js'])
</head>
<body>
<div id="app"></div>
</body>
</html>
