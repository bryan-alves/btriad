<script setup lang="ts">
import axios from 'axios'
import { ref, onMounted } from 'vue'
import BaseLayout from '../../layouts/BaseLayout.vue'
import { summarizeScheduleSlots, type SchoolClassRow } from '../../utils/classSchedule'
import { toastDanger, toastSuccess } from '../../utils/toast'

const classes = ref<SchoolClassRow[]>([])
const reordering = ref(false)

async function getClasses() {
  try {
    const { data } = await axios.get('/api/classes')
    classes.value = Array.isArray(data) ? data : []
  } catch (error) {
    console.error(error)
  }
}

function scheduleLabel(classItem: SchoolClassRow) {
  return classItem.schedule_summary || summarizeScheduleSlots(classItem.schedule_slots)
}

async function persistOrder() {
  if (!classes.value.length) return

  reordering.value = true

  try {
    await axios.post('/api/classes/reorder', {
      order: classes.value.map((item) => item.id),
    })
    toastSuccess('Ordem das turmas atualizada.')
  } catch (error: any) {
    toastDanger(error.response?.data?.message || 'Erro ao atualizar a ordem das turmas.')
    await getClasses()
  } finally {
    reordering.value = false
  }
}

function moveClass(index: number, direction: -1 | 1) {
  const target = index + direction
  if (target < 0 || target >= classes.value.length) return

  const items = [...classes.value]
  const [moved] = items.splice(index, 1)
  items.splice(target, 0, moved)
  classes.value = items
  persistOrder()
}

onMounted(async () => {
  await getClasses()
})
</script>

<template>
  <BaseLayout :title="`Turmas (${classes.length})`" action="Nova Turma" actionRoute="/admin/classes/create">
    <div class="classes">
      <div class="table-scroll">
      <table class="classes__table">
        <thead>
          <tr>
            <th>Ordem</th>
            <th>Nome</th>
            <th>Tipo</th>
            <th>Horários</th>
            <th>Ativa</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody v-if="classes.length">
          <tr v-for="(classItem, index) in classes" :key="classItem.id">
            <td class="classes__order">
              <button
                type="button"
                class="classes__order-btn"
                :disabled="index === 0 || reordering"
                aria-label="Subir turma"
                @click="moveClass(index, -1)"
              >
                ↑
              </button>
              <button
                type="button"
                class="classes__order-btn"
                :disabled="index === classes.length - 1 || reordering"
                aria-label="Descer turma"
                @click="moveClass(index, 1)"
              >
                ↓
              </button>
            </td>
            <td>{{ classItem.name }}</td>
            <td>{{ classItem.type === 'kids' ? 'Kids' : classItem.type === 'adult' ? 'Adulto' : '—' }}</td>
            <td class="classes__schedule">{{ scheduleLabel(classItem) }}</td>
            <td>{{ classItem.active ? 'Sim' : 'Não' }}</td>
            <td>
              <RouterLink :to="`/admin/classes/${classItem.id}`">Editar</RouterLink>
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="6" style="text-align: center">Nenhuma turma cadastrada!</td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>
  </BaseLayout>
</template>

<style lang="scss">
.classes {
  margin: auto;
  font-family: Arial;
}

.classes__table {
  border-collapse: collapse;
  background: white;
}

.classes__table th {
  text-align: left;
  padding: 12px;
  border-bottom: 2px solid #eee;
}

.classes__table td {
  padding: 12px;
  border-bottom: 1px solid #eee;
}

.classes__order {
  white-space: nowrap;
}

.classes__order-btn {
  width: 2rem;
  height: 2rem;
  margin-right: 0.25rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: #fff;
  cursor: pointer;
}

.classes__order-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.classes__order-btn:not(:disabled):hover {
  background: #f3f4f6;
}

.classes__schedule {
  max-width: 320px;
  line-height: 1.4;
}

.classes__table tr:hover {
  background: #f9f9f9;
}
</style>
