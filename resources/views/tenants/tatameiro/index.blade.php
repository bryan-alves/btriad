<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @php
        $site = $tenant?->site;
        $academyName = $site?->academy_name ?? 'Tatameiro';
        $pageTitle = $site?->page_title ?: 'Tatameiro | Gestão completa para academias de Jiu-Jitsu';
        $heroSubtitle = $site?->hero_subtitle ?: 'Gestão completa para academias de Jiu-Jitsu';
        $logoUrl = $site?->logo_url ?? asset('tatameiro-logo.png');
        $primaryColor = $site?->primary_color ?? '#e52521';
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
    <meta property="og:image" content="{{ url($logoUrl) }}">
    <meta property="og:image:alt" content="Logotipo {{ $academyName }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $heroSubtitle }}">
    <meta name="twitter:image" content="{{ url($logoUrl) }}">

    <link rel="icon" href="{{ $logoUrl }}" type="image/png">
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
            'image' => url($logoUrl),
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
        }

        .tm-header__brand span {
            color: var(--tm-red);
        }

        .tm-header__actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
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
            padding: 2rem 1.25rem 3rem;
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
            padding: 3rem 1.25rem;
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
            padding: 3rem 1.25rem 4rem;
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

        @media (max-width: 640px) {
            .tm-header__actions .tm-btn--ghost {
                display: none;
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
            <div class="tm-header__actions">
                <a href="{{ url('/login') }}" class="tm-btn tm-btn--ghost">Entrar</a>
                @if ($whatsappUrl)
                    <a href="{{ $whatsappUrl }}" class="tm-btn tm-btn--primary" target="_blank" rel="noopener noreferrer">
                        Falar conosco
                    </a>
                @else
                    <a href="#contato" class="tm-btn tm-btn--primary">Solicitar demonstração</a>
                @endif
            </div>
        </div>
    </header>

    <main id="conteudo-principal">
        <section class="tm-hero" aria-labelledby="hero-heading">
            <div class="tm-hero__inner">
                <img
                    class="tm-hero__logo"
                    src="{{ $logoUrl }}"
                    alt="{{ $academyName }} — {{ $heroSubtitle }}"
                    decoding="async"
                >
                <h1 id="hero-heading" class="visually-hidden">{{ $academyName }}</h1>
                <div class="tm-hero__actions">
                    @if ($whatsappUrl)
                        <a href="{{ $whatsappUrl }}" class="tm-btn tm-btn--primary" target="_blank" rel="noopener noreferrer">
                            Solicitar demonstração
                        </a>
                    @else
                        <a href="#contato" class="tm-btn tm-btn--primary">Solicitar demonstração</a>
                    @endif
                    <a href="{{ url('/login') }}" class="tm-btn tm-btn--ghost">Acessar painel</a>
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
                        <p class="tm-feature__text">Página pública com logo, horários, contato e identidade da equipe.</p>
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
                        <a href="mailto:contato@tatameiro.com.br" class="tm-btn tm-btn--primary">contato@tatameiro.com.br</a>
                    @endif
                    <a href="{{ url('/login') }}" class="tm-btn tm-btn--ghost">Entrar no painel</a>
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
