<script setup lang="ts">
import axios from "axios"
import { ref, onMounted } from 'vue';
import BaseLayout from '../../layouts/BaseLayout.vue';

const students = ref([]);

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
  <BaseLayout title="Alunos" action="Adicionar aluno">
    <div class="students">
      <table class="students__table">
        <thead>
          <tr>
            <th>Nome</th>
            <th>Idade</th>
            <th>Graduação</th>
            <th>Atestado médico</th>
            <th>Ficha de cadastro</th>
            <th>Autorização de imagem</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody v-if="students.length">
          <tr v-for="student in students">
            <td>{{ student.name }}</td>
            <td>{{ student.birth_date ? new Date(student.birth_date).toLocaleDateString() : '-' }}</td>
            <td><span :class="`belt belt-${student.belt?.slug}`">{{ student.belt?.name }}</span></td>
            <td>{{ student.medical_certificate ? 'Sim' : 'Não' }}</td>
            <td>{{ student.registration_form ? 'Sim' : 'Não' }}</td>
            <td>{{ student.image_authorization ? 'Sim' : 'Não' }}</td>
            <td>
              <button class="btn-details">Detalhes</button>
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
.belt {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 12px;
  color: white;
}

.belt-white {
  background: #e5e5e5;
  color: #333;
  border: 1px solid #ccc;
}

.belt-blue {
  background: #1e40af;
}

.belt-purple {
  background: #6b21a8;
}

.belt-brown {
  background: #78350f;
}

.belt-black {
  background: black;
}

/* ============================= */
/* FAIXAS INFANTIS               */
/* ============================= */

/* CINZA */

/* base */

.belt {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 12px;
  color: white;
  border-left: 6px solid transparent;
}

/* branca */

.belt-white {
  background: #e5e5e5;
  color: #333;
}

/* cinza */

.belt-gray {
  background: #6b7280;
}

.belt-gray-white {
  background: #6b7280;
  border-left: 6px solid white;
}

.belt-gray-black {
  background: #6b7280;
  border-left: 6px solid black;
}

/* amarela */

.belt-yellow {
  background: #facc15;
  color: black;
}

.belt-yellow-white {
  background: #facc15;
  border-left: 6px solid white;
  color: black;
}

.belt-yellow-black {
  background: #facc15;
  border-left: 6px solid black;
  color: black;
}

/* laranja */

.belt-orange {
  background: #f97316;
}

.belt-orange-white {
  background: #f97316;
  border-left: 6px solid white;
}

.belt-orange-black {
  background: #f97316;
  border-left: 6px solid black;
}

/* verde */

.belt-green {
  background: #16a34a;
}

.belt-green-white {
  background: #16a34a;
  border-left: 6px solid white;
}

.belt-green-black {
  background: #16a34a;
  border-left: 6px solid black;
}

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
