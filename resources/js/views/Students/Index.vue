<script setup lang="ts">
import axios from 'axios'
import { ref, onMounted } from 'vue'
import BaseLayout from '../../layouts/BaseLayout.vue'
import BeltBadge from '../../components/ui/Badge/BeltBadge.vue'
import PaginationBar from '../../components/pagination/PaginationBar.vue'
import { parsePaginatorResponse } from '../../utils/pagination'

const students = ref<any[]>([])
const perPage = 15
const meta = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: perPage,
  from: null as number | null,
  to: null as number | null,
})

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

async function getStudents(p = 1) {
  try {
    const { data } = await axios.get('/api/students', {
      params: { page: p, per_page: perPage },
    })
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

function onPageChange(p: number) {
  getStudents(p)
}

onMounted(async () => {
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
      <div class="table-scroll">
        <table class="students__table">
          <thead>
            <tr>
              <th></th>
              <th>Nome</th>
              <th>Idade</th>
              <th>Graduação</th>
              <th>Ficha de cadastro</th>
              <th>Atestado médico</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody v-if="students.length">
            <tr v-for="student in students" :key="student.id">
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
              <td>{{ student.registration_form ? 'Sim' : 'Não' }}</td>
              <td>{{ student.medical_certificate ? 'Sim' : 'Não' }}</td>
              <td>
                <RouterLink :to="`/admin/students/${student.id}`">Detalhes</RouterLink>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="8" style="text-align: center">Não há alunos cadastrados!</td>
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
.students {
  margin: auto;
  font-family: Arial;
}

.students__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.students__actions {
  display: flex;
  gap: 10px;
}

.students__actions input {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 6px;
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

.btn-details {
  padding: 6px 10px;
  border: 1px solid #ddd;
  background: white;
  border-radius: 6px;
  cursor: pointer;
}
</style>
