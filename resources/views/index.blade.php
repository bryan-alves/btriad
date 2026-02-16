<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Equipe B-Triad Jiu-Jitsu com aulas para crianças, jovens e adultos.">
    <meta property="og:title" content="B-Triad Jiu-Jitsu">
    <meta property="og:description" content="Equipe B-Triad Jiu-Jitsu com aulas para crianças, jovens e adultos.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('logo.png') }}">
    <title>B-Triad Jiu-Jitsu</title>
    <link rel="icon" href="/logo-b.png">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/index.css', 'resources/js/index.js'])
    @stack('styles')
</head>

<body class="">
    @include('home.header')
    <div class="home-page">
        <h1 class="home-page__title">
            Equipe B-Triad Jiu-Jitsu
        </h1>
        <h2 style="color:#FFF; font-size: 36px">Estamos no aquecimento 🥋.</h2>
        <p style="font-size: 24px;color:#FFF;">Site em construção!</p>
    </div>
</body>

</html>