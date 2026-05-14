/** Metadados do paginator JSON do Laravel (`LengthAwarePaginator`). */
export type PaginatorMeta = {
  current_page: number
  last_page: number
  total: number
  per_page: number
  from: number | null
  to: number | null
}

/**
 * Interpreta resposta de `paginate()` ou array legado (lista completa).
 */
export function parsePaginatorResponse<T>(data: unknown): { rows: T[]; meta: PaginatorMeta | null } {
  if (
    data &&
    typeof data === 'object' &&
    Array.isArray((data as { data?: unknown }).data) &&
    typeof (data as { total?: unknown }).total === 'number'
  ) {
    const d = data as {
      data: T[]
      current_page: number
      last_page: number
      total: number
      per_page: number
      from?: number | null
      to?: number | null
    }
    return {
      rows: d.data,
      meta: {
        current_page: d.current_page,
        last_page: d.last_page,
        total: d.total,
        per_page: d.per_page,
        from: d.from ?? null,
        to: d.to ?? null,
      },
    }
  }
  return { rows: Array.isArray(data) ? (data as T[]) : [], meta: null }
}
