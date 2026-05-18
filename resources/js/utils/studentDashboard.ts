/** Data YYYY-MM-DD a partir de class_date da API. */
export function trainingDateKey(raw: string | null | undefined): string | null {
  if (!raw) return null
  const d = String(raw).trim().split(/[T ]/)[0]
  return /^\d{4}-\d{2}-\d{2}$/.test(d) ? d : null
}

export function uniqueSortedTrainingDates(trainings: { class_date?: string }[]): string[] {
  const set = new Set<string>()
  for (const t of trainings) {
    const k = trainingDateKey(t.class_date)
    if (k) set.add(k)
  }
  return Array.from(set).sort((a, b) => a.localeCompare(b))
}

export function trainingsInMonth(
  trainings: { class_date?: string }[],
  year: number,
  month1to12: number,
): number {
  const p = `${year}-${String(month1to12).padStart(2, '0')}`
  let n = 0
  for (const t of trainings) {
    const k = trainingDateKey(t.class_date)
    if (k && k.startsWith(p)) n += 1
  }
  return n
}

export function lastTrainingDate(trainings: { class_date?: string }[]): string | null {
  const dates = uniqueSortedTrainingDates(trainings)
  return dates.length ? dates[dates.length - 1] : null
}

/** Quantidade de listas de presença (treinos distintos) por dia no calendário da academia. */
export function sessionsPerDate(
  attendanceLists: { class_date?: string }[],
): Map<string, number> {
  const map = new Map<string, number>()
  for (const list of attendanceLists) {
    const k = trainingDateKey(list.class_date)
    if (!k) continue
    map.set(k, (map.get(k) ?? 0) + 1)
  }
  return map
}

/** Dias em que o aluno compareceu a pelo menos uma lista naquele dia (várias turmas no mesmo dia contam como 1 “compareceu”). */
export function studentAttendedDates(
  studentId: number,
  attendanceLists: { class_date?: string; students?: { id: number }[] }[],
): Set<string> {
  const set = new Set<string>()
  for (const list of attendanceLists) {
    const k = trainingDateKey(list.class_date)
    if (!k) continue
    const present = (list.students || []).some((s) => s.id === studentId)
    if (present) set.add(k)
  }
  return set
}

function formatDateKeyLocal(d: Date): string {
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}

function todayKeyLocal(): string {
  return formatDateKeyLocal(new Date())
}

function subtractCalendarDays(dateKey: string, daysBack: number): string {
  const d = new Date(`${dateKey}T12:00:00`)
  d.setDate(d.getDate() - daysBack)
  return formatDateKeyLocal(d)
}

/**
 * Streak de dias “bons” até hoje, andando para trás no calendário.
 *
 * Regra (por dia):
 * - **Neutro**: não houve nenhuma lista de presença cadastrada nesse dia → não quebra nem soma.
 * - **Bom**: houve ≥1 lista e o aluno consta em pelo menos uma → soma +1 no streak.
 * - **Ruim**: houve ≥1 lista e o aluno não consta em nenhuma → encerra o streak.
 *
 * Assim, no mesmo dia com aula 1 e aula 2, se ele foi só na aula 1, o dia conta como bom.
 */
export function currentDayStreak(
  studentId: number,
  attendanceLists: { class_date?: string; students?: { id: number }[] }[],
  maxLookbackDays = 800,
): number {
  const perDay = sessionsPerDate(attendanceLists)
  const attended = studentAttendedDates(studentId, attendanceLists)
  const today = todayKeyLocal()
  let streak = 0
  for (let i = 0; i < maxLookbackDays; i += 1) {
    const key = subtractCalendarDays(today, i)
    const n = perDay.get(key) ?? 0
    if (n === 0) continue
    if (attended.has(key)) streak += 1
    else break
  }
  return streak
}

/**
 * Maior sequência de dias “bons” no intervalo [primeira data com lista, hoje],
 * com dias neutros no meio (sem listas) não zerando a sequência.
 */
export function maxDayStreak(
  studentId: number,
  attendanceLists: { class_date?: string; students?: { id: number }[] }[],
): number {
  const perDay = sessionsPerDate(attendanceLists)
  const attended = studentAttendedDates(studentId, attendanceLists)
  let minKey: string | null = null
  for (const list of attendanceLists) {
    const k = trainingDateKey(list.class_date)
    if (!k) continue
    if (!minKey || k.localeCompare(minKey) < 0) minKey = k
  }
  if (!minKey) return 0

  const end = todayKeyLocal()
  let run = 0
  let best = 0
  const start = new Date(`${minKey}T12:00:00`)
  const finish = new Date(`${end}T12:00:00`)
  const cur = new Date(start)
  while (cur.getTime() <= finish.getTime()) {
    const key = formatDateKeyLocal(cur)
    const n = perDay.get(key) ?? 0
    if (n === 0) {
      /* neutro */
    } else if (attended.has(key)) {
      run += 1
      best = Math.max(best, run)
    } else {
      run = 0
    }
    cur.setDate(cur.getDate() + 1)
  }
  return best
}

function isoWeekMonday(d: Date): string {
  const x = new Date(d)
  const day = (x.getDay() + 6) % 7
  x.setDate(x.getDate() - day)
  const y = x.getFullYear()
  const m = String(x.getMonth() + 1).padStart(2, '0')
  const dayNum = String(x.getDate()).padStart(2, '0')
  return `${y}-${m}-${dayNum}`
}

/** Semanas (chave segunda-feira ISO simplificada) com pelo menos um treino. */
export function uniqueWeekKeys(trainings: { class_date?: string }[]): string[] {
  const set = new Set<string>()
  for (const t of trainings) {
    const k = trainingDateKey(t.class_date)
    if (!k) continue
    set.add(isoWeekMonday(new Date(`${k}T12:00:00`)))
  }
  return Array.from(set).sort((a, b) => a.localeCompare(b))
}

/** Semanas consecutivas com treino, contando a partir da semana do último treino. */
export function currentWeekStreak(trainings: { class_date?: string }[]): number {
  const weeks = uniqueWeekKeys(trainings)
  if (!weeks.length) return 0
  const set = new Set(weeks)
  const last = weeks[weeks.length - 1]
  let cur = new Date(`${last}T12:00:00`)
  let streak = 0
  while (true) {
    const key = isoWeekMonday(cur)
    if (!set.has(key)) break
    streak += 1
    cur.setDate(cur.getDate() - 7)
  }
  return streak
}

export function monthlyFrequencyPercent(
  trainingsThisMonth: number,
  monthlyGoal: number,
): number {
  if (monthlyGoal <= 0) return 0
  return Math.min(100, Math.round((trainingsThisMonth / monthlyGoal) * 100))
}

/** Frequência de presença: treinos do aluno ÷ total de aulas no mês na academia. */
export function attendanceFrequencyPercent(attended: number, totalSessions: number): number {
  if (totalSessions <= 0) return 0
  return Math.min(100, Math.round((attended / totalSessions) * 100))
}

export type TrainingWithClass = {
  class_date?: string
  class_id?: number
  school_class?: { id?: number; name?: string; type?: string }
}

export type StudentTrainingsPayload = {
  trainings: TrainingWithClass[]
  academySessionsByMonth: Record<string, number>
  academySessionsByClassMonth: Record<string, Record<string, number>>
}

export function trainingListClassId(list: TrainingWithClass): number | null {
  const id = list.class_id ?? list.school_class?.id
  return id != null ? Number(id) : null
}

export function filterTrainingsByClassId(
  trainings: TrainingWithClass[],
  classId: number,
): TrainingWithClass[] {
  return trainings.filter((t) => trainingListClassId(t) === classId)
}

/** Turmas com pelo menos um treino no ano informado (YYYY). */
export function collectClassesFromTrainingsInYear(
  trainings: TrainingWithClass[],
  year: string | number,
): { id: number; label: string }[] {
  const yearStr = String(year)
  const inYear = trainings.filter((t) => {
    const k = trainingDateKey(t.class_date)
    return k && k.startsWith(`${yearStr}-`)
  })
  return collectClassesFromTrainings(inYear)
}

export function collectClassesFromTrainings(
  trainings: TrainingWithClass[],
): { id: number; label: string }[] {
  const map = new Map<number, { id: number; label: string }>()
  for (const t of trainings) {
    const id = trainingListClassId(t)
    if (!id) continue
    if (map.has(id)) continue
    const c = t.school_class
    const name = c?.name?.trim() || `Turma ${id}`
    const typeLabel =
      c?.type === 'kids' ? 'Kids' : c?.type === 'adult' ? 'Adulto' : ''
    map.set(id, {
      id,
      label: typeLabel ? `${name} (${typeLabel})` : name,
    })
  }
  return Array.from(map.values()).sort((a, b) =>
    a.label.localeCompare(b.label, 'pt-BR'),
  )
}

export const TRAINING_CLASS_FILTER_ALL = 'all'

export function isTrainingClassAllFilter(value: string): boolean {
  return value === TRAINING_CLASS_FILTER_ALL || value === ''
}

export function sessionsForClassMonth(
  byClassMonth: Record<string, Record<string, number>>,
  classId: number,
  monthKey: string,
): number {
  return byClassMonth[String(classId)]?.[monthKey] ?? 0
}

/** Soma aulas do mês nas turmas em que o aluno já treinou. */
export function sessionsForStudentClassesMonth(
  byClassMonth: Record<string, Record<string, number>>,
  classIds: number[],
  monthKey: string,
): number {
  let total = 0
  for (const classId of classIds) {
    total += sessionsForClassMonth(byClassMonth, classId, monthKey)
  }
  return total
}

export type StudentHistoryBounds = {
  startYm: string
  endYm: string
}

export function yearMonthKeyFromDate(d: Date): string {
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
}

/** Intervalo de meses do histórico: do primeiro treino até hoje (ativo) ou desativação. */
export function getStudentTrainingHistoryBounds(
  student: {
    first_class_at?: string | null
    active?: boolean
    updated_at?: string
  } | null,
  trainings: { class_date?: string }[],
): StudentHistoryBounds | null {
  let startYm: string | null = null

  const firstClass = trainingDateKey(student?.first_class_at ?? undefined)
  if (firstClass) {
    startYm = firstClass.slice(0, 7)
  }

  for (const t of trainings) {
    const k = trainingDateKey(t.class_date)
    if (k) {
      const ym = k.slice(0, 7)
      if (!startYm || ym < startYm) startYm = ym
    }
  }

  if (!startYm) return null

  let endYm: string
  if (student?.active !== false) {
    endYm = yearMonthKeyFromDate(new Date())
  } else if (student?.updated_at) {
    const raw = String(student.updated_at).trim().split(/[T ]/)[0]
    const dk = trainingDateKey(raw)
    endYm = dk ? dk.slice(0, 7) : yearMonthKeyFromDate(new Date())
  } else {
    endYm = yearMonthKeyFromDate(new Date())
  }

  if (endYm < startYm) {
    endYm = startYm
  }

  return { startYm, endYm }
}

/** Meses consecutivos entre YYYY-MM (inclusive), do mais antigo ao mais recente. */
export function enumerateMonthKeysBetween(startYm: string, endYm: string): string[] {
  const [sy, sm] = startYm.split('-').map(Number)
  const [ey, em] = endYm.split('-').map(Number)
  const keys: string[] = []
  let y = sy
  let m = sm

  while (y < ey || (y === ey && m <= em)) {
    keys.push(`${y}-${String(m).padStart(2, '0')}`)
    m += 1
    if (m > 12) {
      m = 1
      y += 1
    }
  }

  return keys
}

export function parseStudentTrainingsPayload(data: unknown): StudentTrainingsPayload {
  if (Array.isArray(data)) {
    return {
      trainings: data,
      academySessionsByMonth: {},
      academySessionsByClassMonth: {},
    }
  }
  if (data && typeof data === 'object') {
    const payload = data as {
      trainings?: unknown
      academy_sessions_by_month?: Record<string, number>
      academy_sessions_by_class_month?: Record<string, Record<string, number>>
    }
    return {
      trainings: Array.isArray(payload.trainings) ? payload.trainings : [],
      academySessionsByMonth: payload.academy_sessions_by_month ?? {},
      academySessionsByClassMonth: payload.academy_sessions_by_class_month ?? {},
    }
  }
  return { trainings: [], academySessionsByMonth: {}, academySessionsByClassMonth: {} }
}

export interface AnnualRankResult {
  rank: number | null
  totalRanked: number
  totalTrainingsYear: number
}

export function annualRankForStudent(
  attendanceLists: { class_date?: string; students?: { id: number }[] }[],
  studentId: number,
  year: number,
): AnnualRankResult {
  const counts = new Map<number, number>()
  for (const list of attendanceLists) {
    const k = trainingDateKey(list.class_date)
    if (!k || !k.startsWith(String(year))) continue
    for (const s of list.students || []) {
      counts.set(s.id, (counts.get(s.id) ?? 0) + 1)
    }
  }
  const rows = Array.from(counts.entries())
    .map(([id, count]) => ({ id, count }))
    .sort((a, b) => b.count - a.count || a.id - b.id)
  const idx = rows.findIndex((r) => r.id === studentId)
  const mine = rows[idx]
  return {
    rank: idx === -1 ? null : idx + 1,
    totalRanked: rows.length,
    totalTrainingsYear: mine?.count ?? 0,
  }
}

/** Últimos `months` meses (mais antigo primeiro) com contagem de treinos. */
export function lastMonthsTrainingCounts(
  trainings: { class_date?: string }[],
  months: number,
): { key: string; label: string; count: number }[] {
  const now = new Date()
  const out: { key: string; label: string; count: number }[] = []
  for (let i = months - 1; i >= 0; i -= 1) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
    const y = d.getFullYear()
    const m = d.getMonth() + 1
    const key = `${y}-${String(m).padStart(2, '0')}`
    const label = d.toLocaleDateString('pt-BR', { month: 'short', year: '2-digit' })
    const count = trainingsInMonth(trainings, y, m)
    out.push({ key, label: label.replace(/\./g, ''), count })
  }
  return out
}
