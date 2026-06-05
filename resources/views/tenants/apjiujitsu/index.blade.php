<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>AP Jiu-Jitsu | Aulas de jiu-jitsu para crianças e adultos</title>
    <meta name="description"
        content="AP Jiu-Jitsu: aulas de jiu-jitsu para crianças e adultos. Horários segundas, quartas e sextas. Aula experimental pelo WhatsApp.">
    <link rel="canonical" href="{{ url('/') }}">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#c97716">

    <meta property="og:locale" content="pt_BR">
    <meta property="og:title" content="AP Jiu-Jitsu | Equipe e horários">
    <meta property="og:description"
        content="Aulas de jiu-jitsu para crianças e adultos. Segundas, quartas e sextas.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ url(asset('apjiujitsu-logo.png')) }}">
    <meta property="og:image:alt" content="Logotipo AP Jiu-Jitsu">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="AP Jiu-Jitsu | Equipe e horários">
    <meta name="twitter:description"
        content="Aulas de jiu-jitsu para crianças e adultos. Segundas, quartas e sextas.">
    <meta name="twitter:image" content="{{ url(asset('apjiujitsu-logo.png')) }}">

    <link rel="icon" href="{{ asset('apjiujitsu-logo.png') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'WebSite',
            'name' => 'AP Jiu-Jitsu',
            'url' => url('/'),
            'inLanguage' => 'pt-BR',
            'description' => 'Site da AP Jiu-Jitsu — aulas, horários e contato.',
        ],
        [
            '@type' => 'Organization',
            '@id' => url('/').'#organization',
            'name' => 'AP Jiu-Jitsu',
            'url' => url('/'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => url(asset('apjiujitsu-logo.png')),
            ],
            'image' => url(asset('apjiujitsu-logo.png')),
            'sameAs' => [
                'https://apjiujitsu.com.br',
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>

    @vite(['resources/css/index.css', 'resources/js/index.js'])
    <style>
        :root {
            --ap-primary: #c97716;
            --ap-primary-dark: #a85f10;
        }

        .ap-page .skip-link:focus,
        .ap-page .nav__cta--whatsapp,
        .ap-page .schedule-table th,
        .ap-page .location-cta {
            background: var(--ap-primary);
        }

        .ap-page .nav__cta--whatsapp:hover,
        .ap-page .location-cta:hover {
            background: var(--ap-primary-dark);
        }

        .ap-page .nav__links--desktop a.is-active,
        .ap-page .nav-sections__list a.is-active {
            box-shadow: inset 0 -2px 0 var(--ap-primary);
        }

        .ap-page .footer__social-links div:hover {
            background: var(--ap-primary);
        }
    </style>
    @stack('styles')
</head>

<body class="ap-page">
    <a class="skip-link" href="#conteudo-principal">Ir para o conteúdo</a>

    <header class="site-header" id="site-header">
        <nav class="nav nav--main" aria-label="Navegação principal">
            <div class="nav__bar">
                <a href="{{ url('/') }}" class="nav__logo" title="AP Jiu-Jitsu — página inicial">
                    <img src="{{ asset('apjiujitsu-logo.png') }}" alt="AP Jiu-Jitsu" decoding="async">
                </a>

                <ul class="nav__links nav__links--desktop" aria-label="Seções do site">
                    <li><a href="#sobre" data-section-link>Sobre</a></li>
                    <li><a href="#horarios" data-section-link>Horários</a></li>
                    <li><a href="#avaliacoes" data-section-link>Avaliações</a></li>
                    <li><a href="#localizacao" data-section-link>Localização</a></li>
                </ul>

                <a href="#" target="_blank" rel="noopener noreferrer"
                    class="nav__cta nav__cta--whatsapp nav__cta--mobile-center" title="WhatsApp">Aula experimental</a>

                <div class="nav__bar-end">
                    <div class="nav__actions nav__actions--desktop">
                        <a href="#" target="_blank" rel="noopener noreferrer"
                            class="nav__cta nav__cta--whatsapp" title="WhatsApp">Aula experimental</a>
                        <a href="{{ url('/login') }}" class="nav__cta nav__cta--portal">Portal do aluno</a>
                    </div>
                    <button type="button" class="nav__toggle" id="nav-toggle" aria-expanded="false"
                        aria-controls="nav-drawer" aria-label="Abrir menu">
                        <span class="nav__toggle-bars" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </nav>

        <nav class="nav-sections" id="section-nav" aria-label="Atalhos para seções">
            <div class="nav-sections__inner">
                <ul class="nav-sections__list">
                    <li><a href="#sobre" data-section-link>Sobre</a></li>
                    <li><a href="#horarios" data-section-link>Horários</a></li>
                    <li><a href="#avaliacoes" data-section-link>Avaliações</a></li>
                    <li><a href="#localizacao" data-section-link>Localização</a></li>
                </ul>
            </div>
        </nav>

        <div class="nav__backdrop" id="nav-backdrop" aria-hidden="true"></div>

        <aside class="nav__drawer" id="nav-drawer" aria-hidden="true" aria-label="Menu lateral">
            <div class="nav__drawer-head">
                <span class="nav__drawer-brand">AP Jiu-Jitsu</span>
                <button type="button" class="nav__drawer-close" id="nav-drawer-close" aria-label="Fechar menu">&times;</button>
            </div>

            <nav class="nav__drawer-nav" aria-label="Seções do site">
                <ul class="nav__drawer-list">
                    <li><a href="#sobre" data-section-link>Sobre</a></li>
                    <li><a href="#horarios" data-section-link>Horários</a></li>
                    <li><a href="#avaliacoes" data-section-link>Avaliações</a></li>
                    <li><a href="#localizacao" data-section-link>Localização</a></li>
                </ul>
            </nav>

            <div class="nav__drawer-footer">
                <a href="{{ url('/login') }}" class="nav__cta nav__cta--portal nav__cta--block">Portal do aluno</a>
            </div>
        </aside>
    </header>

    <main id="conteudo-principal" class="page-sections">
        <section id="sobre" class="page-section page-section--hero" aria-labelledby="sobre-heading">
            <div class="page-section__inner page-section__inner--hero">
                <img class="page-section__logo" src="{{ asset('apjiujitsu-logo.png') }}"
                    alt="Logotipo AP Jiu-Jitsu" decoding="async">
                <h1 id="sobre-heading" class="page-section__title"><strong>Estamos no aquecimento!</strong></h1>
                <p class="page-section__lead">
                    Em breve, o site oficial da <strong>AP Jiu-Jitsu.</strong>
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
                    <span class="location-address__name">AP Jiu-Jitsu</span><br>
                    Endereço em breve
                </address>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer__logo">
            <img src="{{ asset('apjiujitsu-logo.png') }}" alt="AP Jiu-Jitsu — logotipo" decoding="async" class="footer__logo-img">
        </div>
        <p>
            <strong>© {{ date('Y') }} • AP Jiu-Jitsu</strong><br>
            <span>Todos os direitos reservados</span>
        </p>
        <div class="footer__social-links">
            <a href="#" rel="noopener noreferrer" title="Canal no YouTube em breve">
                <div>
                    <img src="/img/social-media/youtube.png" alt="YouTube (em breve)" width="32" height="32">
                </div>
            </a>
            <a href="#" rel="noopener noreferrer" title="Instagram da AP Jiu-Jitsu">
                <div>
                    <img src="/img/social-media/instagram.png" alt="Instagram da AP Jiu-Jitsu" width="32" height="32">
                </div>
            </a>
            <a href="#" rel="noopener noreferrer" title="WhatsApp — fale conosco">
                <div>
                    <img src="/img/social-media/whatsapp.png" alt="WhatsApp" width="32" height="32">
                </div>
            </a>
        </div>
    </footer>
</body>

</html>
