<script setup lang="ts">
import axios from 'axios'
import { ref, onMounted, reactive, computed } from 'vue'
import BaseLayout from '../../layouts/BaseLayout.vue'
import FormInput from '../../components/form/FormInput.vue'
import FormSelect from '../../components/form/FormSelect.vue'
import { graduationPhotoUrl } from '../../utils/graduation'
import { useRoute } from 'vue-router'

const route = useRoute()
const loading = ref(false)
const students = ref([])
const belts = ref([])
const graduation = ref<any>(null)
const photoFile = ref<File | null>(null)

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
})

const currentPhotoUrl = computed(() => graduationPhotoUrl(graduation.value))

const errors = ref({
  student_id: '',
  belt_id: '',
  graduated_at: '',
  degree: '',
  photo: '',
})

function onPhotoChange(event: Event) {
  const input = event.target as HTMLInputElement
  photoFile.value = input.files?.[0] ?? null
}

function validate() {
  const e: Record<string, string> = {}

  if (!form.student_id) e.student_id = 'Selecione um aluno'
  if (!form.belt_id) e.belt_id = 'Selecione uma faixa'
  if (!form.graduated_at) e.graduated_at = 'Data de graduação é obrigatória'

  errors.value = { student_id: '', belt_id: '', graduated_at: '', degree: '', photo: '', ...e }
  return Object.keys(e).length === 0
}

function buildFormData() {
  const fd = new FormData()
  fd.append('student_id', String(form.student_id))
  fd.append('belt_id', String(form.belt_id))
  fd.append('degree', String(Math.min(4, Math.max(0, parseInt(String(form.degree), 10) || 0))))
  fd.append('graduated_at', form.graduated_at)
  if (photoFile.value) {
    fd.append('photo', photoFile.value)
  }
  return fd
}

async function submit() {
  if (!validate()) return
  loading.value = true

  try {
    await axios.put(`/api/student-graduations/${route.params.id}`, buildFormData(), {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    alert('Graduação atualizada com sucesso!')
    await getGraduation()
    photoFile.value = null
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
    alert('Erro ao atualizar graduação')
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
    graduation.value = data

    form.student_id = data.student_id
    form.belt_id = data.belt_id
    const d = Number(data.degree)
    const clamped = Number.isFinite(d) ? Math.min(4, Math.max(0, d)) : 0
    form.degree = String(clamped)
    form.graduated_at = String(data.graduated_at ?? '').split('T')[0]
  } catch (error) {
    console.error(error)
    alert('Erro ao carregar graduação')
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

          <div>
            <label for="grad-photo" class="font-medium">Foto</label>
            <p v-if="currentPhotoUrl" class="text-sm mt-1 mb-2">
              Foto atual:
              <a :href="currentPhotoUrl" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline">
                Ver foto
              </a>
            </p>
            <input
              id="grad-photo"
              type="file"
              accept="image/jpeg,image/png,image/webp,image/gif"
              class="w-full mt-1 text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded file:border-0 file:bg-gray-100 file:font-medium"
              @change="onPhotoChange"
            />
            <p v-if="errors.photo" class="text-red-500 text-sm mt-1">{{ errors.photo }}</p>
            <p class="text-sm text-gray-500 mt-1">
              Opcional. Envie uma nova imagem para substituir a foto atual.
            </p>
          </div>
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
