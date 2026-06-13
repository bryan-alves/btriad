/**
 * Site público: drawer do menu, variáveis CSS do header empilhado e scroll-spy das seções.
 */
const SECTION_IDS = ['sobre', 'horarios', 'avaliacoes', 'localizacao']

function isMobileNav() {
  return window.matchMedia('(max-width: 899px)').matches
}

/**
 * Navegação por arrastar/deslizar (touch e mouse).
 * Deslize para a esquerda = próximo; para a direita = anterior.
 */
function bindSwipe(root, { onSwipeLeft, onSwipeRight, canSwipe = () => true }) {
  if (!root) return

  let startX = 0
  let startY = 0
  let tracking = false
  const threshold = 48

  root.addEventListener('dragstart', (event) => {
    if (event.target instanceof HTMLImageElement) {
      event.preventDefault()
    }
  })

  root.addEventListener('pointerdown', (event) => {
    if (!canSwipe()) return
    if (event.pointerType === 'mouse' && event.button !== 0) return
    if (event.target instanceof Element && event.target.closest('button, a')) return

    tracking = true
    startX = event.clientX
    startY = event.clientY
    root.classList.add('is-swipe-tracking')

    try {
      root.setPointerCapture(event.pointerId)
    } catch (_) {
      /* ignore */
    }
  })

  function finishSwipe(event) {
    if (!tracking) return

    tracking = false
    root.classList.remove('is-swipe-tracking')

    try {
      root.releasePointerCapture(event.pointerId)
    } catch (_) {
      /* ignore */
    }

    const dx = event.clientX - startX
    const dy = event.clientY - startY

    if (Math.abs(dx) < threshold || Math.abs(dx) <= Math.abs(dy) * 1.15) {
      return
    }

    if (dx < 0) {
      onSwipeLeft()
    } else {
      onSwipeRight()
    }
  }

  root.addEventListener('pointerup', finishSwipe)
  root.addEventListener('pointercancel', (event) => {
    tracking = false
    root.classList.remove('is-swipe-tracking')

    try {
      root.releasePointerCapture(event.pointerId)
    } catch (_) {
      /* ignore */
    }
  })
}

function updateHeaderCssVars() {
  const siteHeader = document.getElementById('site-header')
  const main = document.querySelector('.nav--main')
  const sectionNav = document.getElementById('section-nav')
  const root = document.documentElement
  root.style.setProperty('--main-nav-h', `${main?.offsetHeight ?? 64}px`)
  root.style.setProperty('--section-nav-h', `${sectionNav?.offsetHeight ?? 42}px`)
  root.style.setProperty('--site-header-h', `${siteHeader?.offsetHeight ?? 106}px`)
}

function setActiveSection(id) {
  document.querySelectorAll('a[data-section-link]').forEach((a) => {
    const href = a.getAttribute('href')
    const match = href === `#${id}`
    a.classList.toggle('is-active', match)
    if (match) a.setAttribute('aria-current', 'true')
    else a.removeAttribute('aria-current')
  })
}

function getCurrentSectionId() {
  const line = (document.getElementById('site-header')?.offsetHeight ?? 106) + 12

  let current = SECTION_IDS[0]
  for (const id of SECTION_IDS) {
    const el = document.getElementById(id)
    if (!el) continue
    const top = el.getBoundingClientRect().top
    if (top <= line) current = id
  }
  return current
}

function initSectionScrollSpy() {
  let ticking = false
  function tick() {
    ticking = false
    setActiveSection(getCurrentSectionId())
  }
  function onScrollOrResize() {
    updateHeaderCssVars()
    if (!ticking) {
      ticking = true
      requestAnimationFrame(tick)
    }
  }

  window.addEventListener('scroll', onScrollOrResize, { passive: true })
  window.addEventListener('resize', onScrollOrResize)
  updateHeaderCssVars()
  tick()
}

function initPublicHeaderNav() {
  const root = document.getElementById('site-header')
  if (!root) return

  const toggle = document.getElementById('nav-toggle')
  const closeBtn = document.getElementById('nav-drawer-close')
  const backdrop = document.getElementById('nav-backdrop')
  const drawer = document.getElementById('nav-drawer')

  function openMenu() {
    if (!isMobileNav()) return
    backdrop?.classList.add('is-open')
    drawer?.classList.add('is-open')
    document.body.classList.add('nav-drawer-open')
    toggle?.setAttribute('aria-expanded', 'true')
    drawer?.setAttribute('aria-hidden', 'false')
    backdrop?.setAttribute('aria-hidden', 'false')
  }

  function closeMenu() {
    backdrop?.classList.remove('is-open')
    drawer?.classList.remove('is-open')
    document.body.classList.remove('nav-drawer-open')
    toggle?.setAttribute('aria-expanded', 'false')
    drawer?.setAttribute('aria-hidden', 'true')
    backdrop?.setAttribute('aria-hidden', 'true')
  }

  toggle?.addEventListener('click', () => {
    if (drawer?.classList.contains('is-open')) closeMenu()
    else openMenu()
  })

  closeBtn?.addEventListener('click', closeMenu)
  backdrop?.addEventListener('click', closeMenu)

  drawer?.querySelectorAll('a[data-section-link]').forEach((link) => {
    link.addEventListener('click', () => {
      if (isMobileNav()) closeMenu()
    })
  })

  drawer?.querySelectorAll('a.nav__cta').forEach((link) => {
    link.addEventListener('click', () => {
      if (isMobileNav()) closeMenu()
    })
  })

  window.addEventListener('resize', () => {
    if (!isMobileNav()) closeMenu()
    updateHeaderCssVars()
  })

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && drawer?.classList.contains('is-open')) {
      closeMenu()
    }
  })
}

function initHeroCarousel() {
  const carousel = document.querySelector('[data-hero-carousel]')
  if (!carousel) return

  const slides = Array.from(carousel.querySelectorAll('.hero-carousel__slide'))
  const dots = Array.from(carousel.querySelectorAll('[data-hero-carousel-dot]'))
  const prev = carousel.querySelector('[data-hero-carousel-prev]')
  const next = carousel.querySelector('[data-hero-carousel-next]')
  let current = 0
  let timer = null

  function show(index) {
    current = (index + slides.length) % slides.length
    slides.forEach((slide, slideIndex) => {
      slide.classList.toggle('is-active', slideIndex === current)
    })
    dots.forEach((dot, dotIndex) => {
      dot.classList.toggle('is-active', dotIndex === current)
    })
  }

  function restart() {
    if (timer) window.clearInterval(timer)
    if (slides.length > 1) {
      timer = window.setInterval(() => show(current + 1), 5000)
    }
  }

  prev?.addEventListener('click', () => {
    show(current - 1)
    restart()
  })

  next?.addEventListener('click', () => {
    show(current + 1)
    restart()
  })

  dots.forEach((dot) => {
    dot.addEventListener('click', () => {
      show(Number(dot.dataset.heroCarouselDot || 0))
      restart()
    })
  })

  if (slides.length > 1) {
    bindSwipe(carousel, {
      onSwipeLeft: () => {
        show(current + 1)
        restart()
      },
      onSwipeRight: () => {
        show(current - 1)
        restart()
      },
    })
  }

  restart()
}

function initReviewsCarousel() {
  const carousel = document.querySelector('[data-reviews-carousel]')
  if (!carousel) return

  const track = carousel.querySelector('.reviews-carousel__track')
  const cards = Array.from(carousel.querySelectorAll('.review-card'))
  const prev = carousel.querySelector('[data-reviews-carousel-prev]')
  const next = carousel.querySelector('[data-reviews-carousel-next]')
  if (!track || !cards.length) return

  let page = 0

  function perPage() {
    return window.matchMedia('(max-width: 767px)').matches ? 1 : 3
  }

  let lastPerPage = perPage()

  function maxPage() {
    const step = perPage()
    return Math.max(0, Math.ceil(cards.length / step) - 1)
  }

  function applySlideWidths() {
    const viewport = carousel.querySelector('.reviews-carousel__viewport')
    if (!viewport) return 0

    const isMobile = perPage() === 1
    const width = isMobile ? viewport.clientWidth : (cards[0]?.getBoundingClientRect().width || 300)

    cards.forEach((card) => {
      if (isMobile) {
        card.style.flexBasis = `${width}px`
        card.style.width = `${width}px`
        card.style.maxWidth = `${width}px`
      } else {
        card.style.flexBasis = ''
        card.style.width = ''
        card.style.maxWidth = ''
      }
    })

    return width
  }

  function stepOffset() {
    const gap = Number.parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || '0') || 0
    const slideWidth = applySlideWidths()
    return perPage() * (slideWidth + gap)
  }

  function syncControls() {
    const step = perPage()
    const needsControls = cards.length > step
    carousel.classList.toggle('reviews-carousel--static', !needsControls)

    if (prev) prev.disabled = page <= 0
    if (next) next.disabled = page >= maxPage()
  }

  function show(nextPage) {
    page = Math.max(0, Math.min(nextPage, maxPage()))
    track.style.transform = `translateX(-${page * stepOffset()}px)`
    syncControls()
  }

  prev?.addEventListener('click', () => show(page - 1))
  next?.addEventListener('click', () => show(page + 1))

  bindSwipe(carousel.querySelector('.reviews-carousel__viewport') || carousel, {
    canSwipe: () => cards.length > perPage(),
    onSwipeLeft: () => show(page + 1),
    onSwipeRight: () => show(page - 1),
  })

  window.addEventListener('resize', () => {
    const currentPerPage = perPage()
    if (currentPerPage !== lastPerPage) {
      page = 0
      lastPerPage = currentPerPage
    }
    show(page)
  })

  show(0)
}

document.addEventListener('DOMContentLoaded', () => {
  initPublicHeaderNav()
  initHeroCarousel()
  initReviewsCarousel()
  initSectionScrollSpy()
})
