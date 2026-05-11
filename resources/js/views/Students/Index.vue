<script setup lang="ts">
import axios from "axios"
import { ref, onMounted } from 'vue';
import BaseLayout from '../../layouts/BaseLayout.vue';
import BeltBadge from "../../components/ui/Badge/BeltBadge.vue";

const students = ref([]);

function calculateAge(birthDate: string | null | undefined) {
  if (!birthDate) {
    return '-';
  }

  const date = new Date(birthDate);

  if (Number.isNaN(date.getTime())) {
    return '-';
  }

  const today = new Date();
  let age = today.getFullYear() - date.getFullYear();
  const monthDiff = today.getMonth() - date.getMonth();

  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < date.getDate())) {
    age--;
  }

  return age;
}

async function getStudents() {
  try {
    const { data } = await axios.get('/api/students');

    students.value = data;
  } catch (error) {
    console.error(error)
  }
}


onMounted(async () => {
  await getStudents();
})
</script>

<template>
  <BaseLayout :title="`Alunos (${students.length})`" action="Adicionar aluno" actionRoute="/admin/students/create">
    <div class="students">
      <table class="students__table">
        <thead>
          <tr>
            <th>Nome</th>
            <th>Idade</th>
            <th>Graduação</th>
            <th>Ficha de cadastro</th>
            <th>Atestado médico</th>
            <th>Autorização de imagem</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody v-if="students.length">
          <tr v-for="student in students">
            <td>{{ student.name }}</td>
            <td>{{ calculateAge(student.birth_date) }}</td>
            <td><BeltBadge :belt="student.belt" /></td>
            <td>{{ student.registration_form ? 'Sim' : 'Não' }}</td>
            <td>{{ student.medical_certificate ? 'Sim' : 'Não' }}</td>
            <td>{{ student.image_authorization ? 'Sim' : 'Não' }}</td>
            <td>
              <RouterLink :to="`/admin/students/${student.id}`">Detalhes</RouterLink>
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="7" style="text-align: center">Não há alunos cadastrados!</td>
          </tr>
        </tbody>
      </table>
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
  width: 100%;
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

.students__table tr:hover {
  background: #f9f9f9;
}

.btn-details {
  padding: 6px 10px;
  border: 1px solid #ddd;
  background: white;
  border-radius: 6px;
  cursor: pointer;
}
</style>
