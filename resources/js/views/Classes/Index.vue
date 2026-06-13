<script setup lang="ts">
import axios from 'axios'
import { ref, onMounted } from 'vue'
import BaseLayout from '../../layouts/BaseLayout.vue'
import { summarizeScheduleSlots, type SchoolClassRow } from '../../utils/classSchedule'

const classes = ref<SchoolClassRow[]>([])

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
            <th>Nome</th>
            <th>Tipo</th>
            <th>Horários</th>
            <th>Ativa</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody v-if="classes.length">
          <tr v-for="classItem in classes" :key="classItem.id">
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
            <td colspan="5" style="text-align: center">Nenhuma turma cadastrada!</td>
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

.classes__schedule {
  max-width: 320px;
  line-height: 1.4;
}

.classes__table tr:hover {
  background: #f9f9f9;
}
</style>
