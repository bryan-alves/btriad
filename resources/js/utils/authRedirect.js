function isTruthyPermission(value) {
  return value === true || value === 1 || value === '1' || value === 'true'
}

export function isPlatformAdmin(user) {
  return isTruthyPermission(user?.is_platform_admin)
}

export function canManageSites(user) {
  return isTruthyPermission(user?.can_manage_sites) || isTruthyPermission(user?.canManageSites)
}

export function getDefaultAuthenticatedRoute(user) {
  if (!user) {
    return '/login'
  }

  if (user.role === 'student') {
    return '/student/dashboard'
  }

  if (isPlatformAdmin(user)) {
    return '/admin/platform/tenants'
  }

  return '/admin/students'
}
