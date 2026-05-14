<script setup>
import { computed, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const user = ref(null)

onMounted(() => {
  try {
    user.value = JSON.parse(localStorage.getItem('user') || 'null')
  } catch {
    user.value = null
  }
})

const userRole = computed(() => user.value?.role)

async function logout() {
  try {
    await axios.post('/api/auth/logout')
  } catch (error) {
    console.error(error)
  }

  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}
</script>

<template>
  <div class="sidebar">
    <img src="/public/logo-2.png" alt="" style="max-width: 150px;margin-bottom: 1rem">
    <h1 style="color: #FFF;font-size: 1.5rem">B-Triad Jiu-Jitsu</h1>
    <ul style="color: #FFF">
      <template v-if="userRole === 'student'">
        <li><RouterLink to="/student/dashboard">Início</RouterLink></li>
        <li><RouterLink to="/student/profile">Meu Perfil</RouterLink></li>
        <li><RouterLink to="/student/ranking">Ranking</RouterLink></li>
      </template>
      <template v-else-if="userRole">
        <li><RouterLink to="/admin/students">Alunos</RouterLink></li>
        <li><RouterLink to="/admin/ranking">Ranking</RouterLink></li>
        <li><RouterLink to="/admin/attendance-lists">Treinos</RouterLink></li>
        <li><RouterLink to="/admin/student-graduations">Graduações</RouterLink></li>
        <li><RouterLink to="/admin/classes">Turmas</RouterLink></li>
        <li><RouterLink to="/admin/users">Usuários</RouterLink></li>
      </template>
    </ul>
    <button @click="logout" class="logout-button">
      Sair
    </button>
  </div>
</template>

<style lang="scss" scoped>
.logout-button {
  background: #d32f2f;
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  width: 100%;
  margin-top: auto;
  flex-shrink: 0;
  margin-bottom: 10px;

  &:hover {
    background: #b71c1c;
  }
}
</style>