<script setup lang="ts">
import axios from 'axios'
import { ref, onMounted } from 'vue'
import BaseLayout from '../../layouts/BaseLayout.vue'
import PaginationBar from '../../components/pagination/PaginationBar.vue'
import FormInput from '../../components/form/FormInput.vue'
import FormSelect from '../../components/form/FormSelect.vue'
import { parsePaginatorResponse } from '../../utils/pagination'
import { toastDanger } from '../../utils/toast'

const attendanceLists = ref<any[]>([])
const deletingId = ref<number | null>(null)
const perPage = 15
const meta = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: perPage,
  from: null as number | null,
  to: null as number | null,
})

const filters = ref({
  date_from: '',
  date_to: '',
  class_type: '' as '' | 'adult' | 'kids',
})

const classTypeOptions = [
  { label: 'Todos', value: '' },
  { label: 'Adulto', value: 'adult' },
  { label: 'Kids', value: 'kids' },
]

function buildParams(p: number) {
  const params: Record<string, string | number> = {
    page: p,
    per_page: perPage,
  }
  if (filters.value.date_from) params.date_from = filters.value.date_from
  if (filters.value.date_to) params.date_to = filters.value.date_to
  if (filters.value.class_type) params.class_type = filters.value.class_type
  return params
}

async function getAttendanceLists(p = 1) {
  try {
    const { data } = await axios.get('/api/attendance-lists', {
      params: buildParams(p),
    })
    const { rows, meta: m } = parsePaginatorResponse(data)
    attendanceLists.value = rows
    if (m) {
      meta.value = m
    }
  } catch (error) {
    console.error(error)
  }
}

function formatDate(date: string) {
  return date.split('T')[0].split('-').reverse().join('/')
}

function onPageChange(p: number) {
  getAttendanceLists(p)
}

function applyFilters() {
  getAttendanceLists(1)
}

function clearFilters() {
  filters.value = { date_from: '', date_to: '', class_type: '' }
  getAttendanceLists(1)
}

function formatListLabel(list: { class_date?: string; school_class?: { name?: string } }) {
  const date = list.class_date ? formatDate(list.class_date) : '—'
  const turma = list.school_class?.name?.trim()
  return turma ? `${date} (${turma})` : date
}

async function removeAttendanceList(list: { id: number; class_date?: string; school_class?: { name?: string } }) {
  if (!confirm(`Excluir o treino de ${formatListLabel(list)}? Esta ação não pode ser desfeita.`)) {
    return
  }

  deletingId.value = list.id
  try {
    await axios.delete(`/api/attendance-lists/${list.id}`)
    await getAttendanceLists(meta.value.current_page)
  } catch (error: any) {
    toastDanger(error.response?.data?.message || 'Erro ao excluir treino')
    console.error(error)
  } finally {
    deletingId.value = null
  }
}

onMounted(async () => {
  await getAttendanceLists(1)
})
</script>

<template>
  <BaseLayout
    :title="`Listas de Presença (${meta.total})`"
    action="Nova Lista"
    actionRoute="/admin/attendance-lists/create"
  >
    <div class="attendance-lists">
      <div class="attendance-lists__filters">
        <FormInput v-model="filters.date_from" type="date" label="Data inicial" />
        <FormInput v-model="filters.date_to" type="date" label="Data final" />
        <FormSelect
          v-model="filters.class_type"
          label="Tipo de turma"
          :options="classTypeOptions"
        />
        <div class="attendance-lists__filter-actions">
          <button type="button" class="attendance-lists__filter-btn" @click="applyFilters">
            Filtrar
          </button>
          <button
            type="button"
            class="attendance-lists__filter-btn attendance-lists__filter-btn--ghost"
            @click="clearFilters"
          >
            Limpar
          </button>
        </div>
      </div>

      <div class="table-scroll">
        <table class="attendance-lists__table">
          <thead>
            <tr>
              <th>Data da Aula</th>
              <th>Tipo de Turma</th>
              <th>Total de Alunos</th>
              <th>Foto</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody v-if="attendanceLists.length">
            <tr v-for="list in attendanceLists" :key="list.id">
              <td>{{ formatDate(list.class_date) }}</td>
              <td>{{ list.school_class?.type === 'kids' ? 'Kids' : list.school_class?.type === 'adult' ? 'Adulto' : '—' }}</td>
              <td>{{ list.students?.length || 0 }}</td>
              <td>
                <a
                  v-if="list.photo"
                  :href="list.photo"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  Ver foto
                </a>
                <span v-else>—</span>
              </td>
              <td class="attendance-lists__actions">
                <RouterLink :to="`/admin/attendance-lists/${list.id}/edit`">Editar</RouterLink>
                <button
                  type="button"
                  class="attendance-lists__btn attendance-lists__btn--danger"
                  :disabled="deletingId === list.id"
                  @click="removeAttendanceList(list)"
                >
                  {{ deletingId === list.id ? '…' : 'Excluir' }}
                </button>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="5" style="text-align: center">Nenhuma lista de presença encontrada.</td>
            </tr>
          </tbody>
        </table>
      </div>
      <PaginationBar
        :current-page="meta.current_page"
        :last-page="meta.last_page"
        :total="meta.total"
        :per-page="meta.per_page"
        :from="meta.from"
        :to="meta.to"
        @update:page="onPageChange"
      />
    </div>
  </BaseLayout>
</template>

<style lang="scss">
.attendance-lists {
  margin: auto;
  font-family: Arial;
}

.attendance-lists__filters {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 0.75rem 1rem;
  margin-bottom: 1rem;
  align-items: end;
}

.attendance-lists__filter-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  padding-bottom: 0.15rem;
}

.attendance-lists__filter-btn {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  font-weight: 600;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  background: #111827;
  color: #fff;
  cursor: pointer;

  &--ghost {
    background: #fff;
    color: #374151;
  }
}

.attendance-lists__table {
  border-collapse: collapse;
  background: white;
}

.attendance-lists__table th {
  text-align: left;
  padding: 12px;
  border-bottom: 2px solid #eee;
}

.attendance-lists__table td {
  padding: 12px;
  border-bottom: 1px solid #eee;
}

.attendance-lists__table tr:hover {
  background: #f9f9f9;
}

.attendance-lists__actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.65rem;
}

.attendance-lists__btn {
  padding: 0;
  border: none;
  background: none;
  font-size: inherit;
  font-family: inherit;
  cursor: pointer;
  color: #b91c1c;
  font-weight: 600;

  &:disabled {
    opacity: 0.6;
    cursor: wait;
  }

  &:hover:not(:disabled) {
    text-decoration: underline;
  }
}
</style>
