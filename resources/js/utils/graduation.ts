import { trainingDateKey } from './studentDashboard'

export type GraduationTimelineRow = {
  id: number
  degree?: number
  graduated_at?: string
  photo?: string | null
  photo_url?: string | null
  belt?: { id?: number; slug?: string; name?: string; group?: string }
  classesToReach?: number | null
  classesCountLabel?: string | null
}

/** Faixa branca 0º grau não exibe contagem de aulas. */
export function shouldShowClassesCountForGraduation(row: {
  belt?: { slug?: string }
  degree?: number
}): boolean {
  const degree = Number(row.degree ?? 0)
  return !(row.belt?.slug === 'white' && degree === 0)
}

/** Aulas após `startExclusive` (exclusive) até `endInclusive` (inclusive). */
export function countTrainingsBetweenGraduations(
  trainings: { class_date?: string }[],
  startExclusive: string | null,
  endInclusive: string,
): number {
  let n = 0
  for (const t of trainings) {
    const k = trainingDateKey(t.class_date)
    if (!k) continue
    if (startExclusive && k <= startExclusive) continue
    if (k > endInclusive) continue
    n += 1
  }
  return n
}

/**
 * Para cada graduação, conta aulas desde a graduação anterior (não acumulado desde o início).
 * Ex.: 1º grau = aulas entre 0º e 1º; 2º grau = aulas entre 1º e 2º.
 */
export function buildGraduationTimelineWithClassCounts(
  graduations: GraduationTimelineRow[],
  trainings: { class_date?: string }[],
): GraduationTimelineRow[] {
  const sorted = [...graduations]
    .filter((g) => trainingDateKey(g.graduated_at))
    .sort((a, b) =>
      trainingDateKey(a.graduated_at)!.localeCompare(trainingDateKey(b.graduated_at)!),
    )

  let previousDateKey: string | null = null
  let previousRow: GraduationTimelineRow | null = null

  return sorted.map((row) => {
    const endKey = trainingDateKey(row.graduated_at)!
    const show = shouldShowClassesCountForGraduation(row)
    let classesToReach: number | null = null
    let classesCountLabel: string | null = null

    if (show) {
      classesToReach = countTrainingsBetweenGraduations(
        trainings,
        previousDateKey,
        endKey,
      )
      const unit = classesToReach === 1 ? 'aula' : 'aulas'
      const currDeg = Number(row.degree ?? 0)

      if (previousRow && row.belt?.id === previousRow.belt?.id) {
        const prevDeg = Number(previousRow.degree ?? 0)
        classesCountLabel = `${classesToReach} ${unit} do ${prevDeg}º ao ${currDeg}º grau`
      } else if (previousRow) {
        classesCountLabel = `${classesToReach} ${unit} desde a graduação anterior`
      } else {
        classesCountLabel = `${classesToReach} ${unit} até esta graduação`
      }
    }

    previousDateKey = endKey
    previousRow = row

    return {
      ...row,
      classesToReach: show ? classesToReach : null,
      classesCountLabel: show ? classesCountLabel : null,
    }
  })
}

/** URL pública da foto da graduação (caminho em storage ou URL absoluta). */
export function graduationPhotoUrl(
  row: Pick<GraduationTimelineRow, 'photo' | 'photo_url'> | null | undefined,
): string | null {
  if (!row) return null
  if (row.photo_url) return row.photo_url
  const path = row.photo
  if (!path || !String(path).trim()) return null
  const p = String(path).trim()
  if (p.startsWith('http://') || p.startsWith('https://')) return p
  return `/storage/${p.replace(/^\//, '')}`
}
