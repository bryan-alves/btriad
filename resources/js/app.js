import { createApp } from 'vue'
import App from './App.vue'
import router from './router';
import axios from 'axios'
import { applyTenantTheme } from './utils/publicTenant'
import { registerAdminPwa } from './pwa/registerAdminPwa'

const app = createApp(App)

applyTenantTheme()
registerAdminPwa()

// Configurar axios com token do localStorage
const token = localStorage.getItem('token')
if (token) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
}

// Interceptor para adicionar token em todas as requisições
axios.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
}, error => {
  return Promise.reject(error)
})

// Interceptor para logout se token expirou
axios.interceptors.response.use(response => {
  return response
}, error => {
  if (error.response?.status === 401) {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    router.push('/login')
  }
  return Promise.reject(error)
})

app.use(router).mount('#app')
