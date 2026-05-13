<script setup lang="ts">
import axios from 'axios'
import { ref, onMounted, computed } from 'vue'
import BaseLayout from '../../layouts/BaseLayout.vue'

type UserRow = {
  id: number
  name: string
  username: string
  role: string
  active?: boolean
  student?: { name: string } | null
}

const users = ref<UserRow[]>([])
const busyId = ref<number | null>(null)

const currentUserId = computed(() => {
  try {
    const u = JSON.parse(localStorage.getItem('user') || 'null')
    return u?.id != null ? Number(u.id) : null
  } catch {
    return null
  }
})

async function getUsers() {
  try {
    const { data } = await axios.get('/api/users')
    users.value = Array.isArray(data) ? data : []
  } catch (error) {
    console.error(error)
  }
}

async function toggleActive(user: UserRow) {
  if (user.id === currentUserId.value && user.active !== false) {
    alert('Não é possível desativar a sua própria conta.')
    return
  }
  busyId.value = user.id
  try {
    await axios.put(`/api/users/${user.id}`, { active: user.active === false })
    await getUsers()
  } catch (error: any) {
    const msg = error.response?.data?.message || error.response?.data?.errors?.active?.[0]
    alert(msg || 'Erro ao alterar estado do usuário')
    console.error(error)
  } finally {
    busyId.value = null
  }
}

onMounted(async () => {
  await getUsers()
})
</script>

<template>
  <BaseLayout :title="`Usuários (${users.length})`" action="Novo usuário" actionRoute="/admin/users/create">
    <div class="users">
      <div class="table-scroll">
        <table class="users__table">
          <thead>
            <tr>
              <th>Nome</th>
              <th>Username</th>
              <th>Perfil</th>
              <th>Aluno vinculado</th>
              <th>Ativo</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody v-if="users.length">
            <tr v-for="user in users" :key="user.id">
              <td>{{ user.name }}</td>
              <td>{{ user.username }}</td>
              <td>{{ user.role }}</td>
              <td>{{ user.student?.name ?? '—' }}</td>
              <td>{{ user.active === false ? 'Não' : 'Sim' }}</td>
              <td class="users__actions">
                <RouterLink :to="`/admin/users/${user.id}/edit`" class="users__link">Editar</RouterLink>
                <button
                  type="button"
                  class="users__btn"
                  :class="user.active === false ? 'users__btn--primary' : 'users__btn--danger'"
                  :disabled="busyId === user.id || (user.id === currentUserId && user.active !== false)"
                  @click="toggleActive(user)"
                >
                  {{ busyId === user.id ? '…' : user.active === false ? 'Reativar' : 'Desativar' }}
                </button>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="6" style="text-align: center">Nenhum usuário cadastrado.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </BaseLayout>
</template>

<style lang="scss" scoped>
.users__table {
  border-collapse: collapse;
  background: white;
}

.users__table th,
.users__table td {
  padding: 12px;
  border-bottom: 1px solid #eee;
  text-align: left;
  vertical-align: middle;
}

.users__table tr:hover {
  background: #f9f9f9;
}

.users__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
}

.users__link {
  font-size: 0.875rem;
  color: #2563eb;
  text-decoration: none;
  font-weight: 500;

  &:hover {
    text-decoration: underline;
  }
}

.users__btn {
  font-size: 0.8125rem;
  padding: 6px 10px;
  border-radius: 6px;
  border: 1px solid #ddd;
  cursor: pointer;
  background: #fff;

  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
}

.users__btn--danger {
  border-color: #f87171;
  color: #b91c1c;
}

.users__btn--primary {
  border-color: #86efac;
  color: #166534;
}
</style>
