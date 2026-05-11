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
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/index.css', 'resources/js/index.js'])
    @stack('styles')
</head>

<body class="">
    @include('home.header')
    <main style="display: flex;
    flex: 1;
    align-items: center;
    padding: 20px;
    flex-direction: column;
    justify-content: center;
    width: 100%;">
        <div>
            <img class="mb-4" src="logo.png" alt="" style="max-width: 350px">

        </div>
        <h1 style="font-size: 26px;"><strong>Estamos no aquecimento!</strong></h1>
        <p style="text-align: center">
            Em breve, o site oficial da <strong>Equipe B-Triad Jiu-Jitsu.</strong> <br />Fique ligado para novidades sobre nossas aulas,
            horários e eventos!
        </p>
    </main>
    @include('home.footer')
</body>

</html>