<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @php
        $site = $tenant?->site;
        $academyName = $site?->academy_name ?? 'Tatameiro';
        $pageTitle = $site?->page_title ?: 'Tatameiro | Gestão completa para academias de Jiu-Jitsu';
        $heroSubtitle = $site?->hero_subtitle ?: 'Gestão completa para academias de Jiu-Jitsu';
        $appLogoUrl = $site?->logo_url ?? asset('tatameiro-app-logo.png');
        $faviconUrl = $site?->nav_logo_url ?? asset('tatameiro-favicon.png');
        $heroLogoUrl = $site?->hero_logo_url ?? $site?->logo_url ?? asset('tatameiro-app-logo.png');
        $primaryColor = $site?->primary_color ?? '#e52521';
        $demoUrl = 'https://wa.link/3nl8q1';
        $whatsapp = trim((string) ($site?->whatsapp ?? ''));
        $whatsappUrl = $whatsapp !== '' ? (str_starts_with($whatsapp, 'http') ? $whatsapp : 'https://wa.me/'.preg_replace('/\D/', '', $whatsapp)) : null;
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $heroSubtitle }}. Site, alunos, presença, graduações, ranking e portal do aluno em uma única plataforma.">
    <link rel="canonical" href="{{ url('/') }}">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#0a0a0a">

    <meta property="og:locale" content="pt_BR">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $heroSubtitle }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ url($heroLogoUrl) }}">
    <meta property="og:image:alt" content="Logotipo {{ $academyName }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $heroSubtitle }}">
    <meta name="twitter:image" content="{{ url($heroLogoUrl) }}">

    <link rel="icon" href="{{ $faviconUrl }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'WebSite',
            'name' => $academyName,
            'url' => url('/'),
            'inLanguage' => 'pt-BR',
            'description' => $heroSubtitle,
        ],
        [
            '@type' => 'SoftwareApplication',
            'name' => $academyName,
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web',
            'description' => $heroSubtitle,
            'url' => url('/'),
            'image' => url($heroLogoUrl),
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>

    <style>
        :root {
            --tm-black: #0a0a0a;
            --tm-red: {{ $primaryColor }};
            --tm-white: #ffffff;
            --tm-muted: rgba(255, 255, 255, 0.72);
            --tm-border: rgba(229, 37, 33, 0.35);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background: var(--tm-black);
            color: var(--tm-white);
        }

        .skip-link {
            position: absolute;
            left: -9999px;
            top: 0;
            z-index: 1000;
            padding: 0.75rem 1rem;
            background: var(--tm-red);
            color: var(--tm-white);
            text-decoration: none;
        }

        .skip-link:focus {
            left: 1rem;
            top: 1rem;
        }

        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .tm-header {
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(12px);
            background: rgba(10, 10, 10, 0.88);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .tm-header__inner {
            max-width: 72rem;
            margin: 0 auto;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .tm-header__brand {
            font-size: 1.125rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-decoration: none;
            color: var(--tm-white);
            flex-shrink: 1;
            min-width: 0;
        }

        .tm-header__brand span {
            color: var(--tm-red);
        }

        .tm-header__actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-shrink: 0;
        }

        .tm-header .tm-btn {
            white-space: nowrap;
        }

        .tm-btn__label--mobile {
            display: none;
        }

        .tm-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.75rem;
            padding: 0.65rem 1.25rem;
            border-radius: 999px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.15s ease, opacity 0.15s ease, background 0.15s ease;
        }

        .tm-btn:hover {
            transform: translateY(-1px);
        }

        .tm-btn--ghost {
            color: var(--tm-white);
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: transparent;
        }

        .tm-btn--ghost:hover {
            background: rgba(255, 255, 255, 0.06);
        }

        .tm-btn--primary {
            color: var(--tm-white);
            background: var(--tm-red);
            border: 1px solid var(--tm-red);
        }

        .tm-btn--primary:hover {
            opacity: 0.92;
        }

        .tm-hero {
            padding: 2rem 1.25rem 0;
        }

        .tm-hero__inner {
            max-width: 42rem;
            margin: 0 auto;
            text-align: center;
        }

        .tm-hero__logo {
            width: min(100%, 28rem);
            height: auto;
            display: block;
            margin: 0 auto 2rem;
        }

        .tm-hero__actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.75rem;
            margin-top: 2rem;
        }

        .tm-section {
            padding: 3rem 1.25rem 0;
        }

        .tm-section__inner {
            max-width: 72rem;
            margin: 0 auto;
        }

        .tm-section__heading {
            margin: 0 0 0.75rem;
            font-size: clamp(1.5rem, 3vw, 2rem);
            font-weight: 700;
            text-align: center;
        }

        .tm-section__intro {
            margin: 0 auto 2.5rem;
            max-width: 42rem;
            text-align: center;
            color: var(--tm-muted);
            line-height: 1.6;
        }

        .tm-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr));
            gap: 1rem;
        }

        .tm-feature {
            padding: 1.5rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.03);
            text-align: center;
        }

        .tm-feature__icon {
            width: 2.5rem;
            height: 2.5rem;
            margin: 0 auto 1rem;
            color: var(--tm-white);
        }

        .tm-feature__title {
            margin: 0 0 0.5rem;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .tm-feature__text {
            margin: 0;
            font-size: 0.9rem;
            line-height: 1.5;
            color: var(--tm-muted);
        }

        .tm-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr));
            gap: 1rem;
        }

        .tm-step {
            padding: 1.5rem;
            border-left: 3px solid var(--tm-red);
            background: rgba(255, 255, 255, 0.03);
            border-radius: 0 1rem 1rem 0;
        }

        .tm-step__number {
            display: inline-block;
            margin-bottom: 0.75rem;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: var(--tm-red);
        }

        .tm-step__title {
            margin: 0 0 0.5rem;
            font-size: 1.05rem;
            font-weight: 600;
        }

        .tm-step__text {
            margin: 0;
            color: var(--tm-muted);
            line-height: 1.55;
            font-size: 0.95rem;
        }

        .tm-cta {
            padding: 3rem 1.25rem 0;
        }

        .tm-cta__box {
            max-width: 48rem;
            margin: 0 auto;
            padding: 2.5rem 1.5rem;
            text-align: center;
            border: 1px solid var(--tm-border);
            border-radius: 1.25rem;
            background: linear-gradient(180deg, rgba(229, 37, 33, 0.12), rgba(255, 255, 255, 0.02));
        }

        .tm-cta__title {
            margin: 0 0 0.75rem;
            font-size: clamp(1.35rem, 3vw, 1.85rem);
            font-weight: 700;
        }

        .tm-cta__text {
            margin: 0 auto 1.5rem;
            max-width: 34rem;
            color: var(--tm-muted);
            line-height: 1.6;
        }

        .tm-footer {
            padding: 2rem 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            text-align: center;
            color: var(--tm-muted);
            font-size: 0.9rem;
        }

        .tm-footer strong {
            color: var(--tm-white);
        }

        .tm-header__nav {
            display: none;
            align-items: center;
            gap: 1.5rem;
        }

        .tm-header__nav a {
            color: var(--tm-muted);
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .tm-header__nav a:hover {
            color: var(--tm-white);
        }

        .tm-pricing {
            padding-top: 1rem;
        }

        .tm-clients {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr));
            gap: 1rem;
        }

        .tm-client {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            min-height: 7rem;
            padding: 1.25rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.03);
            text-decoration: none;
            color: inherit;
            transition: border-color 0.15s ease, transform 0.15s ease;
        }

        .tm-client:hover {
            border-color: var(--tm-border);
            transform: translateY(-2px);
        }

        .tm-client__logo {
            width: min(100%, 9rem);
            height: 3rem;
            object-fit: contain;
        }

        .tm-client__name {
            margin: 0;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--tm-muted);
            text-align: center;
        }

        .tm-pricing__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            align-items: stretch;
            max-width: 52rem;
            margin: 0 auto;
        }

        .tm-plan {
            position: relative;
            display: flex;
            flex-direction: column;
            padding: 1.75rem 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.25rem;
            background: rgba(255, 255, 255, 0.03);
        }

        .tm-plan--featured {
            border-color: var(--tm-border);
            background: linear-gradient(180deg, rgba(229, 37, 33, 0.14), rgba(255, 255, 255, 0.04));
            box-shadow: 0 0 0 1px rgba(229, 37, 33, 0.15);
        }

        .tm-plan__badge {
            position: absolute;
            top: -0.65rem;
            left: 50%;
            transform: translateX(-50%);
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            background: var(--tm-red);
            color: var(--tm-white);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .tm-plan__name {
            margin: 0 0 0.35rem;
            font-size: 1.15rem;
            font-weight: 700;
        }

        .tm-plan__url {
            margin: 0 0 1.25rem;
            font-size: 0.82rem;
            color: var(--tm-muted);
            line-height: 1.45;
        }

        .tm-plan__url code {
            display: inline-block;
            margin-top: 0.25rem;
            padding: 0.2rem 0.45rem;
            border-radius: 0.35rem;
            background: rgba(255, 255, 255, 0.06);
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.78rem;
            word-break: break-all;
        }

        .tm-plan__price {
            margin: 0 0 0.25rem;
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }

        .tm-plan__price small {
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--tm-muted);
        }

        .tm-plan__annual {
            margin: 0 0 1.25rem;
            font-size: 0.82rem;
            color: var(--tm-muted);
        }

        .tm-plan__features {
            flex: 1;
            margin: 0 0 1.5rem;
            padding: 0;
            list-style: none;
        }

        .tm-plan__features li {
            position: relative;
            padding: 0.45rem 0 0.45rem 1.35rem;
            font-size: 0.92rem;
            line-height: 1.45;
            color: rgba(255, 255, 255, 0.88);
        }

        .tm-plan__features li::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0.85rem;
            width: 0.45rem;
            height: 0.45rem;
            border-radius: 999px;
            background: var(--tm-red);
        }

        .tm-plan__features li.is-muted {
            color: var(--tm-muted);
        }

        .tm-plan__features li.is-muted::before {
            background: rgba(255, 255, 255, 0.22);
        }

        .tm-plan .tm-btn {
            width: 100%;
        }

        .tm-pricing__note {
            margin: 1.5rem auto 0;
            max-width: 42rem;
            text-align: center;
            font-size: 0.88rem;
            color: var(--tm-muted);
            line-height: 1.55;
        }

        @media (min-width: 900px) {
            .tm-header__nav {
                display: flex;
            }
        }

        @media (max-width: 900px) {
            .tm-pricing__grid {
                grid-template-columns: 1fr;
                max-width: 24rem;
                margin: 0 auto;
            }

            .tm-plan--featured {
                order: -1;
            }
        }

        @media (max-width: 480px) {
            .tm-header__inner {
                padding: 0.75rem 1rem;
                gap: 0.5rem;
            }

            .tm-header__brand {
                font-size: 0.95rem;
                letter-spacing: 0.06em;
            }

            .tm-header .tm-btn {
                font-size: 0.8125rem;
                padding: 0.5rem 0.85rem;
                min-height: 2.5rem;
            }

            .tm-header .tm-btn .tm-btn__label--desktop {
                display: none;
            }

            .tm-header .tm-btn .tm-btn__label--mobile {
                display: inline;
            }
        }
    </style>
</head>

<body class="tm-page">
    <a class="skip-link" href="#conteudo-principal">Ir para o conteúdo</a>

    <header class="tm-header">
        <div class="tm-header__inner">
            <a href="{{ url('/') }}" class="tm-header__brand" title="{{ $academyName }} — página inicial">
                TATA<span>MEIRO</span>
            </a>
            <nav class="tm-header__nav" aria-label="Seções do site">
                <a href="#recursos">Recursos</a>
                <a href="#clientes">Clientes</a>
                <a href="#planos">Planos</a>
                <a href="#contato">Contato</a>
            </nav>
            <div class="tm-header__actions">
                @if ($whatsappUrl)
                    <a href="{{ $whatsappUrl }}" class="tm-btn tm-btn--primary" target="_blank" rel="noopener noreferrer">
                        Falar conosco
                    </a>
                @else
                    <a href="{{ $demoUrl }}" class="tm-btn tm-btn--primary" target="_blank" rel="noopener noreferrer">
                        <span class="tm-btn__label--desktop">Solicitar demonstração</span>
                        <span class="tm-btn__label--mobile">Demonstração</span>
                    </a>
                @endif
            </div>
        </div>
    </header>

    <main id="conteudo-principal">
        <section class="tm-hero" aria-labelledby="hero-heading">
            <div class="tm-hero__inner">
                <img
                    class="tm-hero__logo"
                    src="{{ $heroLogoUrl }}"
                    alt="{{ $academyName }} — {{ $heroSubtitle }}"
                    decoding="async"
                >
                <h1 id="hero-heading" class="visually-hidden">{{ $academyName }}</h1>
                <div class="tm-hero__actions">
                    <a href="{{ $demoUrl }}" class="tm-btn tm-btn--primary" target="_blank" rel="noopener noreferrer">
                        Solicitar demonstração
                    </a>
                </div>
            </div>
        </section>

        <section id="recursos" class="tm-section" aria-labelledby="recursos-heading">
            <div class="tm-section__inner">
                <h2 id="recursos-heading" class="tm-section__heading">Tudo que sua academia precisa</h2>
                <p class="tm-section__intro">
                    Site institucional, gestão administrativa e portal do aluno integrados em uma plataforma
                    pensada para o dia a dia do Jiu-Jitsu.
                </p>

                <div class="tm-features">
                    <article class="tm-feature">
                        <svg class="tm-feature__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <h3 class="tm-feature__title">Alunos</h3>
                        <p class="tm-feature__text">Cadastro completo, faixa, foto, turma e histórico em um só lugar.</p>
                    </article>

                    <article class="tm-feature">
                        <svg class="tm-feature__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <path d="M16 2v4M8 2v4M3 10h18"/>
                            <path d="m9 16 2 2 4-4"/>
                        </svg>
                        <h3 class="tm-feature__title">Presença</h3>
                        <p class="tm-feature__text">Listas de chamada por turma e data, sem planilhas ou caderno.</p>
                    </article>

                    <article class="tm-feature">
                        <svg class="tm-feature__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path d="M12 2 4 6v6c0 5 3.5 9.5 8 10 4.5-.5 8-5 8-10V6l-8-4Z"/>
                            <path d="M9 12h6"/>
                        </svg>
                        <h3 class="tm-feature__title">Graduações</h3>
                        <p class="tm-feature__text">Registre faixas e graus com histórico confiável para cada aluno.</p>
                    </article>

                    <article class="tm-feature">
                        <svg class="tm-feature__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path d="M4 19V5"/>
                            <path d="M4 19h16"/>
                            <rect x="7" y="10" width="3" height="6" rx="0.5"/>
                            <rect x="12" y="7" width="3" height="9" rx="0.5"/>
                            <rect x="17" y="4" width="3" height="12" rx="0.5"/>
                        </svg>
                        <h3 class="tm-feature__title">Ranking</h3>
                        <p class="tm-feature__text">Engaje a equipe com ranking mensal de treinos e evolução.</p>
                    </article>

                    <article class="tm-feature">
                        <svg class="tm-feature__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="14" rx="2"/>
                            <path d="M3 8h18"/>
                            <circle cx="12" cy="13" r="3"/>
                            <path d="M12 10v1"/>
                            <path d="M12 15v1"/>
                            <path d="M10 13h1"/>
                            <path d="M13 13h1"/>
                        </svg>
                        <h3 class="tm-feature__title">Site da academia</h3>
                        <p class="tm-feature__text">Página pública no domínio da equipe, com logo, horários, contato e identidade visual.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="como-funciona" class="tm-section" aria-labelledby="como-funciona-heading">
            <div class="tm-section__inner">
                <h2 id="como-funciona-heading" class="tm-section__heading">Como funciona</h2>
                <p class="tm-section__intro">
                    Cada academia opera no seu próprio domínio, com dados isolados e visual personalizado.
                </p>

                <div class="tm-steps">
                    <article class="tm-step">
                        <span class="tm-step__number">01</span>
                        <h3 class="tm-step__title">Configure sua marca</h3>
                        <p class="tm-step__text">Logo, cores, horários, WhatsApp e redes sociais no painel administrativo.</p>
                    </article>
                    <article class="tm-step">
                        <span class="tm-step__number">02</span>
                        <h3 class="tm-step__title">Organize a operação</h3>
                        <p class="tm-step__text">Cadastre alunos, turmas, presenças e graduações sem depender de planilhas.</p>
                    </article>
                    <article class="tm-step">
                        <span class="tm-step__number">03</span>
                        <h3 class="tm-step__title">Engaje os alunos</h3>
                        <p class="tm-step__text">Portal do aluno com histórico de treinos, faixa e ranking mensal.</p>
                    </article>
                </div>
            </div>
        </section>

        @if (($platformClients ?? collect())->isNotEmpty())
            <section id="clientes" class="tm-section" aria-labelledby="clientes-heading">
                <div class="tm-section__inner">
                    <h2 id="clientes-heading" class="tm-section__heading">Academias no Tatameiro</h2>
                    <p class="tm-section__intro">
                        Equipes que já utilizam a plataforma para organizar a operação e a presença digital.
                    </p>

                    <div class="tm-clients">
                        @foreach ($platformClients as $client)
                            @if ($client->display_website_url)
                                <a
                                    href="{{ $client->display_website_url }}"
                                    class="tm-client"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    @if ($client->logo_url)
                                        <img
                                            class="tm-client__logo"
                                            src="{{ $client->logo_url }}"
                                            alt="Logo {{ $client->name }}"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    @endif
                                    <p class="tm-client__name">{{ $client->name }}</p>
                                </a>
                            @else
                                <div class="tm-client">
                                    @if ($client->logo_url)
                                        <img
                                            class="tm-client__logo"
                                            src="{{ $client->logo_url }}"
                                            alt="Logo {{ $client->name }}"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    @endif
                                    <p class="tm-client__name">{{ $client->name }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section id="planos" class="tm-section tm-pricing" aria-labelledby="planos-heading">
            <div class="tm-section__inner">
                <h2 id="planos-heading" class="tm-section__heading">Planos</h2>
                <p class="tm-section__intro">
                    Dois planos, decisão simples: quer só o app de gestão ou transformar sua academia em uma academia digital?
                </p>

                <div class="tm-pricing__grid">
                    <article class="tm-plan">
                        <h3 class="tm-plan__name">Tatameiro App</h3>
                        <p class="tm-plan__url">
                            Gestão completa no painel<br>
                            <code>suaacademia.tatameiro.com.br</code>
                        </p>
                        <p class="tm-plan__price">R$ 99<small>/mês</small></p>
                        <ul class="tm-plan__features">
                            <li>Alunos, presença, graduações e ranking</li>
                            <li>Portal do aluno</li>
                            <li>Login personalizado com logo da academia</li>
                            <li>Cores do painel e do login</li>
                            <li class="is-muted">Sem site público</li>
                            <li class="is-muted">Sem domínio exclusivo</li>
                        </ul>
                        <a href="{{ $demoUrl }}" class="tm-btn tm-btn--ghost" target="_blank" rel="noopener noreferrer">Começar</a>
                    </article>

                    <article class="tm-plan tm-plan--featured">
                        <span class="tm-plan__badge">Mais popular</span>
                        <h3 class="tm-plan__name">Academia Digital</h3>
                        <p class="tm-plan__url">
                            App + site no seu domínio<br>
                            <code>suaacademia.com.br</code>
                        </p>
                        <p class="tm-plan__price">R$ 199<small>/mês</small></p>
                        <ul class="tm-plan__features">
                            <li>Tudo do Tatameiro App</li>
                            <li>Domínio exclusivo: suaacademia.com.br</li>
                            <li>Site da academia com identidade visual</li>
                            <li>Horários, localização e CTA de aula experimental</li>
                            <li>Galeria, avaliações e SEO básico</li>
                            <li>Integração com redes sociais</li>
                        </ul>
                        <a href="{{ $demoUrl }}" class="tm-btn tm-btn--primary" target="_blank" rel="noopener noreferrer">Solicitar demonstração</a>
                    </article>
                </div>

                <p class="tm-pricing__note">
                    Sem limite de alunos. No plano Academia Digital, auxiliamos na configuração do DNS do domínio próprio.
                </p>
            </div>
        </section>

        <section id="contato" class="tm-cta" aria-labelledby="contato-heading">
            <div class="tm-cta__box">
                <h2 id="contato-heading" class="tm-cta__title">Pronto para profissionalizar sua academia?</h2>
                <p class="tm-cta__text">
                    Veja na prática como o Tatameiro integra site, gestão e portal do aluno em uma única plataforma.
                </p>
                <div class="tm-hero__actions">
                    @if ($whatsappUrl)
                        <a href="{{ $whatsappUrl }}" class="tm-btn tm-btn--primary" target="_blank" rel="noopener noreferrer">
                            Falar no WhatsApp
                        </a>
                    @else
                        <a href="{{ $demoUrl }}" class="tm-btn tm-btn--primary" target="_blank" rel="noopener noreferrer">Fale Conosco</a>
                    @endif
                </div>
            </div>
        </section>
    </main>

    <footer class="tm-footer">
        <p>
            <strong>© {{ date('Y') }} • {{ $academyName }}</strong><br>
            <span>Gestão completa para academias de Jiu-Jitsu</span>
        </p>
    </footer>
</body>

</html>
