<script setup lang="ts">
import axios from 'axios'
import { ref, onMounted } from 'vue'
import BaseLayout from '../../layouts/BaseLayout.vue'
import BeltBadge from '../../components/ui/Badge/BeltBadge.vue'
import PaginationBar from '../../components/pagination/PaginationBar.vue'
import FormInput from '../../components/form/FormInput.vue'
import FormSelect from '../../components/form/FormSelect.vue'
import { parsePaginatorResponse } from '../../utils/pagination'

const students = ref<any[]>([])
const belts = ref<{ label: string; value: number | string }[]>([])
const busyId = ref<number | null>(null)
const includeInactive = ref(false)
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
  search: '',
  belt_id: '' as string | number,
  has_registration_form: '' as '' | '1' | '0',
  has_medical_certificate: '' as '' | '1' | '0',
})

const yesNoOptions = [
  { label: 'Todos', value: '' },
  { label: 'Sim', value: '1' },
  { label: 'Não', value: '0' },
]

function calculateAge(birthDate: string | null | undefined) {
  if (!birthDate) {
    return '-'
  }

  const date = new Date(birthDate)

  if (Number.isNaN(date.getTime())) {
    return '-'
  }

  const today = new Date()
  let age = today.getFullYear() - date.getFullYear()
  const monthDiff = today.getMonth() - date.getMonth()

  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < date.getDate())) {
    age--
  }

  return age
}

function isStudentActive(student: { active?: boolean }) {
  return student.active !== false
}

function hasRegistrationForm(student: { registration_form_file?: string | null }) {
  return Boolean(student.registration_form_file)
}

function hasMedicalCertificate(student: { medical_certificate?: string | null }) {
  return Boolean(student.medical_certificate)
}

function buildParams(p: number) {
  const params: Record<string, string | number | boolean> = {
    page: p,
    per_page: perPage,
  }
  if (includeInactive.value) {
    params.include_inactive = 1
  }
  const q = filters.value.search.trim()
  if (q) params.search = q
  if (filters.value.belt_id !== '' && filters.value.belt_id != null) {
    params.belt_id = Number(filters.value.belt_id)
  }
  if (filters.value.has_registration_form !== '') {
    params.has_registration_form = filters.value.has_registration_form === '1' ? 1 : 0
  }
  if (filters.value.has_medical_certificate !== '') {
    params.has_medical_certificate = filters.value.has_medical_certificate === '1' ? 1 : 0
  }
  return params
}

async function getStudents(p = 1) {
  try {
    const { data } = await axios.get('/api/students', { params: buildParams(p) })
    const { rows, meta: m } = parsePaginatorResponse(data)
    students.value = rows
    if (m) {
      meta.value = m
    } else {
      meta.value = {
        current_page: 1,
        last_page: 1,
        total: rows.length,
        per_page: perPage,
        from: rows.length ? 1 : null,
        to: rows.length,
      }
    }
  } catch (error) {
    console.error(error)
  }
}

async function loadBelts() {
  try {
    const { data } = await axios.get('/api/belts')
    belts.value = [
      { label: 'Todas', value: '' },
      ...(data || []).map(({ id, name, group }: { id: number; name: string; group?: string }) => ({
        label: group ? `${name} (${group})` : name,
        value: id,
      })),
    ]
  } catch (error) {
    console.error(error)
  }
}

async function toggleActive(student: { id: number; name: string; active?: boolean }) {
  const activating = student.active === false
  const msg = activating
    ? `Reativar o aluno "${student.name}"?`
    : `Desativar o aluno "${student.name}"? Ele deixará de aparecer nas listas padrão.`
  if (!confirm(msg)) return

  busyId.value = student.id
  try {
    if (activating) {
      await axios.put(`/api/students/${student.id}`, {
        active: true,
        name: student.name,
      })
    } else {
      await axios.delete(`/api/students/${student.id}`)
    }
    await getStudents(meta.value.current_page)
  } catch (error: any) {
    const message = error.response?.data?.message || 'Erro ao alterar estado do aluno'
    alert(message)
    console.error(error)
  } finally {
    busyId.value = null
  }
}

function onIncludeInactiveChange() {
  getStudents(1)
}

function onPageChange(p: number) {
  getStudents(p)
}

function applyFilters() {
  getStudents(1)
}

function clearFilters() {
  filters.value = {
    search: '',
    belt_id: '',
    has_registration_form: '',
    has_medical_certificate: '',
  }
  getStudents(1)
}

onMounted(async () => {
  await loadBelts()
  await getStudents(1)
})
</script>

<template>
  <BaseLayout
    :title="`Alunos (${meta.total})`"
    action="Adicionar aluno"
    actionRoute="/admin/students/create"
  >
    <div class="students">
      <div class="students__filters">
        <FormInput
          v-model="filters.search"
          label="Nome"
          placeholder="Buscar por nome..."
          @keyup.enter="applyFilters"
        />
        <FormSelect
          v-model="filters.belt_id"
          label="Graduação"
          :options="belts"
          placeholder="Todas"
        />
        <FormSelect
          v-model="filters.has_registration_form"
          label="Ficha de cadastro"
          :options="yesNoOptions"
        />
        <FormSelect
          v-model="filters.has_medical_certificate"
          label="Atestado médico"
          :options="yesNoOptions"
        />
        <div class="students__filter-actions">
          <button type="button" class="students__filter-btn" @click="applyFilters">
            Filtrar
          </button>
          <button
            type="button"
            class="students__filter-btn students__filter-btn--ghost"
            @click="clearFilters"
          >
            Limpar
          </button>
        </div>
      </div>

      <label class="students__filter">
        <input v-model="includeInactive" type="checkbox" @change="onIncludeInactiveChange" />
        Mostrar alunos inativos
      </label>

      <div class="table-scroll">
        <table class="students__table">
          <thead>
            <tr>
              <th></th>
              <th>Nome</th>
              <th>Idade</th>
              <th>Graduação</th>
              <th>Ativo</th>
              <th>Ficha</th>
              <th>Atestado</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody v-if="students.length">
            <tr
              v-for="student in students"
              :key="student.id"
              :class="{ 'students__row--inactive': !isStudentActive(student) }"
            >
              <td class="students__avatar-cell">
                <img
                  v-if="student.photo_url"
                  :src="student.photo_url"
                  alt=""
                  class="students__avatar"
                />
                <span v-else class="students__avatar students__avatar--empty">
                  {{ (student.name || '?').charAt(0).toUpperCase() }}
                </span>
              </td>
              <td>{{ student.name }}</td>
              <td>{{ calculateAge(student.birth_date) }}</td>
              <td><BeltBadge :belt="student.belt" /></td>
              <td>{{ isStudentActive(student) ? 'Sim' : 'Não' }}</td>
              <td>{{ hasRegistrationForm(student) ? 'Sim' : 'Não' }}</td>
              <td>{{ hasMedicalCertificate(student) ? 'Sim' : 'Não' }}</td>
              <td class="students__actions">
                <RouterLink :to="`/admin/students/${student.id}`">Detalhes</RouterLink>
                <button
                  type="button"
                  class="students__btn"
                  :class="isStudentActive(student) ? 'students__btn--danger' : 'students__btn--primary'"
                  :disabled="busyId === student.id"
                  @click="toggleActive(student)"
                >
                  {{
                    busyId === student.id
                      ? '…'
                      : isStudentActive(student)
                        ? 'Desativar'
                        : 'Reativar'
                  }}
                </button>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="8" style="text-align: center">Nenhum aluno encontrado.</td>
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
        :disabled="busyId != null"
        @update:page="onPageChange"
      />
    </div>
  </BaseLayout>
</template>

<style lang="scss">
.students {
  margin: auto;
  font-family: Arial;
}

.students__filters {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 0.75rem 1rem;
  margin-bottom: 1rem;
  align-items: end;
}

.students__filter-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  align-items: flex-end;
  padding-bottom: 0.15rem;
}

.students__filter-btn {
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

.students__filter {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  font-size: 0.875rem;
  color: #374151;
  cursor: pointer;
}

.students__row--inactive {
  opacity: 0.65;
  background: #fafafa;
}

.students__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
}

.students__btn {
  font-size: 0.8125rem;
  padding: 6px 10px;
  border-radius: 6px;
  border: 1px solid #ddd;
  cursor: pointer;
  background: #fff;

  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
}

.students__btn--danger {
  border-color: #f87171;
  color: #b91c1c;
}

.students__btn--primary {
  border-color: #86efac;
  color: #166534;
}

.students__table {
  border-collapse: collapse;
  background: white;
}

.students__table th {
  text-align: left;
  padding: 12px;
  border-bottom: 2px solid #eee;
}

.students__table td {
  padding: 12px;
  border-bottom: 1px solid #eee;
}

.students__avatar-cell {
  width: 52px;
  vertical-align: middle;
}

.students__avatar {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  object-fit: cover;
  display: block;
  border: 1px solid #e5e7eb;
}

.students__avatar--empty {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f3f4f6;
  color: #6b7280;
  font-size: 0.875rem;
  font-weight: 700;
}
</style>
