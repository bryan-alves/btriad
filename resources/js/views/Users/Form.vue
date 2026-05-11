<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import BaseLayout from '../../layouts/BaseLayout.vue'
import FormInput from '../../components/form/FormInput.vue'
import FormSelect from '../../components/form/FormSelect.vue'

const router = useRouter()
const loading = ref(false)

const form = reactive({
  name: '',
  username: '',
  password: '',
  role: 'student',
})

const errors = ref({
  name: '',
  username: '',
  password: '',
  role: '',
})

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
  } else if (form.password.length < 8) {
    e.password = 'Senha deve ter no mínimo 8 caracteres'
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
    await axios.post('/api/users', form)
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
