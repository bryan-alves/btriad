<script setup lang="ts">
import axios from 'axios'
import { ref, onMounted } from 'vue'
import BaseLayout from '../../layouts/BaseLayout.vue'
import BeltBadge from '../../components/ui/Badge/BeltBadge.vue'
import PaginationBar from '../../components/pagination/PaginationBar.vue'
import { parsePaginatorResponse } from '../../utils/pagination'

const studentGraduations = ref<any[]>([])
const perPage = 15
const meta = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: perPage,
  from: null as number | null,
  to: null as number | null,
})

async function getStudentGraduations(p = 1) {
  try {
    const { data } = await axios.get('/api/student-graduations', {
      params: { page: p, per_page: perPage },
    })
    const { rows, meta: m } = parsePaginatorResponse(data)
    studentGraduations.value = rows
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
  getStudentGraduations(p)
}

onMounted(async () => {
  await getStudentGraduations(1)
})
</script>

<template>
  <BaseLayout
    :title="`Graduações (${meta.total})`"
    action="Nova Graduação"
    actionRoute="/admin/student-graduations/create"
  >
    <div class="student-graduations">
      <div class="table-scroll">
        <table class="student-graduations__table">
          <thead>
            <tr>
              <th>Aluno</th>
              <th>Faixa</th>
              <th>Grau</th>
              <th>Data de Graduação</th>
              <th>Observações</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody v-if="studentGraduations.length">
            <tr v-for="graduation in studentGraduations" :key="graduation.id">
              <td>{{ graduation.student?.name || '-' }}</td>
              <td><BeltBadge :belt="graduation.belt" /></td>
              <td>{{ graduation.degree != null && graduation.degree !== '' ? graduation.degree : '—' }}</td>
              <td>{{ formatDate(graduation.graduated_at) }}</td>
              <td>{{ graduation.notes || '-' }}</td>
              <td>
                <RouterLink :to="`/admin/student-graduations/${graduation.id}/edit`">Editar</RouterLink>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="6" style="text-align: center">Nenhuma graduação registrada!</td>
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
.student-graduations {
  margin: auto;
  font-family: Arial;
}

.student-graduations__table {
  border-collapse: collapse;
  background: white;
}

.student-graduations__table th {
  text-align: left;
  padding: 12px;
  border-bottom: 2px solid #eee;
}

.student-graduations__table td {
  padding: 12px;
  border-bottom: 1px solid #eee;
}

.student-graduations__table tr:hover {
  background: #f9f9f9;
}
</style>
