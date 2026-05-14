<script setup lang="ts">
import axios from "axios"
import { ref, onMounted } from 'vue';
import BaseLayout from '../../layouts/BaseLayout.vue';

const attendanceLists = ref([]);

async function getAttendanceLists() {
  try {
    const { data } = await axios.get('/api/attendance-lists');
    attendanceLists.value = data;
  } catch (error) {
    console.error(error)
  }
}

function formatDate(date: string) {
  return date.split('T')[0].split('-').reverse().join('/')
}

onMounted(async () => {
  await getAttendanceLists();
})
</script>

<template>
  <BaseLayout :title="`Listas de Presença (${attendanceLists.length})`" action="Nova Lista" actionRoute="/admin/attendance-lists/create">
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
