/**
 * Site público: drawer do menu, variáveis CSS do header empilhado e scroll-spy das seções.
 */
const SECTION_IDS = ['sobre', 'horarios', 'avaliacoes', 'localizacao']

function isMobileNav() {
  return window.matchMedia('(max-width: 899px)').matches
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

document.addEventListener('DOMContentLoaded', () => {
  initPublicHeaderNav()
  initSectionScrollSpy()
})
