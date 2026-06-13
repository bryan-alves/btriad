<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { getAcademyName, getLogoUrl } from '../../utils/publicTenant'

const router = useRouter()
const user = ref(null)
const academyName = ref(getAcademyName())
const logoUrl = ref(getLogoUrl())

onMounted(() => {
  try {
    user.value = JSON.parse(localStorage.getItem('user') || 'null')
    console.log(user.value)
  } catch {
    user.value = null
  }
})

function refreshTenantBrand() {
  academyName.value = getAcademyName()
  logoUrl.value = getLogoUrl()
}

onMounted(() => {
  window.addEventListener('app-tenant-updated', refreshTenantBrand)
})

onUnmounted(() => {
  window.removeEventListener('app-tenant-updated', refreshTenantBrand)
})

const userRole = computed(() => user.value?.role)

function isTruthyPermission(value) {
  return value === true || value === 1 || value === '1' || value === 'true'
}

const canManageSites = computed(() => (
  isTruthyPermission(user.value?.can_manage_sites)
  || isTruthyPermission(user.value?.canManageSites)
))

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
    <img v-if="logoUrl" :src="logoUrl" :alt="academyName" class="sidebar__logo">
    <ul style="color: #FFF">
      <template v-if="userRole === 'student'">
        <li><RouterLink to="/student/dashboard">Início</RouterLink></li>
        <li><RouterLink to="/student/profile">Meu Perfil</RouterLink></li>
        <li><RouterLink to="/student/profile?tab=site-review">Avaliação</RouterLink></li>
        <li><RouterLink to="/student/ranking">Ranking</RouterLink></li>
      </template>
      <template v-else-if="userRole">
        <li><RouterLink to="/admin/students">Alunos</RouterLink></li>
        <li><RouterLink to="/admin/ranking">Ranking</RouterLink></li>
        <li><RouterLink to="/admin/attendance-lists">Treinos</RouterLink></li>
        <li><RouterLink to="/admin/student-graduations">Graduações</RouterLink></li>
        <li><RouterLink to="/admin/classes">Turmas</RouterLink></li>
        <li><RouterLink to="/admin/users">Usuários</RouterLink></li>
        <li v-if="canManageSites"><RouterLink to="/admin/site-settings">Sites</RouterLink></li>
      </template>
    </ul>
    <button @click="logout" class="logout-button">
      Sair
    </button>
  </div>
</template>

<style lang="scss" scoped>
.sidebar__logo {
  max-width: 150px;
  max-height: 120px;
  object-fit: contain;
  margin-bottom: 1rem;
}

.sidebar__title {
  color: #fff;
  font-size: 1.5rem;
  text-align: center;
  margin: 0 0 1rem;
  flex-shrink: 0;
}

.sidebar ul {
  flex: 1;
  min-height: 0;
  overflow-y: auto;
  margin: 0;
}

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