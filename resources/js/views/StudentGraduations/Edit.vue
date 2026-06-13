<script setup lang="ts">
import axios from 'axios'
import { ref, onMounted, reactive } from 'vue'
import BaseLayout from '../../layouts/BaseLayout.vue'
import FormInput from '../../components/form/FormInput.vue'
import FormSelect from '../../components/form/FormSelect.vue'
import { useRoute } from 'vue-router'
import { toastDanger, toastSuccess } from '../../utils/toast'

const route = useRoute()
const loading = ref(false)
const students = ref([])
const belts = ref([])

const degreeOptions = [
  { value: '0', label: '0 — sem grau' },
  { value: '1', label: '1' },
  { value: '2', label: '2' },
  { value: '3', label: '3' },
  { value: '4', label: '4' },
]

const form = reactive({
  student_id: null,
  belt_id: null,
  degree: '0',
  graduated_at: '',
  photo: '',
})

const errors = ref({
  student_id: '',
  belt_id: '',
  graduated_at: '',
  degree: '',
  photo: '',
})

function validate() {
  const e: Record<string, string> = {}

  if (!form.student_id) e.student_id = 'Selecione um aluno'
  if (!form.belt_id) e.belt_id = 'Selecione uma faixa'
  if (!form.graduated_at) e.graduated_at = 'Data de graduação é obrigatória'

  errors.value = { student_id: '', belt_id: '', graduated_at: '', degree: '', photo: '', ...e }
  return Object.keys(e).length === 0
}

async function submit() {
  if (!validate()) return
  loading.value = true

  try {
    await axios.put(`/api/student-graduations/${route.params.id}`, {
      student_id: form.student_id,
      belt_id: form.belt_id,
      degree: Math.min(4, Math.max(0, parseInt(String(form.degree), 10) || 0)),
      graduated_at: form.graduated_at,
      photo: form.photo.trim() || null,
    })

    toastSuccess('Graduação atualizada com sucesso!')
  } catch (e: any) {
    const err = e?.response?.data?.errors
    if (err) {
      errors.value = {
        ...errors.value,
        ...Object.fromEntries(
          Object.entries(err).map(([k, v]) => [k, Array.isArray(v) ? v[0] : String(v)]),
        ),
      }
    }
    toastDanger('Erro ao atualizar graduação')
    console.log(e)
  }

  loading.value = false
}

async function getStudents() {
  try {
    const { data } = await axios.get('/api/students', { params: { all: 1 } })
    students.value = data.map((student: { name: string; id: number }) => ({
      label: student.name,
      value: student.id,
    }))
  } catch (error) {
    console.error(error)
  }
}

async function getBelts() {
  try {
    const { data } = await axios.get('/api/belts')
    belts.value = data.map((belt: { name: string; group: string; id: number }) => ({
      label: `${belt.name} - ${belt.group}`,
      value: belt.id,
    }))
  } catch (error) {
    console.error(error)
  }
}

async function getGraduation() {
  try {
    const { data } = await axios.get(`/api/student-graduations/${route.params.id}`)

    form.student_id = data.student_id
    form.belt_id = data.belt_id
    const d = Number(data.degree)
    const clamped = Number.isFinite(d) ? Math.min(4, Math.max(0, d)) : 0
    form.degree = String(clamped)
    form.graduated_at = String(data.graduated_at ?? '').split('T')[0]
    form.photo = data.photo || ''
  } catch (error) {
    console.error(error)
    toastDanger('Erro ao carregar graduação')
  }
}

onMounted(async () => {
  await getStudents()
  await getBelts()
  await getGraduation()
})
</script>

<template>
  <BaseLayout title="Editar Graduação">
    <div class="mx-auto max-w-3xl">
      <form @submit.prevent="submit" class="space-y-6">
        <div class="space-y-4">
          <h2 class="text-xl font-bold">Informações da Graduação</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <FormSelect
              v-model="form.student_id"
              :options="students"
              label="Aluno"
              placeholder="Selecione um aluno"
              :error="errors.student_id"
            />

            <FormSelect
              v-model="form.belt_id"
              :options="belts"
              label="Faixa"
              placeholder="Selecione uma faixa"
              :error="errors.belt_id"
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <FormInput
              type="date"
              v-model="form.graduated_at"
              label="Data de Graduação"
              :error="errors.graduated_at"
            />

            <FormSelect
              v-model="form.degree"
              :options="degreeOptions"
              label="Grau"
              placeholder="Grau"
              :error="errors.degree"
            />
          </div>

          <FormInput
            v-model="form.photo"
            type="url"
            label="Foto"
            placeholder="https://..."
            :error="errors.photo"
          />
        </div>

        <div style="display: flex; justify-content: space-between; gap: 10px">
          <router-link to="/admin/student-graduations" class="btn-primary">Voltar</router-link>
          <button
            :disabled="loading"
            class="bg-blue-600 text-white px-6 py-3 rounded font-semibold hover:bg-blue-700 disabled:opacity-50"
          >
            {{ loading ? 'Salvando...' : 'Atualizar Graduação' }}
          </button>
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
</style>
