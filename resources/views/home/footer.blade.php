<footer class="footer">
  <div class="footer__logo">
    <img src="{{ asset('logo.png') }}" alt="Equipe B-Triad Jiu-Jitsu — logotipo" decoding="async" class="footer__logo-img">
  </div>
  <p>
    <strong>© {{ date('Y') }} • B-Triad Jiu-Jitsu</strong><br>
    <span>Todos os direitos reservados</span>
  </p>
  <div class="footer__social-links">
    <a href="#" rel="noopener noreferrer" title="Canal no YouTube em breve">
      <div>
        <img src="/img/social-media/youtube.png" alt="YouTube (em breve)" width="32" height="32">
      </div>
    </a>
    <a href="https://www.instagram.com/equipe.btriad.jiujitsu" target="_blank" rel="noopener noreferrer"
      title="Instagram da Equipe B-Triad">
      <div>
        <img src="/img/social-media/instagram.png" alt="Instagram @equipe.btriad.jiujitsu" width="32" height="32">
      </div>
    </a>
    <a href="https://wa.me/5513981245120" target="_blank" rel="noopener noreferrer"
      title="WhatsApp — fale conosco">
      <div>
        <img src="/img/social-media/whatsapp.png" alt="WhatsApp" width="32" height="32">
      </div>
    </a>
  </div>
</footer>
@include('home.credits')
