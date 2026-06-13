<script setup lang="ts">
import axios from "axios"
import { ref, onMounted, computed, reactive } from 'vue';
import BaseLayout from '../../layouts/BaseLayout.vue';
import FormInput from "../../components/form/FormInput.vue";
import FormSelect from "../../components/form/FormSelect.vue";
import { useRoute, useRouter } from 'vue-router'
import { classOptionLabel } from '../../utils/classSchedule'
import { toastDanger, toastSuccess } from '../../utils/toast'
import { trainingDateKey } from '../../utils/studentDashboard'

const route = useRoute();
const router = useRouter();
const loading = ref(false)
const deleting = ref(false)
const students = ref([]);
const classes = ref([]);
const attendanceList = ref(null);
const searchFilter = ref("");

const form = reactive({
  class_date: "",
  class_id: null,
  photo: "",
  student_ids: []
})

const filteredStudents = computed(() => {
  let filtered = students.value;

  // Aplicar filtro de busca se houver
  if (searchFilter.value.trim()) {
    const query = searchFilter.value.toLowerCase();
    filtered = filtered.filter(student =>
      student.name.toLowerCase().includes(query)
    );
  }

  // Ordenar: alunos selecionados primeiro (ordem alfabética), depois não selecionados (ordem alfabética)
  return filtered.sort((a, b) => {
    const aSelected = form.student_ids.includes(a.id);
    const bSelected = form.student_ids.includes(b.id);

    if (aSelected && !bSelected) return -1;
    if (!aSelected && bSelected) return 1;

    // Dentro do mesmo grupo (selecionados ou não), ordenar alfabeticamente
    return a.name.localeCompare(b.name);
  });
})

const errors = ref({
  class_date: "",
  class_id: "",
  student_ids: ""
})

function validate() {
  const e: any = {}

  if (!form.class_date) e.class_date = "Data da aula é obrigatória"
  if (!form.class_id) e.class_id = "Selecione uma turma"
  if (!form.student_ids || form.student_ids.length === 0) e.student_ids = "Selecione pelo menos um aluno"

  errors.value = e
  return Object.keys(e).length === 0
}

function toggleStudent(studentId: number) {
  const index = form.student_ids.indexOf(studentId)
  if (index > -1) {
    form.student_ids.splice(index, 1)
  } else {
    form.student_ids.push(studentId)
  }
}

async function submit() {
  if (!validate()) return
  loading.value = true

  try {
    await axios.put(`/api/attendance-lists/${route.params.id}`, {
      class_date: form.class_date,
      class_id: form.class_id,
      photo: form.photo || null,
      student_ids: form.student_ids
    })

    toastSuccess('Lista de presença atualizada com sucesso!')
  } catch (e) {
    toastDanger('Erro ao atualizar lista de presença')
    console.log(e)
  }

  loading.value = false
}

async function removeAttendanceList() {
  const dateLabel = form.class_date
    ? form.class_date.split('-').reverse().join('/')
    : 'este treino'
  if (!confirm(`Excluir o treino de ${dateLabel}? Esta ação não pode ser desfeita.`)) {
    return
  }

  deleting.value = true
  try {
    await axios.delete(`/api/attendance-lists/${route.params.id}`)
    router.push('/admin/attendance-lists')
  } catch (e: any) {
    toastDanger(e.response?.data?.message || 'Erro ao excluir treino')
    console.error(e)
  } finally {
    deleting.value = false
  }
}

async function getStudents() {
  try {
    const { data } = await axios.get('/api/students', { params: { all: 1 } });
    students.value = data;
  } catch (error) {
    console.error(error)
  }
}

async function getClasses() {
  try {
    const { data } = await axios.get('/api/classes');
    classes.value = data.map((c) => ({
      value: c.id,
      label: classOptionLabel(c),
    }))
  } catch (error) {
    console.error(error)
  }
}

async function getAttendanceList() {
  try {
    const { data } = await axios.get(`/api/attendance-lists/${route.params.id}`);
    attendanceList.value = data;

    // Preencher formulário
    form.class_date = trainingDateKey(data.class_date) ?? '';
    form.class_id = data.class_id;
    form.photo = data.photo || "";
    form.student_ids = data.students?.map(student => student.id) || [];
  } catch (error) {
    console.error(error);
    toastDanger('Erro ao carregar lista de presença')
  }
}

onMounted(async () => {
  await getStudents();
  await getClasses();
  await getAttendanceList();
})
</script>

<template>
  <BaseLayout title="Editar Lista de Presença">
    <div class="mx-auto max-w-3xl">
      <form @submit.prevent="submit" class="space-y-6">
        <div class="space-y-4">
          <h2 class="text-xl font-bold">Informações da Aula</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <FormInput type="date" v-model="form.class_date" label="Data da aula" :error="errors.class_date" />

            <FormSelect v-model="form.class_id" :options="classes" label="Turma" placeholder="Selecione uma turma" :error="errors.class_id" />
          </div>

          <div>
            <FormInput
              v-model="form.photo"
              label="Foto"
              placeholder="URL da foto do treino"
            />
          </div>
        </div>

        <div class="space-y-4">
          <h2 class="text-xl font-bold">Selecione os Alunos</h2>

          <div v-if="errors.student_ids" class="text-red-500 text-sm">{{ errors.student_ids }}</div>

          <div>
            <FormInput v-model="searchFilter" label="Buscar aluno" placeholder="Digite o nome do aluno..." />
          </div>

          <div class="border border-gray-300 rounded-lg p-4 max-h-96 overflow-y-auto">
            <div v-if="filteredStudents.length === 0" class="text-center text-gray-500 py-8">
              {{ students.length === 0 ? 'Nenhum aluno cadastrado' : 'Nenhum aluno encontrado' }}
            </div>

            <div v-for="student in filteredStudents" :key="student.id" class="flex items-center gap-3 py-2 border-b last:border-b-0">
              <input
                :id="`student-${student.id}`"
                type="checkbox"
                :value="student.id"
                :checked="form.student_ids.includes(student.id)"
                @change="toggleStudent(student.id)"
                class="h-4 w-4 rounded border-gray-300"
              />
              <label :for="`student-${student.id}`" class="flex-1 cursor-pointer">{{ student.name }}</label>
            </div>
          </div>

          <div class="text-sm text-gray-600">
            {{ form.student_ids.length }} aluno(s) selecionado(s)
          </div>
        </div>

        <div class="form-actions">
          <router-link to="/admin/attendance-lists" class="btn-primary">Voltar</router-link>
          <div class="form-actions__right">
            <button
              type="button"
              class="btn-danger"
              :disabled="loading || deleting"
              @click="removeAttendanceList"
            >
              {{ deleting ? 'Excluindo…' : 'Excluir treino' }}
            </button>
            <button
              type="submit"
              :disabled="loading || deleting"
              class="btn-submit"
            >
              {{ loading ? 'Salvando...' : 'Atualizar Lista' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </BaseLayout>
</template>

<style scoped lang="scss">
.btn-primary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #e0e0e0;
  color: #333;
  padding: 10px 20px;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 500;
  cursor: pointer;

  &:hover {
    background: #d0d0d0;
  }
}

.form-actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 0.75rem;
}

.form-actions__right {
  display: flex;
  flex-wrap: wrap;
  gap: 0.65rem;
}

.btn-danger {
  padding: 0.65rem 1rem;
  border-radius: 6px;
  border: 1px solid #fecaca;
  background: #fef2f2;
  color: #b91c1c;
  font-weight: 600;
  cursor: pointer;

  &:disabled {
    opacity: 0.6;
    cursor: wait;
  }

  &:hover:not(:disabled) {
    background: #fee2e2;
  }
}

.btn-submit {
  background: #2563eb;
  color: #fff;
  padding: 0.65rem 1.5rem;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;

  &:disabled {
    opacity: 0.5;
    cursor: wait;
  }

  &:hover:not(:disabled) {
    background: #1d4ed8;
  }
}
</style>
