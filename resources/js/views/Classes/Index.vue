<script setup lang="ts">
import axios from "axios"
import { ref, onMounted } from 'vue';
import BaseLayout from '../../layouts/BaseLayout.vue';

const classes = ref([]);

async function getClasses() {
  try {
    const { data } = await axios.get('/api/classes');
    classes.value = data;
  } catch (error) {
    console.error(error)
  }
}

function formatTime(time: string | null) {
  return time ? time : '-';
}

onMounted(async () => {
  await getClasses();
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
            <th>Início</th>
            <th>Fim</th>
            <th>Ativa</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody v-if="classes.length">
          <tr v-for="classItem in classes" :key="classItem.id">
            <td>{{ classItem.name }}</td>
            <td>{{ classItem.type === 'kids' ? 'Kids' : classItem.type === 'adult' ? 'Adulto' : '—' }}</td>
            <td>{{ formatTime(classItem.start_time) }}</td>
            <td>{{ formatTime(classItem.end_time) }}</td>
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

.classes__table tr:hover {
  background: #f9f9f9;
}
</style>
