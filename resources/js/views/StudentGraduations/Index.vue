<script setup lang="ts">
import axios from "axios"
import { ref, onMounted } from 'vue';
import BaseLayout from '../../layouts/BaseLayout.vue';
import BeltBadge from "../../components/ui/Badge/BeltBadge.vue";

const studentGraduations = ref([]);

async function getStudentGraduations() {
  try {
    const { data } = await axios.get('/api/student-graduations');
    studentGraduations.value = data;
  } catch (error) {
    console.error(error)
  }
}

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('pt-BR')
}

onMounted(async () => {
  await getStudentGraduations();
})
</script>

<template>
  <BaseLayout :title="`Graduações (${studentGraduations.length})`" action="Nova Graduação" actionRoute="/admin/student-graduations/create">
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
            <td>{{ graduation.degree || '-' }}</td>
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
