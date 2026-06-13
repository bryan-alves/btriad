@php
  $site = $tenant?->site;
  $academyName = $site?->academy_name ?? $tenant?->name ?? 'Academia';
  $footerLogoUrl = $site?->footer_logo_url ?? $site?->logo_url ?? asset('img/logo/triangulo.png');
  $socialLinks = collect([
    [
      'url' => $site?->youtube,
      'icon' => 'youtube.png',
      'title' => "Canal no YouTube da {$academyName}",
      'alt' => "YouTube da {$academyName}",
    ],
    [
      'url' => $site?->instagram,
      'icon' => 'instagram.png',
      'title' => "Instagram da {$academyName}",
      'alt' => "Instagram da {$academyName}",
    ],
    [
      'url' => $site?->whatsapp,
      'icon' => 'whatsapp.png',
      'title' => 'WhatsApp — fale conosco',
      'alt' => 'WhatsApp',
    ],
  ])->map(function (array $link) {
    $active = filled($link['url']);

    return [
      ...$link,
      'href' => $active ? $link['url'] : '#',
      'class' => 'footer__social-link'.($active ? '' : ' footer__social-link--inactive'),
      'attrs' => $active
        ? 'target="_blank" rel="noopener noreferrer"'
        : 'aria-hidden="true" tabindex="-1"',
    ];
  });
@endphp

<footer class="footer">
  <div class="footer__logo">
    <img src="{{ $footerLogoUrl }}" alt="{{ $academyName }} — logotipo" decoding="async" class="footer__logo-img">
  </div>
  <p>
    <strong>© {{ date('Y') }} • {{ $academyName }}</strong><br>
    <span>Todos os direitos reservados</span>
  </p>
  <div class="footer__social-links">
    @foreach ($socialLinks as $link)
      <a
        href="{{ $link['href'] }}"
        class="{{ $link['class'] }}"
        title="{{ $link['title'] }}"
        {!! $link['attrs'] !!}
      >
        <div>
          <img src="/img/social-media/{{ $link['icon'] }}" alt="{{ $link['alt'] }}" width="32" height="32">
        </div>
      </a>
    @endforeach
  </div>
</footer>
@include('home.credits')
