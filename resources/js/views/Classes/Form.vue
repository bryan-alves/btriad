<script setup lang="ts">
import axios from "axios"
import { ref, onMounted, reactive } from 'vue';
import BaseLayout from '../../layouts/BaseLayout.vue';
import FormInput from "../../components/form/FormInput.vue";
import FormSelect from "../../components/form/FormSelect.vue";
import { useRoute } from 'vue-router'

const route = useRoute();
const loading = ref(false);
const isEdit = ref(false);

const form = reactive({
  name: "",
  type: "kids",
  start_time: "",
  end_time: "",
  active: true
});

const errors = ref({
  name: "",
  type: "",
  start_time: ""
});

function validate() {
  const e: any = {}

  if (!form.name) e.name = "Nome da turma é obrigatório"
  if (!form.type) e.type = "Tipo da turma é obrigatório"
  if (!form.start_time) e.start_time = "Horário de início é obrigatório"

  errors.value = e
  return Object.keys(e).length === 0
}

async function submit() {
  if (!validate()) return
  loading.value = true

  try {
    if (isEdit.value) {
      await axios.put(`/api/classes/${route.params.id}`, {
        name: form.name,
        type: form.type,
        start_time: form.start_time,
        end_time: form.end_time || null,
        active: form.active
      })

      alert('Turma atualizada com sucesso!')
    } else {
      await axios.post('/api/classes', {
        name: form.name,
        type: form.type,
        start_time: form.start_time,
        end_time: form.end_time || null,
        active: form.active
      })

      alert('Turma cadastrada com sucesso!')
      form.name = ''
      form.type = 'kids'
      form.start_time = ''
      form.end_time = ''
      form.active = true
    }
  } catch (e) {
    alert(isEdit.value ? 'Erro ao atualizar turma' : 'Erro ao cadastrar turma')
    console.log(e)
  }

  loading.value = false
}

async function getClass() {
  try {
    const { data } = await axios.get(`/api/classes/${route.params.id}`)

    form.name = data.name
    form.type = data.type || 'kids'
    form.start_time = data.start_time
    form.end_time = data.end_time || ''
    form.active = data.active
  } catch (error) {
    console.error(error)
    alert('Erro ao carregar turma')
  }
}

onMounted(async () => {
  isEdit.value = !!route.params.id

  if (isEdit.value) {
    await getClass();
  }
})
</script>

<template>
  <BaseLayout :title="isEdit ? 'Editar Turma' : 'Nova Turma'">
    <div class="mx-auto max-w-3xl">
      <form @submit.prevent="submit" class="space-y-6">
        <div class="space-y-4">
          <h2 class="text-xl font-bold">Informações da Turma</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <FormInput v-model="form.name" label="Nome da turma" :error="errors.name" />
            <FormSelect v-model="form.type" :options="[
              { value: 'kids', label: 'Kids' },
              { value: 'adult', label: 'Adulto' }
            ]" label="Tipo da turma" placeholder="Selecione" :error="errors.type" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <FormInput type="time" v-model="form.start_time" label="Horário de início" :error="errors.start_time" />
            <FormInput type="time" v-model="form.end_time" label="Horário de término" />
          </div>

          <div class="flex items-center gap-3">
            <input id="active" type="checkbox" v-model="form.active" class="h-4 w-4 rounded border-gray-300" />
            <label for="active" class="font-medium">Turma ativa</label>
          </div>
        </div>

        <div style="display: flex; justify-content: space-between; gap: 10px;">
          <router-link to="/admin/classes" class="btn-primary">Voltar</router-link>
          <button :disabled="loading" class="bg-blue-600 text-white px-6 py-3 rounded font-semibold hover:bg-blue-700 disabled:opacity-50">
            {{ loading ? 'Salvando...' : isEdit ? 'Atualizar Turma' : 'Cadastrar Turma' }}
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
