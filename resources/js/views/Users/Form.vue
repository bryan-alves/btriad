<script setup lang="ts">
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import BaseLayout from '../../layouts/BaseLayout.vue'
import FormInput from '../../components/form/FormInput.vue'
import FormSelect from '../../components/form/FormSelect.vue'

const route = useRoute()
const router = useRouter()
const loading = ref(false)
const loadError = ref('')
const studentsLoadError = ref('')
const students = ref<{ id: number; name: string; user_id?: number | null }[]>([])
const loadedUserRole = ref<'student' | 'admin' | 'instructor'>('student')

const isEdit = computed(() => route.name === 'UsersEdit')
const userId = computed(() => {
  if (!isEdit.value) return null
  const raw = route.params.id as string | string[] | undefined
  const s = Array.isArray(raw) ? raw[0] : raw
  const n = Number(s)
  return Number.isFinite(n) ? n : null
})

const form = reactive({
  name: '',
  username: '',
  password: '',
  student_id: '' as string | number,
  active: true,
})

const errors = ref({
  name: '',
  username: '',
  password: '',
  student_id: '',
  active: '',
})

function resetForm() {
  form.name = ''
  form.username = ''
  form.password = ''
  form.student_id = ''
  form.active = true
  loadedUserRole.value = 'student'
  errors.value = { name: '', username: '', password: '', student_id: '', active: '' }
}

function isStudentLinked(s: { user_id?: number | null }, allowUserId?: number | null) {
  if (s.user_id == null || s.user_id === '') return false
  if (allowUserId != null && Number(s.user_id) === Number(allowUserId)) return false
  return true
}

const studentSelectOptions = computed(() => {
  const list = Array.isArray(students.value) ? students.value : []
  const allowUid = isEdit.value ? userId.value : null
  const opts: { value: string | number; label: string; disabled?: boolean }[] = [
    { value: '', label: 'Não vincular' },
  ]
  for (const s of list) {
    const linked = isStudentLinked(s, allowUid)
    opts.push({
      value: s.id,
      label: linked ? `${s.name} (já possui usuário)` : s.name,
      disabled: linked,
    })
  }
  return opts
})

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

async function loadUser(id: number) {
  loadError.value = ''
  try {
    const { data } = await axios.get(`/api/users/${id}`)
    form.name = data.name
    form.username = data.username
    form.password = ''
    form.active = data.active !== false
    loadedUserRole.value = data.role
    form.student_id = data.student?.id ?? ''
  } catch (e) {
    console.error(e)
    loadError.value = 'Não foi possível carregar o usuário.'
  }
}

watch(
  () => [route.name, route.params.id] as const,
  async ([name, id]) => {
    if (name === 'UsersEdit' && id != null) {
      await loadUser(Number(id))
    } else if (name === 'UsersCreate') {
      resetForm()
    }
  },
)

function validate() {
  const e: Record<string, string> = {}

  if (!form.name?.trim()) {
    e.name = 'Nome é obrigatório'
  }

  if (!form.username?.trim()) {
    e.username = 'Username é obrigatório'
  }

  if (!isEdit.value && !form.password) {
    e.password = 'Senha é obrigatória'
  }

  errors.value = { name: '', username: '', password: '', student_id: '', active: '', ...e }
  return Object.keys(e).length === 0
}

async function submit() {
  if (!validate()) {
    return
  }

  loading.value = true

  try {
    if (isEdit.value && userId.value != null) {
      const payload: Record<string, unknown> = {
        name: form.name,
        username: form.username,
        active: form.active,
      }
      if (form.password?.trim()) {
        payload.password = form.password
      }
      if (loadedUserRole.value === 'student') {
        payload.student_id = form.student_id === '' || form.student_id == null ? null : Number(form.student_id)
      }
      await axios.put(`/api/users/${userId.value}`, payload)
    } else {
      const payload: Record<string, unknown> = {
        name: form.name,
        username: form.username,
        password: form.password,
        role: 'student',
      }
      if (form.student_id !== '' && form.student_id != null) {
        payload.student_id = Number(form.student_id)
      }
      await axios.post('/api/users', payload)
    }
    router.push('/admin/users')
  } catch (error: any) {
    if (error.response?.data?.errors) {
      errors.value = {
        ...errors.value,
        ...Object.fromEntries(Object.entries(error.response.data.errors).map(([key, value]) => [key, Array.isArray(value) ? value[0] : value])),
      }
    } else {
      alert(isEdit.value ? 'Erro ao atualizar usuário' : 'Erro ao cadastrar usuário')
      console.error(error)
    }
  }

  loading.value = false
}

const pageTitle = computed(() => (isEdit.value ? 'Editar usuário' : 'Novo usuário'))

onMounted(() => {
  loadStudents().catch((err) => {
    console.error(err)
    studentsLoadError.value = 'Não foi possível carregar a lista de alunos.'
  })
  if (isEdit.value && userId.value != null) {
    loadUser(userId.value)
  }
})
</script>

<template>
  <BaseLayout :title="pageTitle">
    <div class="max-w-lg">
      <p v-if="loadError" class="text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2 mb-4">
        {{ loadError }}
      </p>

      <form @submit.prevent="submit" class="space-y-4">
        <FormInput v-model="form.name" label="Nome" placeholder="Nome completo" :error="errors.name" />
        <FormInput v-model="form.username" label="Username" placeholder="username" :error="errors.username" />
        <FormInput
          type="password"
          v-model="form.password"
          :label="isEdit ? 'Nova senha (opcional)' : 'Senha'"
          placeholder="••••••••"
          :error="errors.password"
        />

        <div v-if="isEdit" class="flex flex-col gap-1">
          <span class="text-sm font-medium text-gray-700">Perfil</span>
          <span class="text-sm text-gray-600 capitalize">{{ loadedUserRole }}</span>
          <p class="text-xs text-gray-500">Novos logins só podem ser criados como aluno. O perfil não é alterado aqui.</p>
        </div>
        <div v-else class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
          <strong>Perfil:</strong> Aluno (único tipo disponível para novos usuários).
        </div>

        <template v-if="!isEdit || loadedUserRole === 'student'">
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
            Só é possível escolher alunos ainda sem usuário. O aluno ligado a esta conta aparece habilitado na lista.
          </p>
        </template>

        <label v-if="isEdit" class="flex items-center gap-2 text-sm text-gray-800">
          <input v-model="form.active" type="checkbox" class="rounded border-gray-300" />
          Conta ativa (pode entrar no sistema)
        </label>
        <p v-if="errors.active" class="text-sm text-red-600">{{ errors.active }}</p>

        <div class="flex gap-3">
          <button type="submit" class="btn-primary" :disabled="loading">
            {{ loading ? 'Salvando...' : isEdit ? 'Salvar alterações' : 'Cadastrar usuário' }}
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
