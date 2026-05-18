<script setup lang="ts">
import axios from 'axios'
import { ref, onMounted } from 'vue'
import BaseLayout from '../../layouts/BaseLayout.vue'
import BeltBadge from '../../components/ui/Badge/BeltBadge.vue'
import PaginationBar from '../../components/pagination/PaginationBar.vue'
import { parsePaginatorResponse } from '../../utils/pagination'
import { graduationPhotoUrl } from '../../utils/graduation'

const studentGraduations = ref<any[]>([])
const busyId = ref<number | null>(null)
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

async function removeGraduation(graduation: { id: number; student?: { name?: string } }) {
  const name = graduation.student?.name || 'este aluno'
  if (!confirm(`Excluir a graduação de ${name}? A faixa atual do aluno será recalculada.`)) return

  busyId.value = graduation.id
  try {
    await axios.delete(`/api/student-graduations/${graduation.id}`)
    await getStudentGraduations(meta.value.current_page)
  } catch (error: any) {
    alert(error.response?.data?.message || 'Erro ao excluir graduação')
    console.error(error)
  } finally {
    busyId.value = null
  }
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
              <th>Foto</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody v-if="studentGraduations.length">
            <tr v-for="graduation in studentGraduations" :key="graduation.id">
              <td>{{ graduation.student?.name || '-' }}</td>
              <td><BeltBadge :belt="graduation.belt" /></td>
              <td>{{ graduation.degree != null && graduation.degree !== '' ? graduation.degree : '—' }}</td>
              <td>{{ formatDate(graduation.graduated_at) }}</td>
              <td>
                <a
                  v-if="graduationPhotoUrl(graduation)"
                  :href="graduationPhotoUrl(graduation)!"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  Ver foto
                </a>
                <span v-else>—</span>
              </td>
              <td class="grad-actions">
                <RouterLink :to="`/admin/student-graduations/${graduation.id}/edit`">Editar</RouterLink>
                <button
                  type="button"
                  class="grad-actions__btn grad-actions__btn--danger"
                  :disabled="busyId === graduation.id"
                  @click="removeGraduation(graduation)"
                >
                  {{ busyId === graduation.id ? '…' : 'Excluir' }}
                </button>
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
        :disabled="busyId != null"
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

.grad-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
}

.grad-actions__btn {
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

.grad-actions__btn--danger {
  border-color: #f87171;
  color: #b91c1c;
}
</style>
