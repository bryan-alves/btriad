<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tenant->name }}</title>
    @vite(['resources/css/index.css', 'resources/js/index.js'])
</head>
<body>
    <main style="max-width: 40rem; margin: 4rem auto; padding: 0 1rem; font-family: system-ui, sans-serif;">
        <h1>{{ $tenant->name }}</h1>
        <p>Página inicial do tenant <strong>{{ $tenant->slug }}</strong>.</p>
        <p>Personalize este arquivo em <code>resources/views/tenants/apjiujitsu/index.blade.php</code>.</p>
    </main>
</body>
</html>
