export type PublicTenant = {
  name?: string | null
  site?: {
    academy_name?: string | null
    primary_color?: string | null
    header_color?: string | null
    background_color?: string | null
    trial_button_color?: string | null
    portal_button_color?: string | null
    app_primary_color?: string | null
    app_header_color?: string | null
    app_background_color?: string | null
    app_login_background_color?: string | null
    logo_url?: string | null
  } | null
}

type WindowWithTenant = Window & {
  __APP_TENANT__?: PublicTenant
}

export function getPublicTenant(): PublicTenant | undefined {
  return (window as WindowWithTenant).__APP_TENANT__
}

export function setPublicTenant(tenant: PublicTenant) {
  ;(window as WindowWithTenant).__APP_TENANT__ = tenant
  applyTenantTheme(tenant)
  window.dispatchEvent(new CustomEvent('app-tenant-updated'))
}

export function getAcademyName(): string {
  const tenant = getPublicTenant()

  return tenant?.site?.academy_name || tenant?.name || 'B-Triad Jiu-Jitsu'
}

export function getLogoUrl(): string {
  return getPublicTenant()?.site?.logo_url || ''
}

export function applyTenantTheme(tenant = getPublicTenant()) {
  if (!tenant?.site || typeof document === 'undefined') return

  const root = document.documentElement
  const site = tenant.site

  root.style.setProperty('--app-primary-color', site.app_primary_color || '#111827')
  root.style.setProperty('--app-header-color', site.app_header_color || '#1b1b18')
  root.style.setProperty('--app-background-color', site.app_background_color || '#f8fafc')
  root.style.setProperty('--app-login-background-color', site.app_login_background_color || '#333333')
}
