<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @php
        $site = $tenant?->site;
        $academyName = $site?->academy_name ?? 'Equipe B-Triad Jiu-Jitsu';
        $pageTitle = $site?->page_title ?: 'B-Triad Jiu-Jitsu | Aulas de jiu-jitsu para crianças e adultos';
        $heroTitle = $site?->hero_title ?: 'Estamos no aquecimento!';
        $heroSubtitle = $site?->hero_subtitle ?: 'Em breve, o site oficial da Equipe B-Triad Jiu-Jitsu. Fique ligado para novidades sobre nossas aulas, horários e eventos!';
        $logoUrl = $site?->logo_url ?? asset('logo.png');
        $carouselImages = $site?->carousel_image_urls ?? [];
        $primaryColor = $site?->primary_color ?? '#c41e3a';
        $backgroundColor = $site?->background_color ?? '#3d3d3d';
        $schedule = $site?->schedule ?: [
            ['day' => 'Segunda-feira', 'kids_time' => '18h - 19h', 'adults_time' => '19h - 20h'],
            ['day' => 'Quarta-feira', 'kids_time' => '18h - 19h', 'adults_time' => '19h - 20h'],
            ['day' => 'Sexta-feira', 'kids_time' => '18h - 19h', 'adults_time' => '19h - 20h'],
        ];
        $reviews = $tenant?->reviews ?? collect();
        $address = $site?->address ?: "R. Cel. Antônio Pietscher, 160 — Vila Jockei Clube\nSão Vicente, SP — CEP 11360-330\nBrasil";
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $pageTitle }}</title>
    <meta name="description"
        content="Equipe B-Triad Jiu-Jitsu em São Vicente (SP): aulas para crianças e adultos na R. Cel. Antônio Pietscher, 160. Horários segundas, quartas e sextas. Aula experimental pelo WhatsApp.">
    <link rel="canonical" href="{{ url('/') }}">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#1b1b18">

    <meta property="og:locale" content="pt_BR">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description"
        content="Aulas de jiu-jitsu para crianças e adultos em São Vicente (SP). Segundas, quartas e sextas. R. Cel. Antônio Pietscher, 160 — Vila Jockei Clube.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ url($logoUrl) }}">
    <meta property="og:image:alt" content="Logotipo {{ $academyName }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description"
        content="Aulas em São Vicente (SP), Vila Jockei Clube. Segundas, quartas e sextas. Aula experimental pelo WhatsApp.">
    <meta name="twitter:image" content="{{ url($logoUrl) }}">

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
            'name' => $academyName,
            'url' => url('/'),
            'inLanguage' => 'pt-BR',
            'description' => 'Site da Equipe B-Triad Jiu-Jitsu — aulas, horários e contato.',
        ],
        [
            '@type' => 'Organization',
            '@id' => url('/').'#organization',
            'name' => $academyName,
            'url' => url('/'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => url($logoUrl),
            ],
            'image' => url($logoUrl),
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

<body style="--site-primary-color: {{ $primaryColor }}; --site-background-color: {{ $backgroundColor }};">
    <a class="skip-link" href="#conteudo-principal">Ir para o conteúdo</a>
    @include('home.header')

    <main id="conteudo-principal" class="page-sections">
        <section id="sobre" class="page-section page-section--hero" aria-labelledby="sobre-heading">
            <div class="page-section__inner page-section__inner--hero">
                @if (! empty($carouselImages))
                    <div class="hero-carousel" data-hero-carousel>
                        <div class="hero-carousel__track">
                            @foreach ($carouselImages as $imageUrl)
                                <img
                                    class="hero-carousel__slide {{ $loop->first ? 'is-active' : '' }}"
                                    src="{{ $imageUrl }}"
                                    alt="Foto da {{ $academyName }}"
                                    decoding="async"
                                    loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                >
                            @endforeach
                        </div>

                        @if (count($carouselImages) > 1)
                            <button type="button" class="hero-carousel__control hero-carousel__control--prev" data-hero-carousel-prev aria-label="Foto anterior">&lsaquo;</button>
                            <button type="button" class="hero-carousel__control hero-carousel__control--next" data-hero-carousel-next aria-label="Próxima foto">&rsaquo;</button>
                            <div class="hero-carousel__dots" aria-label="Fotos do carrossel">
                                @foreach ($carouselImages as $imageUrl)
                                    <button
                                        type="button"
                                        class="hero-carousel__dot {{ $loop->first ? 'is-active' : '' }}"
                                        data-hero-carousel-dot="{{ $loop->index }}"
                                        aria-label="Mostrar foto {{ $loop->iteration }}"
                                    ></button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <img class="page-section__logo" src="{{ $logoUrl }}"
                        alt="Logotipo {{ $academyName }}" decoding="async">
                @endif
                <h1 id="sobre-heading" class="page-section__title"><strong>{{ $heroTitle }}</strong></h1>
                <p class="page-section__lead">
                    {{ $heroSubtitle }}
                </p>
            </div>
        </section>

        <section id="horarios" class="page-section" aria-labelledby="horarios-heading">
            <div class="page-section__inner page-section__inner--wide page-section__inner--stack">
                <h2 id="horarios-heading" class="page-section__heading page-section__heading--center">Horários</h2>
                <p class="page-section__intro page-section__text">
                    Confira os dias e horários das aulas.
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
                            @foreach ($schedule as $row)
                                <tr>
                                    <th scope="row" class="schedule-table__col-day">{{ $row['day'] ?? '' }}</th>
                                    <td>{{ $row['kids_time'] ?? '-' }}</td>
                                    <td>{{ $row['adults_time'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="avaliacoes" class="page-section" aria-labelledby="avaliacoes-heading">
            <div class="page-section__inner page-section__inner--stack">
                <h2 id="avaliacoes-heading" class="page-section__heading">Avaliações</h2>
                @if ($reviews->isNotEmpty())
                    <div class="reviews-grid">
                        @foreach ($reviews as $review)
                            <article class="review-card">
                                <div class="review-card__header">
                                    @if ($review->author_photo_url)
                                        <img
                                            class="review-card__photo"
                                            src="{{ $review->author_photo_url }}"
                                            alt="Foto de {{ $review->short_author_name }}"
                                            width="48"
                                            height="48"
                                            loading="lazy"
                                            decoding="async">
                                    @endif
                                    <strong>{{ $review->short_author_name }}</strong>
                                </div>
                                <div class="review-card__rating" aria-label="{{ $review->rating }} de 5 estrelas">
                                    {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                                </div>
                                <p class="review-card__comment">{{ $review->comment }}</p>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="page-section__text">Depoimentos em breve.</p>
                @endif
            </div>
        </section>

        <section id="localizacao" class="page-section" aria-labelledby="localizacao-heading">
            <div class="page-section__inner page-section__inner--wide page-section__inner--stack">
                <h2 id="localizacao-heading" class="page-section__heading page-section__heading--center">Localização</h2>

                <address class="location-address">
                    <span class="location-address__name">{{ $academyName }}</span><br>
                    {!! nl2br(e($address)) !!}
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
