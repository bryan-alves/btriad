import { trainingDateKey } from './studentDashboard'

export const RANKING_MONTH_LABELS = [
  'Jan',
  'Fev',
  'Mar',
  'Abr',
  'Mai',
  'Jun',
  'Jul',
  'Ago',
  'Set',
  'Out',
  'Nov',
  'Dez',
] as const

export type AttendanceListForRanking = {
  class_date?: string
  students?: Array<{ id: number; name: string; active?: boolean }>
}

export function parseListYearMonth(list: AttendanceListForRanking) {
  const key = trainingDateKey(list.class_date)
  if (!key) return null
  const [y, m] = key.split('-').map(Number)
  if (!y || !m || m < 1 || m > 12) return null
  return { year: y, month: m, dateKey: key }
}

export function hasAttendanceInMonth(
  lists: AttendanceListForRanking[],
  year: number,
  month: number,
) {
  return lists.some((list) => {
    const p = parseListYearMonth(list)
    return p && p.year === year && p.month === month && list.students?.length
  })
}

export function collectYearsFromLists(lists: AttendanceListForRanking[]) {
  const years = new Set<number>()
  for (const list of lists) {
    const p = parseListYearMonth(list)
    if (p) years.add(p.year)
  }
  return Array.from(years).sort((a, b) => b - a)
}

/**
 * Ranking mensal: conta dias distintos com presença por aluno (várias turmas no mesmo dia = 1 treino).
 * Alinha com a lógica do dashboard do aluno (`studentAttendedDates`).
 */
export function buildMonthlyRanking(
  lists: AttendanceListForRanking[],
  year: number,
  month: number,
  options?: { activeStudentsOnly?: boolean },
) {
  const byStudent = new Map<
    number,
    { id: number; name: string; dates: Set<string> }
  >()

  for (const list of lists) {
    const p = parseListYearMonth(list)
    if (!p || p.year !== year || p.month !== month || !list.students?.length) continue

    for (const student of list.students) {
      if (options?.activeStudentsOnly !== false && student.active === false) continue

      let row = byStudent.get(student.id)
      if (!row) {
        row = { id: student.id, name: student.name, dates: new Set() }
        byStudent.set(student.id, row)
      }
      row.dates.add(p.dateKey)
    }
  }

  return Array.from(byStudent.values())
    .map((s) => ({
      id: s.id,
      name: s.name,
      count: s.dates.size,
    }))
    .sort((a, b) => {
      if (b.count !== a.count) return b.count - a.count
      return a.name.localeCompare(b.name, 'pt-BR')
    })
}

export function normalizeAttendanceListsPayload(data: unknown): AttendanceListForRanking[] {
  if (Array.isArray(data)) return data
  if (data && typeof data === 'object' && Array.isArray((data as { data?: unknown }).data)) {
    return (data as { data: AttendanceListForRanking[] }).data
  }
  return []
}

export type RankingPeriodsResponse = {
  years: number[]
  months_by_year: Record<string, number[]>
}

export type RankingRow = {
  id: number
  name: string
  count: number
}

export function parseRankingPeriodsPayload(data: unknown): RankingPeriodsResponse {
  const empty: RankingPeriodsResponse = { years: [], months_by_year: {} }
  if (!data || typeof data !== 'object') return empty

  const payload = data as { years?: unknown; months_by_year?: unknown }
  const years = Array.isArray(payload.years)
    ? payload.years.map((y) => Number(y)).filter((y) => y > 0)
    : []

  const monthsByYear: Record<string, number[]> = {}
  if (payload.months_by_year && typeof payload.months_by_year === 'object') {
    for (const [yearKey, months] of Object.entries(payload.months_by_year)) {
      if (!Array.isArray(months)) continue
      monthsByYear[yearKey] = months
        .map((m) => Number(m))
        .filter((m) => m >= 1 && m <= 12)
        .sort((a, b) => a - b)
    }
  }

  return { years, months_by_year: monthsByYear }
}

export function monthsWithAttendanceInYear(
  periods: RankingPeriodsResponse,
  year: number,
): number[] {
  return periods.months_by_year[String(year)] ?? []
}

export const RANKING_FULL_YEAR_MONTH = 0

export function isRankingFullYear(month: number): boolean {
  return month === RANKING_FULL_YEAR_MONTH
}

export function pickDefaultRankingMonth(year: number, monthsInYear: number[]): string {
  const now = new Date()
  if (year === now.getFullYear()) {
    return String(RANKING_FULL_YEAR_MONTH)
  }

  if (!monthsInYear.length) return String(RANKING_FULL_YEAR_MONTH)

  return String(monthsInYear[0])
}

export function rankPodiumMeta(position: number): { icon: string; className: string } | null {
  if (position === 1) return { icon: '🏆', className: 'rank-podium rank-podium--gold' }
  if (position === 2) return { icon: '🥈', className: 'rank-podium rank-podium--silver' }
  if (position === 3) return { icon: '🥉', className: 'rank-podium rank-podium--bronze' }
  return null
}
