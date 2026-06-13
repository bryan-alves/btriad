<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @php
        $site = $tenant?->site;
        $academyName = $site?->academy_name ?? $tenant?->name ?? 'Academia';
        $pageTitle = $site?->page_title ?: "{$academyName} | Aulas de jiu-jitsu";
        $heroTitle = $site?->hero_title ?: $academyName;
        $heroSubtitle = $site?->hero_subtitle ?: 'Aulas de jiu-jitsu para crianças e adultos.';
        $navLogoUrl = $site?->nav_logo_url ?? $site?->logo_url ?? asset('img/logo/triangulo.png');
        $heroLogoUrl = $site?->hero_logo_url ?? $site?->logo_url ?? asset('img/logo/triangulo.png');
        $carouselImages = $site?->carousel_image_urls ?? [];
        $primaryColor = $site?->primary_color ?? '#c41e3a';
        $backgroundColor = $site?->background_color ?? '#3d3d3d';
        $schedule = is_array($schedule ?? null) ? $schedule : ['weekdays' => [], 'rows' => []];
        $scheduleRows = $schedule['rows'] ?? [];
        $reviews = $tenant?->reviews ?? collect();
        $address = trim((string) ($site?->address ?? ''));
        $metaDescription = trim($heroSubtitle.' '.($address ? "Endereço: {$address}" : ''));
        $mapQuery = $address ?: $academyName;
        $sameAs = array_values(array_filter([$site?->youtube, $site?->instagram, $site?->whatsapp]));
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ url('/') }}">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="{{ $site?->header_color ?? '#1b1b18' }}">

    <meta property="og:locale" content="pt_BR">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ url($navLogoUrl) }}">
    <meta property="og:image:alt" content="Logotipo {{ $academyName }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ url($navLogoUrl) }}">

    <link rel="icon" href="{{ $navLogoUrl }}" type="image/x-icon">
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
            'description' => $metaDescription,
        ],
        [
            '@type' => 'Organization',
            '@id' => url('/').'#organization',
            'name' => $academyName,
            'url' => url('/'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => url($navLogoUrl),
            ],
            'image' => url($navLogoUrl),
            'address' => $address ?: null,
            'sameAs' => $sameAs,
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>

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
                    <img class="page-section__logo" src="{{ $heroLogoUrl }}"
                        alt="Logotipo {{ $academyName }}" decoding="async">
                @endif

                <div class="page-section__copy">
                    <h1 id="sobre-heading" class="page-section__title"><strong>{{ $heroTitle }}</strong></h1>
                    <p class="page-section__lead">{{ $heroSubtitle }}</p>
                </div>
            </div>
        </section>

        <section id="horarios" class="page-section" aria-labelledby="horarios-heading">
            <div class="page-section__inner page-section__inner--wide page-section__inner--stack">
                <h2 id="horarios-heading" class="page-section__heading page-section__heading--center">Horários</h2>
                @if (! empty($scheduleRows))
                    <p class="page-section__intro page-section__text">Confira os dias e horários das aulas.</p>

                    <div class="schedule-table-wrap">
                        <table class="schedule-table schedule-table--matrix">
                            <caption class="visually-hidden">Grade de horários por turma e dia da semana</caption>
                            <thead>
                                <tr>
                                    <th scope="col" class="schedule-table__col-class">Turma</th>
                                    @foreach ($schedule['weekdays'] ?? [] as $weekday)
                                        <th scope="col">{{ $weekday['label'] ?? '' }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($scheduleRows as $row)
                                    <tr>
                                        <th scope="row" class="schedule-table__col-class">{{ $row['class_name'] ?? '' }}</th>
                                        @foreach ($row['times'] ?? [] as $time)
                                            <td>
                                                @if (is_array($time) && count($time) > 0)
                                                    <div class="schedule-table__times">
                                                        @foreach ($time as $slot)
                                                            <span class="schedule-table__time">{{ $slot }}</span>
                                                        @endforeach
                                                    </div>
                                                @elseif (is_string($time) && $time !== '' && $time !== '-')
                                                    <span class="schedule-table__time">{{ $time }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="page-section__text">Horários em breve.</p>
                @endif
            </div>
        </section>

        @include('home.reviews-section', ['reviews' => $reviews])

        <section id="localizacao" class="page-section" aria-labelledby="localizacao-heading">
            <div class="page-section__inner page-section__inner--wide page-section__inner--stack">
                <h2 id="localizacao-heading" class="page-section__heading page-section__heading--center">Localização</h2>

                <address class="location-address">
                    <span class="location-address__name">{{ $academyName }}</span>
                    @if ($address)
                        <br>{!! nl2br(e($address)) !!}
                    @else
                        <br>Endereço em breve
                    @endif
                </address>

                @if ($address)
                    <div class="location-map">
                        <iframe
                            src="https://www.google.com/maps?q={{ rawurlencode($mapQuery) }}&amp;output=embed"
                            title="Mapa do Google: localização da {{ $academyName }}"
                            width="600"
                            height="350"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen></iframe>
                    </div>

                    <a class="location-cta" href="https://www.google.com/maps/dir/?api=1&amp;destination={{ rawurlencode($mapQuery) }}"
                        target="_blank" rel="noopener noreferrer">
                        Como chegar
                    </a>
                @endif
            </div>
        </section>
    </main>

    @include('home.footer')
</body>

</html>
