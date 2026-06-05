<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>B-Triad Jiu-Jitsu | Aulas de jiu-jitsu para crianças e adultos</title>
    <meta name="description"
        content="Equipe B-Triad Jiu-Jitsu em São Vicente (SP): aulas para crianças e adultos na R. Cel. Antônio Pietscher, 160. Horários segundas, quartas e sextas. Aula experimental pelo WhatsApp.">
    <link rel="canonical" href="{{ url('/') }}">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#1b1b18">

    <meta property="og:locale" content="pt_BR">
    <meta property="og:title" content="B-Triad Jiu-Jitsu | Equipe e horários">
    <meta property="og:description"
        content="Aulas de jiu-jitsu para crianças e adultos em São Vicente (SP). Segundas, quartas e sextas. R. Cel. Antônio Pietscher, 160 — Vila Jockei Clube.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ url(asset('logo.png')) }}">
    <meta property="og:image:alt" content="Logotipo Equipe B-Triad Jiu-Jitsu">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="B-Triad Jiu-Jitsu | Equipe e horários">
    <meta name="twitter:description"
        content="Aulas em São Vicente (SP), Vila Jockei Clube. Segundas, quartas e sextas. Aula experimental pelo WhatsApp.">
    <meta name="twitter:image" content="{{ url(asset('logo.png')) }}">

    <link rel="icon" href="/img/logo/triangulo.png" type="image/x-icon">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'WebSite',
            'name' => 'B-Triad Jiu-Jitsu',
            'url' => url('/'),
            'inLanguage' => 'pt-BR',
            'description' => 'Site da Equipe B-Triad Jiu-Jitsu — aulas, horários e contato.',
        ],
        [
            '@type' => 'Organization',
            '@id' => url('/').'#organization',
            'name' => 'Equipe B-Triad Jiu-Jitsu',
            'url' => url('/'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => url(asset('logo.png')),
            ],
            'image' => url(asset('logo.png')),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'R. Cel. Antônio Pietscher, 160 - Vila Jockei Clube',
                'addressLocality' => 'São Vicente',
                'addressRegion' => 'SP',
                'postalCode' => '11360-330',
                'addressCountry' => 'BR',
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => -23.9455525,
                'longitude' => -46.3987499,
            ],
            'hasMap' => 'https://www.google.com/maps/place/Equipe+B-Triad+Jiu-Jitsu/@-23.9455525,-46.3987499,17z',
            'sameAs' => [
                'https://www.instagram.com/equipe.btriad.jiujitsu',
                'https://wa.me/5513981245120',
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>

    <!-- Styles / Scripts -->
    @vite(['resources/css/index.css', 'resources/js/index.js'])
    @stack('styles')
</head>

<body>
    <a class="skip-link" href="#conteudo-principal">Ir para o conteúdo</a>
    @include('home.header')

    <main id="conteudo-principal" class="page-sections">
        <section id="sobre" class="page-section page-section--hero" aria-labelledby="sobre-heading">
            <div class="page-section__inner page-section__inner--hero">
                <img class="page-section__logo" src="{{ asset('logo.png') }}"
                    alt="Logotipo Equipe B-Triad Jiu-Jitsu" decoding="async">
                <h1 id="sobre-heading" class="page-section__title"><strong>Estamos no aquecimento!</strong></h1>
                <p class="page-section__lead">
                    aaaaEm breve, o site oficial da <strong>Equipe B-Triad Jiu-Jitsu.</strong>
                    Fique ligado para novidades sobre nossas aulas, horários e eventos!
                </p>
            </div>
        </section>

        <section id="horarios" class="page-section" aria-labelledby="horarios-heading">
            <div class="page-section__inner page-section__inner--wide page-section__inner--stack">
                <h2 id="horarios-heading" class="page-section__heading page-section__heading--center">Horários</h2>
                <p class="page-section__intro page-section__text">
                    Aulas às <strong>segundas</strong>, <strong>quartas</strong> e <strong>sextas-feiras</strong>.
                </p>

                <div class="schedule-table-wrap">
                    <table class="schedule-table">
                        <caption class="visually-hidden">Grade de horários por dia e faixa etária</caption>
                        <thead>
                            <tr>
                                <th scope="col" class="schedule-table__col-day">Dia</th>
                                <th scope="col">Crianças</th>
                                <th scope="col">Adultos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row" class="schedule-table__col-day">Segunda-feira</th>
                                <td>18h – 19h</td>
                                <td>19h – 20h</td>
                            </tr>
                            <tr>
                                <th scope="row" class="schedule-table__col-day">Quarta-feira</th>
                                <td>18h – 19h</td>
                                <td>19h – 20h</td>
                            </tr>
                            <tr>
                                <th scope="row" class="schedule-table__col-day">Sexta-feira</th>
                                <td>18h – 19h</td>
                                <td>19h – 20h</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="avaliacoes" class="page-section" aria-labelledby="avaliacoes-heading">
            <div class="page-section__inner page-section__inner--stack">
                <h2 id="avaliacoes-heading" class="page-section__heading">Avaliações</h2>
                <p class="page-section__text">Depoimentos em breve.</p>
            </div>
        </section>

        <section id="localizacao" class="page-section" aria-labelledby="localizacao-heading">
            <div class="page-section__inner page-section__inner--wide page-section__inner--stack">
                <h2 id="localizacao-heading" class="page-section__heading page-section__heading--center">Localização</h2>

                <address class="location-address">
                    <span class="location-address__name">Equipe B-Triad Jiu-Jitsu</span><br>
                    R. Cel. Antônio Pietscher, 160 — Vila Jockei Clube<br>
                    São Vicente, SP — CEP 11360-330<br>
                    Brasil
                </address>
                <div class="location-map">
                    <iframe
                        src="https://www.google.com/maps?q=-23.9455525,-46.3987499&amp;z=17&amp;output=embed"
                        title="Mapa do Google: localização da Equipe B-Triad Jiu-Jitsu em São Vicente"
                        width="600"
                        height="350"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen></iframe>
                </div>

                <a class="location-cta" href="https://www.google.com/maps/dir/?api=1&amp;destination={{ rawurlencode('R. Cel. Antônio Pietscher, 160 - Vila Jockei Clube, São Vicente - SP, 11360-330, Brasil') }}"
                    target="_blank" rel="noopener noreferrer">
                    Como chegar
                </a>
            </div>
        </section>
    </main>

    @include('home.footer')
</body>

</html>
