/** URL pública da foto da graduação (caminho em storage ou URL absoluta). */
export function graduationPhotoUrl(row: {
  photo?: string | null
  photo_url?: string | null
  notes?: string | null
} | null | undefined): string | null {
  if (!row) return null
  if (row.photo_url) return row.photo_url
  const path = row.photo ?? row.notes
  if (!path || !String(path).trim()) return null
  const p = String(path).trim()
  if (p.startsWith('http://') || p.startsWith('https://')) return p
  return `/storage/${p.replace(/^\//, '')}`
}
