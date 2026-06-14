export function registerAdminPwa() {
  const path = window.location.pathname
  const isAdminPortal = path === '/login' || path.startsWith('/admin')

  if (!isAdminPortal) {
    return
  }

  import('virtual:pwa-register')
    .then(({ registerSW }) => {
      registerSW({ immediate: true })
    })
    .catch(() => {
      // Service worker unavailable (e.g. dev without build)
    })
}
