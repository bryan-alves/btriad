<header class="site-header" id="site-header">
  <nav class="nav nav--main" aria-label="Navegação principal">
    <div class="nav__bar">
      <a href="{{ url('/') }}" class="nav__logo" title="B-Triad Jiu-Jitsu — página inicial">
        <img src="{{ asset('img/logo/triangulo.png') }}" alt="B-Triad Jiu-Jitsu" decoding="async">
      </a>

      <ul class="nav__links nav__links--desktop" aria-label="Seções do site">
        <li><a href="#sobre" data-section-link>Sobre</a></li>
        <li><a href="#horarios" data-section-link>Horários</a></li>
        <li><a href="#avaliacoes" data-section-link>Avaliações</a></li>
        <li><a href="#localizacao" data-section-link>Localização</a></li>
      </ul>

      <a href="https://wa.link/ue67hy" target="_blank" rel="noopener noreferrer"
        class="nav__cta nav__cta--whatsapp nav__cta--mobile-center" title="WhatsApp">Aula experimental</a>

      <div class="nav__bar-end">
        <div class="nav__actions nav__actions--desktop">
          <a href="https://wa.link/ue67hy" target="_blank" rel="noopener noreferrer"
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
      <span class="nav__drawer-brand">B-Triad</span>
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
