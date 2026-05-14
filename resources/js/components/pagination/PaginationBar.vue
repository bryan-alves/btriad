<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  /** Página atual (1-based) */
  currentPage: number
  /** Total de páginas */
  lastPage: number
  /** Total de registros */
  total: number
  /** Itens por página */
  perPage: number
  /** Primeiro índice da página (retorno Laravel `from`) */
  from?: number | null
  /** Último índice da página (retorno Laravel `to`) */
  to?: number | null
  disabled?: boolean
}>()

const emit = defineEmits<{
  'update:page': [page: number]
}>()

const summary = computed(() => {
  const t = props.total
  if (t === 0) return 'Nenhum registro'
  const from = props.from ?? (t > 0 ? (props.currentPage - 1) * props.perPage + 1 : 0)
  const to = props.to ?? Math.min(props.currentPage * props.perPage, t)
  return `Mostrando ${from}–${to} de ${t}`
})

const windowPages = computed(() => {
  const last = Math.max(1, props.lastPage)
  const cur = Math.min(Math.max(1, props.currentPage), last)
  if (last <= 9) {
    return Array.from({ length: last }, (_, i) => i + 1)
  }
  const pages: (number | 'ellipsis')[] = []
  const delta = 2
  pages.push(1)
  const start = Math.max(2, cur - delta)
  const end = Math.min(last - 1, cur + delta)
  if (start > 2) {
    pages.push('ellipsis')
  }
  for (let p = start; p <= end; p++) {
    pages.push(p)
  }
  if (end < last - 1) {
    pages.push('ellipsis')
  }
  if (last > 1) {
    pages.push(last)
  }
  return pages
})

function go(p: number) {
  if (props.disabled || p < 1 || p > props.lastPage || p === props.currentPage) return
  emit('update:page', p)
}
</script>

<template>
  <div v-if="lastPage >= 1 && total > 0" class="pagination-bar">
    <p class="pagination-bar__summary">{{ summary }}</p>
    <nav v-if="lastPage > 1" class="pagination-bar__nav" aria-label="Paginação">
      <button
        type="button"
        class="pagination-bar__btn"
        :disabled="disabled || currentPage <= 1"
        aria-label="Página anterior"
        @click="go(currentPage - 1)"
      >
        ‹
      </button>
      <template v-for="(p, idx) in windowPages" :key="idx">
        <span v-if="p === 'ellipsis'" class="pagination-bar__ellipsis">…</span>
        <button
          v-else
          type="button"
          class="pagination-bar__btn"
          :class="{ 'pagination-bar__btn--active': p === currentPage }"
          :disabled="disabled"
          :aria-current="p === currentPage ? 'page' : undefined"
          @click="go(p)"
        >
          {{ p }}
        </button>
      </template>
      <button
        type="button"
        class="pagination-bar__btn"
        :disabled="disabled || currentPage >= lastPage"
        aria-label="Próxima página"
        @click="go(currentPage + 1)"
      >
        ›
      </button>
    </nav>
  </div>
</template>

<style scoped lang="scss">
.pagination-bar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem 1rem;
  margin-top: 1rem;
  padding-top: 0.75rem;
  border-top: 1px solid #e5e7eb;
}

.pagination-bar__summary {
  margin: 0;
  font-size: 0.8125rem;
  color: #6b7280;
}

.pagination-bar__nav {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.25rem;
}

.pagination-bar__btn {
  min-width: 2.25rem;
  height: 2.25rem;
  padding: 0 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: #fff;
  color: #374151;
  font-size: 0.8125rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.12s ease, border-color 0.12s ease;

  &:hover:not(:disabled) {
    background: #f9fafb;
    border-color: #9ca3af;
  }

  &:disabled {
    opacity: 0.45;
    cursor: not-allowed;
  }
}

.pagination-bar__btn--active {
  background: #111827;
  border-color: #111827;
  color: #fff;
}

.pagination-bar__ellipsis {
  padding: 0 0.25rem;
  color: #9ca3af;
  font-size: 0.875rem;
  user-select: none;
}
</style>
