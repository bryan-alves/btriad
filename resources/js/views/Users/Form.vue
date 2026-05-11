<script setup lang="ts">
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import BaseLayout from '../../layouts/BaseLayout.vue'
import FormInput from '../../components/form/FormInput.vue'
import FormSelect from '../../components/form/FormSelect.vue'

const router = useRouter()
const loading = ref(false)
const studentsLoadError = ref('')
const students = ref<{ id: number; name: string; user_id?: number | null }[]>([])

const form = reactive({
  name: '',
  username: '',
  password: '',
  role: 'student',
  student_id: '' as string | number,
})

const errors = ref({
  name: '',
  username: '',
  password: '',
  role: '',
  student_id: '',
})

function isStudentLinked(s: { user_id?: number | null }) {
  return s.user_id != null && s.user_id !== ''
}

const studentSelectOptions = computed(() => {
  const list = Array.isArray(students.value) ? students.value : []
  const opts: { value: string | number; label: string; disabled?: boolean }[] = [
    { value: '', label: 'Não vincular' },
  ]
  for (const s of list) {
    const linked = isStudentLinked(s)
    opts.push({
      value: s.id,
      label: linked ? `${s.name} (já possui usuário)` : s.name,
      disabled: linked,
    })
  }
  return opts
})

watch(
  () => form.role,
  (role) => {
    if (role !== 'student') {
      form.student_id = ''
    }
  },
)

async function loadStudents() {
  studentsLoadError.value = ''
  const { data } = await axios.get('/api/students', {
    params: { for_user_link: 1 },
  })
  students.value = Array.isArray(data) ? data : []
  if (!students.value.length) {
    studentsLoadError.value = 'Nenhum cadastro de aluno encontrado.'
  }
}

function validate() {
  const e: any = {}

  if (!form.name?.trim()) {
    e.name = 'Nome é obrigatório'
  }

  if (!form.username?.trim()) {
    e.username = 'Username é obrigatório'
  }

  if (!form.password) {
    e.password = 'Senha é obrigatória'
  }

  if (!form.role) {
    e.role = 'Perfil é obrigatório'
  }

  errors.value = e
  return Object.keys(e).length === 0
}

async function submit() {
  if (!validate()) {
    return
  }

  loading.value = true

  try {
    const payload: Record<string, unknown> = {
      name: form.name,
      username: form.username,
      password: form.password,
      role: form.role,
    }
    if (form.role === 'student' && form.student_id !== '' && form.student_id != null) {
      payload.student_id = Number(form.student_id)
    }

    await axios.post('/api/users', payload)
    router.push('/admin/users')
  } catch (error: any) {
    if (error.response?.data?.errors) {
      errors.value = {
        ...errors.value,
        ...Object.fromEntries(Object.entries(error.response.data.errors).map(([key, value]) => [key, value[0]])),
      }
    } else {
      alert('Erro ao cadastrar usuário')
      console.error(error)
    }
  }

  loading.value = false
}

onMounted(() => {
  loadStudents().catch((err) => {
    console.error(err)
    studentsLoadError.value = 'Não foi possível carregar a lista de alunos.'
  })
})
</script>

<template>
  <BaseLayout title="Novo usuário">
    <div class="max-w-lg">
      <form @submit.prevent="submit" class="space-y-4">
        <FormInput v-model="form.name" label="Nome" placeholder="Nome completo" :error="errors.name" />
        <FormInput v-model="form.username" label="Username" placeholder="username" :error="errors.username" />
        <FormInput type="password" v-model="form.password" label="Senha" placeholder="Senha" :error="errors.password" />
        <FormSelect v-model="form.role" :options="[
          { value: 'admin', label: 'Admin' },
          { value: 'instructor', label: 'Instrutor' },
          { value: 'student', label: 'Aluno' },
        ]" label="Perfil" placeholder="Selecione" :error="errors.role" />

        <template v-if="form.role === 'student'">
          <FormSelect
            v-model="form.student_id"
            :options="studentSelectOptions"
            label="Vincular a cadastro de aluno"
            placeholder="Opcional"
            :error="errors.student_id"
          />
          <p v-if="studentsLoadError" class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
            {{ studentsLoadError }}
          </p>
          <p v-else class="text-sm text-gray-600">
            Só é possível escolher alunos ainda sem usuário. Os demais aparecem desabilitados.
          </p>
        </template>

        <div class="flex gap-3">
          <button class="btn-primary" :disabled="loading">
            {{ loading ? 'Salvando...' : 'Cadastrar usuário' }}
          </button>
          <router-link to="/admin/users" class="btn-secondary">Cancelar</router-link>
        </div>
      </form>
    </div>
  </BaseLayout>
</template>

<style scoped lang="scss">
.btn-secondary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 14px;
  border-radius: 6px;
  border: 1px solid #ccc;
  color: #333;
  text-decoration: none;
}
</style>
