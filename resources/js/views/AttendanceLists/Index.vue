<script setup lang="ts">
import axios from 'axios'
import { ref, onMounted } from 'vue'
import BaseLayout from '../../layouts/BaseLayout.vue'
import PaginationBar from '../../components/pagination/PaginationBar.vue'
import { parsePaginatorResponse } from '../../utils/pagination'

const attendanceLists = ref<any[]>([])
const perPage = 15
const meta = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: perPage,
  from: null as number | null,
  to: null as number | null,
})

async function getAttendanceLists(p = 1) {
  try {
    const { data } = await axios.get('/api/attendance-lists', {
      params: { page: p, per_page: perPage },
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
              <td><a :href="list.notes">{{ list.notes }}</a></td>
              <td>
                <RouterLink :to="`/admin/attendance-lists/${list.id}/edit`">Editar</RouterLink>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="5" style="text-align: center">Nenhuma lista de presença cadastrada!</td>
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
</style>
