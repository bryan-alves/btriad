<script setup lang="ts">
import BaseLayout from '../../layouts/BaseLayout.vue'
import { useRouter } from 'vue-router'
import { onMounted, reactive, ref, computed } from 'vue'
import axios from 'axios'
import FormInput from '../../components/form/FormInput.vue'
import FormSelect from '../../components/form/FormSelect.vue'

const router = useRouter()
const loading = ref(false)

const form = reactive({
  belt_id: null,
  photo: null as File | null,
  name: '',
  cpf: '',
  birth_date: '',
  sex: '',
  class_type: null,
  user_id: '' as string | number,
  address: {
    cep: '',
    street: '',
    number: '',
    complement: '',
    neighborhood: '',
    city: '',
  },
  emergency_contacts: [
    { name: '', relationship: '', phone: '' },
    { name: '', relationship: '', phone: '' },
  ],
  other_sports: '',
  health_issues: '',
  medical_certificate: null as File | null,
  registration_form_file: null as File | null,
})

function onCreateMedicalCert(e: Event) {
  const input = e.target as HTMLInputElement
  form.medical_certificate = input.files?.[0] ?? null
}

function onCreateRegistrationFile(e: Event) {
  const input = e.target as HTMLInputElement
  form.registration_form_file = input.files?.[0] ?? null
}

const belts = ref<{ label: string; value: number }[]>([])
const users = ref<{ label: string; value: number }[]>([])

const savedPhotoUrl = ref<string | null>(null)
const localPhotoPreview = ref<string | null>(null)
const displayPhotoUrl = computed(() => localPhotoPreview.value || savedPhotoUrl.value)

function onPhotoFileChange(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0] ?? null
  if (localPhotoPreview.value) {
    URL.revokeObjectURL(localPhotoPreview.value)
    localPhotoPreview.value = null
  }
  form.photo = file
  if (file) {
    localPhotoPreview.value = URL.createObjectURL(file)
  }
}

const errors = ref({
  belt_id: null as string | null,
  photo: null as string | null,
  name: '',
  cpf: '',
  birth_date: '',
  sex: '',
  class_type: null as string | null,
  user_id: null as string | null,
  address: {
    cep: '',
    street: '',
    number: '',
    complement: '',
    neighborhood: '',
    city: '',
  },
})

function validateCPF(cpf: string) {
  cpf = cpf.replace(/\D/g, '')
  if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false
  for (let t = 9; t < 11; t++) {
    let soma = 0
    for (let i = 0; i < t; i++) {
      soma += parseInt(cpf[i], 10) * (t + 1 - i)
    }
    const digito = ((10 * soma) % 11) % 10
    if (parseInt(cpf[t], 10) !== digito) return false
  }
  return true
}

function validate() {
  const e: Record<string, any> = {}
  if (!form.name?.trim()) e.name = 'Nome é obrigatório'
  if (form.cpf) {
    if (form.cpf.replace(/\D/g, '').length !== 11 || !validateCPF(form.cpf)) {
      e.cpf = 'CPF inválido'
    }
  }
  errors.value = e
  return Object.keys(e).length === 0
}

async function submit() {
  if (!validate()) return
  loading.value = true

  try {
    const data = new FormData()
    Object.entries(form).forEach(([key, value]) => {
      if (key === 'address' || key === 'emergency_contacts') {
        data.append(key, JSON.stringify(value))
      } else if (key === 'cpf') {
        /* opcional */
      } else if (key === 'photo') {
        if (value instanceof File) data.append('photo', value)
      } else if (key === 'medical_certificate') {
        if (value instanceof File) data.append('medical_certificate', value)
      } else if (key === 'registration_form_file') {
        if (value instanceof File) data.append('registration_form_file', value)
      } else if (typeof value === 'boolean') {
        data.append(key, value ? '1' : '0')
      } else if (value !== null && value !== undefined && value !== '') {
        data.append(key, value as any)
      }
    })
    if (form.user_id) {
      data.append('user_id', String(form.user_id))
    }

    const { data: created } = await axios.post('/api/students', data, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    alert('Cadastrado com sucesso')
    if (created?.id) {
      router.push(`/admin/students/${created.id}`)
    } else {
      router.push('/admin/students')
    }
  } catch (e) {
    alert('Erro ao cadastrar')
    console.log(e)
  }

  loading.value = false
}

async function getBelts() {
  try {
    const { data } = await axios.get('/api/belts')
    belts.value = data.map(({ id, name, group }: any) => ({
      label: group ? `${name} - ${group}` : name,
      value: id,
    }))
  } catch {
    /* ignore */
  }
}

async function getUsers() {
  try {
    const { data } = await axios.get('/api/users', { params: { all: 1 } })
    users.value = data.map((user: any) => ({
      label: `${user.name} (${user.username})`,
      value: user.id,
    }))
  } catch (error) {
    console.error(error)
  }
}

onMounted(async () => {
  await getBelts()
  await getUsers()
})
</script>

<template>
  <BaseLayout title="Novo aluno">
    <div class="tab-content">
      <div class="mx-auto">
        <form @submit.prevent="submit">
          <div class="mb-4 space-y-4">
            <h2 class="text-xl font-bold mb-4">Dados do aluno</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="md:col-span-1">
                <label class="font-medium">Foto <span class="font-normal text-gray-500">(opcional)</span></label>
                <div
                  v-if="displayPhotoUrl"
                  class="mt-2 mb-2 rounded-lg overflow-hidden border border-gray-200 max-w-[200px]"
                >
                  <img :src="displayPhotoUrl" alt="Foto do aluno" class="w-full h-44 object-cover block" />
                </div>
                <input type="file" accept="image/*" class="block w-full text-sm" @change="onPhotoFileChange" />
                <p class="text-xs text-gray-500 mt-1">Pode ficar em branco. JPG, PNG ou WebP · até 2 MB</p>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormInput v-model="form.name" label="Nome" placeholder="Nome completo" :error="errors.name" />

              <FormSelect
                v-model="form.user_id"
                :options="[{ value: '', label: 'Nenhum usuário vinculado' }, ...users]"
                label="Usuário vinculado"
                placeholder="Selecione"
                :error="errors.user_id"
              />

              <FormInput
                mask="###.###.###-##"
                v-model="form.cpf"
                label="CPF"
                placeholder="000.000.000-00"
                :error="errors.cpf"
              />

              <FormInput type="date" v-model="form.birth_date" label="Data de nascimento" :error="errors.birth_date" />

              <FormSelect
                v-model="form.sex"
                :options="[
                  { value: '', label: 'Selecione' },
                  { value: 'M', label: 'Masculino' },
                  { value: 'F', label: 'Feminino' },
                ]"
                label="Sexo"
                placeholder="Selecione"
                :error="errors.sex"
              />

              <FormSelect
                v-model="form.class_type"
                :options="[
                  { value: '', label: 'Selecione' },
                  { value: 'kids', label: 'Kids' },
                  { value: 'adult', label: 'Adulto' },
                ]"
                label="Turma"
                placeholder="Selecione"
                :error="errors.class_type"
              />

              <FormSelect v-model="form.belt_id" :options="belts" label="Graduação" placeholder="Selecione" :error="errors.belt_id" />
            </div>
          </div>
          <div class="mb-4 space-y-4">
            <h3 class="text-xl font-bold">Endereço</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormInput v-model="form.address.cep" label="CEP" placeholder="CEP" :error="errors.address.cep" />

              <FormInput v-model="form.address.street" label="Rua" placeholder="Nome da rua" :error="errors.address.street" />

              <FormInput v-model="form.address.number" label="Número" placeholder="Número" :error="errors.address.number" />

              <FormInput v-model="form.address.complement" label="Complemento" placeholder="Complemento" :error="errors.address.complement" />

              <FormInput v-model="form.address.neighborhood" label="Bairro" placeholder="Nome do bairro" :error="errors.address.neighborhood" />
              <FormInput v-model="form.address.city" label="Cidade" placeholder="Nome da cidade" :error="errors.address.city" />
            </div>
          </div>

          <div class="mb-4 space-y-4">
            <h3 class="text-xl font-bold">Contatos de emergência</h3>

            <div v-for="(contact, index) in form.emergency_contacts" :key="index" class="grid md:grid-cols-3 gap-4 items-end">
              <FormSelect
                v-model="contact.relationship"
                :options="[
                  { value: '', label: 'Selecione' },
                  { value: 'father', label: 'Pai' },
                  { value: 'mother', label: 'Mãe' },
                ]"
                label="Parentesco"
                placeholder="Selecione"
              />

              <FormInput v-model="contact.name" label="Nome" placeholder="Nome do contato" />

              <FormInput v-model="contact.phone" label="Telefone" placeholder="Telefone do contato" />
            </div>
          </div>

          <div class="mb-4 space-y-4">
            <h3 class="text-xl font-bold">Saúde</h3>
            <div>
              <FormInput v-model="form.other_sports" label="Outros esportes" placeholder="Opcional" />
            </div>

            <div>
              <label class="font-medium">Problemas de saúde</label>
              <textarea v-model="form.health_issues" class="input-base" />
            </div>

            <div>
              <label class="font-medium">Atestado médico</label>
              <input type="file" class="block w-full text-sm" @change="onCreateMedicalCert" />
            </div>
          </div>
          <div class="mb-4 space-y-4">
            <h3 class="text-xl font-bold">Autorizações</h3>
            <div>
              <label class="flex items-center gap-2 font-medium"> Ficha de cadastro assinada </label>

              <input type="file" class="block w-full text-sm" @change="onCreateRegistrationFile" />
            </div>
          </div>
          <div class="flex justify-between flex-wrap gap-3">
            <router-link to="/admin/students" class="btn-primary">Voltar</router-link>
            <button
              :disabled="loading"
              class="bg-blue-600 text-white p-3 rounded font-semibold hover:bg-blue-700 disabled:opacity-50"
            >
              {{ loading ? 'Salvando...' : 'Cadastrar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </BaseLayout>
</template>

<style scoped lang="scss">
.tab-content {
  background: white;
  border: 1px solid #ddd;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.input-base {
  width: 100%;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  padding: 0.5rem 0.75rem;
}

.input-base:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

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
