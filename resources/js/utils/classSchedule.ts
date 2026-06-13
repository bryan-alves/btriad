export type ScheduleSlot = {
  weekday: number
  start_time: string
  end_time: string
}

export type ScheduleConflict = {
  slot_index: number
  class_id: number
  class_name: string
  message: string
}

export type SchoolClassRow = {
  id: number
  name: string
  type: 'kids' | 'adult'
  schedule_slots?: ScheduleSlot[] | null
  schedule_summary?: string
  active?: boolean
  sort_order?: number
}

export const WEEKDAY_OPTIONS = [
  { value: 1, label: 'Segunda-feira' },
  { value: 2, label: 'Terça-feira' },
  { value: 3, label: 'Quarta-feira' },
  { value: 4, label: 'Quinta-feira' },
  { value: 5, label: 'Sexta-feira' },
  { value: 6, label: 'Sábado' },
  { value: 7, label: 'Domingo' },
]

export const WEEKDAY_LABELS: Record<number, string> = Object.fromEntries(
  WEEKDAY_OPTIONS.map((option) => [option.value, option.label]),
)

function normalizeTime(value: string | null | undefined): string | null {
  if (!value) return null
  const match = value.match(/^(\d{1,2}):(\d{2})/)
  if (!match) return null
  return `${String(Number(match[1])).padStart(2, '0')}:${match[2]}`
}

export function normalizeScheduleSlots(
  slots: Array<Partial<ScheduleSlot>> | null | undefined,
): ScheduleSlot[] {
  return (slots ?? [])
    .map((slot) => {
      const weekday = Number(slot.weekday)
      const startTime = normalizeTime(slot.start_time)
      if (!weekday || weekday < 1 || weekday > 7 || !startTime) return null

      return {
        weekday,
        start_time: startTime,
        end_time: normalizeTime(slot.end_time) || '',
      }
    })
    .filter((slot): slot is ScheduleSlot => slot !== null)
    .sort((a, b) => `${a.weekday}-${a.start_time}`.localeCompare(`${b.weekday}-${b.start_time}`))
}

function toMinutes(time: string): number {
  const match = time.match(/^(\d{1,2}):(\d{2})/)
  if (!match) return 0
  return Number(match[1]) * 60 + Number(match[2])
}

function minuteRange(startTime: string, endTime: string | null | undefined): [number, number] {
  const start = toMinutes(startTime)
  let end = endTime ? toMinutes(endTime) : start + 60
  if (end <= start) end = start + 60
  return [start, end]
}

export function slotsOverlap(a: ScheduleSlot, b: ScheduleSlot): boolean {
  if (a.weekday !== b.weekday) return false
  const [aStart, aEnd] = minuteRange(a.start_time, a.end_time)
  const [bStart, bEnd] = minuteRange(b.start_time, b.end_time)
  return aStart < bEnd && bStart < aEnd
}

export function formatSlotRange(startTime: string, endTime?: string | null): string {
  const format = (time: string) => {
    const match = time.match(/^(\d{1,2}):(\d{2})/)
    if (!match) return time
    const hour = Number(match[1])
    const minute = match[2]
    return minute === '00' ? `${hour}h` : `${hour}h ${minute}`
  }

  const start = format(startTime)
  const end = endTime ? format(endTime) : null
  return end ? `${start} - ${end}` : start
}

export function detectScheduleConflicts(
  slots: ScheduleSlot[],
  allClasses: SchoolClassRow[],
  ignoreClassId?: number | null,
): ScheduleConflict[] {
  const normalized = normalizeScheduleSlots(slots)
  const warnings: ScheduleConflict[] = []

  normalized.forEach((slot, index) => {
    normalized.forEach((otherSlot, otherIndex) => {
      if (otherIndex <= index) return
      if (!slotsOverlap(slot, otherSlot)) return

      warnings.push({
        slot_index: index,
        class_id: ignoreClassId || 0,
        class_name: '',
        message: `Conflito entre horários desta turma em ${WEEKDAY_LABELS[slot.weekday] || ''} (${formatSlotRange(slot.start_time, slot.end_time)}).`,
      })
    })

    allClasses.forEach((otherClass) => {
      if (ignoreClassId && otherClass.id === ignoreClassId) return
      if (otherClass.active === false) return

      normalizeScheduleSlots(otherClass.schedule_slots).forEach((otherSlot) => {
        if (!slotsOverlap(slot, otherSlot)) return

        warnings.push({
          slot_index: index,
          class_id: otherClass.id,
          class_name: otherClass.name,
          message: `Conflito com "${otherClass.name}" em ${WEEKDAY_LABELS[slot.weekday] || ''} (${formatSlotRange(slot.start_time, slot.end_time)}).`,
        })
      })
    })
  })

  return warnings
}

export function summarizeScheduleSlots(slots: ScheduleSlot[] | null | undefined): string {
  const normalized = normalizeScheduleSlots(slots)
  if (!normalized.length) return '—'

  const shortDay: Record<number, string> = {
    1: 'Seg',
    2: 'Ter',
    3: 'Qua',
    4: 'Qui',
    5: 'Sex',
    6: 'Sáb',
    7: 'Dom',
  }

  return normalized
    .map((slot) => `${shortDay[slot.weekday] || '?'} ${formatSlotRange(slot.start_time, slot.end_time)}`)
    .join(' · ')
}

export function classOptionLabel(classItem: SchoolClassRow): string {
  const typeLabel = classItem.type === 'adult' ? 'Adulto' : 'Kids'
  const schedule = classItem.schedule_summary || summarizeScheduleSlots(classItem.schedule_slots)
  return `${classItem.name} (${typeLabel}) · ${schedule}`
}

export function emptyScheduleSlot(): ScheduleSlot {
  return { weekday: 1, start_time: '', end_time: '' }
}
