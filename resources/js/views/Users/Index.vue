<script setup lang="ts">
import axios from "axios"
import { ref, onMounted } from 'vue'
import BaseLayout from "../../layouts/BaseLayout.vue"

const users = ref([])

async function getUsers() {
  try {
    const { data } = await axios.get('/api/users')
    users.value = data
  } catch (error) {
    console.error(error)
  }
}

onMounted(async () => {

  console.log(localStorage.getItem('user'))
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
          </tr>
        </thead>
        <tbody v-if="users.length">
          <tr v-for="user in users" :key="user.id">
            <td>{{ user.name }}</td>
            <td>{{ user.username }}</td>
            <td>{{ user.role }}</td>
            <td>{{ user.student?.name ?? '-' }}</td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="4" style="text-align: center">Nenhum usuário cadastrado.</td>
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
}

.users__table tr:hover {
  background: #f9f9f9;
}
</style>
