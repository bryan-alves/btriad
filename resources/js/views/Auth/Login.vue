<script setup lang="ts">
import axios from "axios"
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router'
import FormInput from "../../components/form/FormInput.vue";
import { getAcademyName, getLogoUrl } from '../../utils/publicTenant'
import { toastDanger } from '../../utils/toast'

const academyName = getAcademyName()
const logoUrl = getLogoUrl()

const router = useRouter();
const loading = ref(false);

const form = reactive({
  username: "",
  password: ""
});

const errors = ref({
  username: "",
  password: ""
});

function validate() {
  const e: any = {}

  if (!form.username) e.username = "Username é obrigatório"
  if (!form.password) e.password = "Senha é obrigatória"

  errors.value = e
  return Object.keys(e).length === 0
}

async function submit() {
  if (!validate()) return
  loading.value = true

  try {
    const { data } = await axios.post('/api/auth/login', {
      username: form.username,
      password: form.password
    })

    localStorage.setItem('token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))
    axios.defaults.headers.common['Authorization'] = `Bearer ${data.token}`

    const redirectTo = data.user?.role === 'student'
      ? '/student/dashboard'
      : '/admin/students'

    router.push(redirectTo)
  } catch (e: any) {
    if (e.response?.data?.username) {
      errors.value.username = e.response.data.username[0]
    } else {
      toastDanger('Erro ao fazer login')
    }
    console.log(e)
  }

  loading.value = false
}
</script>

<template>
  <div class="login-container">
    <div class="login-box">
      <div class="login-brand">
        <img
          v-if="logoUrl"
          :src="logoUrl"
          :alt="`Logo ${academyName}`"
          class="login-logo"
        >
        <h1 class="login-title">{{ academyName }}</h1>
        <p class="login-subtitle">Área Administrativa</p>
      </div>

      <form @submit.prevent="submit" class="login-form">
        <FormInput 
          type="text" 
          v-model="form.username" 
          label="Username" 
          placeholder="Seu username"
          :error="errors.username" 
        />

        <FormInput 
          type="password" 
          v-model="form.password" 
          label="Senha" 
          placeholder="Sua senha"
          :error="errors.password" 
        />

        <button 
          type="submit" 
          :disabled="loading" 
          class="login-button"
        >
          {{ loading ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>
    </div>
  </div>
</template>

<style scoped lang="scss">
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, var(--app-header-color, #1b1b18) 0%, var(--app-login-background-color, #333) 100%);
}

.login-box {
  background: white;
  padding: 40px;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  width: 100%;
  max-width: 400px;
}

.login-brand {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 20px;
}

.login-logo {
  display: block;
  max-width: 160px;
  max-height: 120px;
  object-fit: contain;
  
}

.login-title {
  font-size: 28px;
  font-weight: bold;
  color: #1b1b18;
  margin-bottom: 8px;
  text-align: center;
}

.login-subtitle {
  color: #666;
  text-align: center;
  margin-bottom: 0;
  font-size: 14px;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.login-button {
  background: var(--app-primary-color, #1b1b18);
  color: white;
  padding: 12px;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  font-size: 16px;
  transition: background 0.3s;

  &:hover:not(:disabled) {
    filter: brightness(0.92);
  }

  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
}
</style>
