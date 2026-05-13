<script setup lang="ts">
import BaseLayout from "../../layouts/BaseLayout.vue"
import { useRoute } from 'vue-router'
import { onMounted, reactive, ref, computed } from "vue"
import axios from "axios"
import FormInput from "../../components/form/FormInput.vue"
import FormSelect from "../../components/form/FormSelect.vue"
import Tabs from "../../components/tabs/Tabs.vue"

const loading = ref(false)

const route = useRoute();

const studentId = ref(null);

const activeTab = ref('personal-data')

const tabs = reactive([
  {
    id: 'personal-data',
    name: 'Dados pessoais'
  },
])


const form = reactive({
  belt_id: null,
  photo: null,
  name: "",
  cpf: "",
  birth_date: "",
  sex: "",
  class_type: null,
  user_id: '',
  address: {
    cep: "",
    street: "",
    number: "",
    complement: "",
    neighborhood: "",
    city: "",
  },
  emergency_contacts: [
    { name: "", relationship: "", phone: "" },
    { name: "", relationship: "", phone: "" }
  ],
  other_sports: "",
  health_issues: "",
  medical_certificate: null,
})

const belts = ref([]);
const users = ref([]);

/** URL da foto já salva (API envia `photo_url` com o append do modelo). */
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
  form.photo = file as any
  if (file) {
    localPhotoPreview.value = URL.createObjectURL(file)
  }
}

const errors = ref({
  belt_id: null,
  photo: null,
  name: "",
  cpf: "",
  birth_date: "",
  sex: "",
  class_type: null,
  user_id: null,
  address: {
    cep: "",
    street: "",
    number: "",
    complement: "",
    neighborhood: "",
    city: "",
  },
});

function validateCPF(cpf) {
  // Remove tudo que não for número
  cpf = cpf.replace(/\D/g, '');

  // Verifica tamanho e sequência inválida
  if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
    return false;
  }

  // Validação dos dígitos verificadores
  for (let t = 9; t < 11; t++) {
    let soma = 0;

    for (let i = 0; i < t; i++) {
      soma += parseInt(cpf[i]) * ((t + 1) - i);
    }

    let digito = ((10 * soma) % 11) % 10;

    if (parseInt(cpf[t]) !== digito) {
      return false;
    }
  }

  return true;
}

function validate() {
  const e: any = {
    /*address: [
      { street: "obrigatório", number: "", complement: "", neighborhood: "", city: "", state: "" }
    ]*/
  }

  // ===== DADOS PRINCIPAIS =====
  if (!form.name?.trim()) e.name = "Nome é obrigatório"

  /*if (!form.cpf) {
    e.cpf = "CPF é obrigatório"
  } else if (form.cpf.replace(/\D/g, '').length !== 11) {
    e.cpf = "CPF inválido"
  }*/

  if (form.cpf) {

    if (form.cpf.replace(/\D/g, '').length !== 11 || !validateCPF(form.cpf)) {
      e.cpf = "CPF inválido"
    }

  }

  /*if (!form.birth_date) e.birth_date = "Data de nascimento obrigatória"

  if (!form.sex) e.sex = "Sexo é obrigatório"

  if (!form.class_type) e.class_type = "Turma é obrigatória"

  if (!form.belt_id) e.belt_id = "Graduação é obrigatória"

  // ===== ENDEREÇO =====
  if (!form.address.street?.trim()) e.address.street = "Rua é obrigatória"
  if (!form.address.number?.trim()) e.address.number = "Número é obrigatório"
  if (!form.address.neighborhood?.trim()) e.address.neighborhood = "Bairro é obrigatório"
  if (!form.address.city?.trim()) e.address.city = "Cidade é obrigatória"
  if (!form.address.cep) e.address.cep = "Estado é obrigatório"

  // ===== CONTATO EMERGÊNCIA =====
  const firstContact = form.emergency_contacts[0]

  if (!firstContact?.name?.trim())
    e.contact_name = "Nome do contato é obrigatório"

  if (!firstContact?.relationship)
    e.relationship = "Parentesco é obrigatório"

  if (!firstContact?.phone?.trim())
    e.phone = "Telefone é obrigatório"

  // ===== OUTROS ESPORTES =====
  if (form.practices_other_sports && !form.other_sports?.trim()) {
    e.other_sports = "Informe quais esportes"
  }

  // ===== AUTORIZAÇÃO =====
  if (!form.image_authorization) {
    e.image_authorization = "É necessário autorizar uso de imagem"
  }

  if (!form.image_authorization_file) {
    e.image_authorization_file = "Envie o documento assinado"
  }*/

  errors.value = e

  return Object.keys(e).length === 0
}

function fillForm(data: any) {
  if (localPhotoPreview.value) {
    URL.revokeObjectURL(localPhotoPreview.value)
    localPhotoPreview.value = null
  }
  ;(form as any).photo = null

  Object.assign(form, data)
  form.birth_date = data.birth_date?.split('T')[0] ?? ""
  form.user_id = data.user_id ?? ''

  savedPhotoUrl.value =
    data.photo_url ||
    (data.photo ? `/storage/${data.photo}` : null)

  // garantir objetos internos
  if (data.address) {
    Object.assign(form.address, data.address)
  }

  if (data.emergency_contacts) {
    Object.assign(form.emergency_contacts, data.emergency_contacts);
  }

  delete (form as any).photo_url
  delete (form as any).belt
}

function formatCPF(value) {
  if (!value) return ''

  return value
    .replace(/\D/g, '') // remove tudo que não é número
    .slice(0, 11) // limita a 11 dígitos
    .replace(/(\d{3})(\d)/, '$1.$2')
    .replace(/(\d{3})(\d)/, '$1.$2')
    .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
}

function clearForm() {
  form.name = '';
}

async function submit() {
  if (!validate()) return
  loading.value = true

  try {
    const data = new FormData()

    Object.entries(form).forEach(([key, value]) => {

      if (key === "address" || key === "emergency_contacts") {
        data.append(key, JSON.stringify(value))
      } else if (key === "cpf") {
       // data.append(key, value.replace(/\D/g, ''))
      }

      else if (typeof value === "boolean") {
        data.append(key, value ? "1" : "0")
      }

      else if (value !== null) {
        data.append(key, value as any)
      }

    })
    if (form.user_id) {
      data.append('user_id', form.user_id)
    }
    let url = `/api/students`

    if (studentId.value) {
      data.append('_method', 'PUT')
      url = `${url}/${studentId.value}`;
    }

    await axios.post(url, data, {
      headers: {
        "Content-Type": "multipart/form-data"
      }
    })

    if (studentId.value) {
      await getStudent()
      alert('Salvo com sucesso')
    } else {
      clearForm()
      alert('Cadastrado')
    }
  } catch (e) {
    alert("Erro ao atualizar")
    console.log(e)
  }

  loading.value = false
}

async function getBelts() {
  try {
    const { data } = await axios.get('/api/belts');

    belts.value = data.map(({ id, name, group }) => {
      return {
        label: group ? `${name} - ${group}` : name,
        value: id,
      }
    });
  } catch (error) {

  }
}

async function getUsers() {
  try {
    const { data } = await axios.get('/api/users');

    users.value = data.map((user) => {
      return {
        label: `${user.name} (${user.username})`,
        value: user.id,
      }
    });
  } catch (error) {
    console.error(error)
  }
}

async function getStudent() {
  try {
    const { data } = await axios.get(`/api/students/${studentId.value}`)

    fillForm(data);
  } catch (error) {

  }
}

onMounted(async () => {
  studentId.value = route.params.id;

  await getBelts();
  await getUsers();

  if (studentId.value) {
    tabs.push({
      id: 'progress-report',
      name: 'Relatório de Progresso'
    })
    await getStudent();
  }
})
</script>

<template>
  <BaseLayout :title="studentId ? 'Perfil do aluno' : 'Novo aluno'">
    <Tabs :tabs="tabs" :selectedTab="activeTab" @tab="(val) => activeTab = val" />
    <div class="tab-content">
      <div v-if="activeTab === 'personal-data'">

        <div class="mx-auto">

          <form @submit.prevent="submit">
            <!-- FOTO -->
            <div class="mb-4 space-y-4">
              <h2 class="text-xl font-bold mb-4">Dados do aluno</h2>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-1">
                  <label class="font-medium">Foto <span class="font-normal text-gray-500">(opcional)</span></label>
                  <div v-if="displayPhotoUrl" class="mt-2 mb-2 rounded-lg overflow-hidden border border-gray-200 max-w-[200px]">
                    <img :src="displayPhotoUrl" alt="Foto do aluno" class="w-full h-44 object-cover block" />
                  </div>
                  <input type="file" accept="image/*" class="block w-full text-sm"
                    @change="onPhotoFileChange" />
                  <p class="text-xs text-gray-500 mt-1">Pode ficar em branco. JPG, PNG ou WebP · até 2 MB</p>
                </div>

              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormInput v-model="form.name" :value="form.name" label="Nome" placeholder="Nome completo" :error="errors.name" />

                <FormSelect v-model="form.user_id" :options="[{ value: '', label: 'Nenhum usuário vinculado' }, ...users]" label="Usuário vinculado" placeholder="Selecione" :error="errors.user_id" />

                <FormInput mask="###.###.###-##" v-model="form.cpf" label="CPF" placeholder="000.000.000-00"
                  :error="errors.cpf" />

                <FormInput type="date" v-model="form.birth_date" label="Data de nascimento"
                  placeholder="Selecione a data" :error="errors.birth_date" />

                <FormSelect v-model="form.sex" :options="[
                  { value: '', label: 'Selecione' },
                  { value: 'M', label: 'Masculino' },
                  { value: 'F', label: 'Feminino' }
                ]" label="Sexo" placeholder="Selecione" :error="errors.sex" />

                <FormSelect v-model="form.class_type" :options="[
                  { value: '', label: 'Selecione' },
                  { value: 'kids', label: 'Kids' },
                  { value: 'adult', label: 'Adulto' }
                ]" label="Turma" placeholder="Selecione" :error="errors.class_type" />

                <FormSelect v-model="form.belt_id" :options="belts" label="Graduação" placeholder="Selecione"
                  :error="errors.belt_id" />
              </div>
            </div>
            <div class="mb-4 space-y-4">
              <h3 class="text-xl font-bold">Endereço</h3>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormInput v-model="form.address.cep" label="CEP" placeholder="CEP" :error="errors.address.cep" />

                <FormInput v-model="form.address.street" label="Rua" placeholder="Nome da rua"
                  :error="errors.address.street" />

                <FormInput v-model="form.address.number" label="Número" placeholder="Número"
                  :error="errors.address.number" />

                <FormInput v-model="form.address.complement" label="Complemento" placeholder="Complemento"
                  :error="errors.address.complement" />

                <FormInput v-model="form.address.neighborhood" label="Bairro" placeholder="Nome do bairro"
                  :error="errors.address.neighborhood" />
                <FormInput v-model="form.address.city" label="Cidade" placeholder="Nome da cidade"
                  :error="errors.address.city" />

              </div>
            </div>

            <div class="mb-4 space-y-4">
              <h3 class="text-xl font-bold">Contatos de emergência</h3>

              <div v-for="(contact, index) in form.emergency_contacts" :key="index"
                class="grid md:grid-cols-3 gap-4 items-end">
                <FormSelect v-model="contact.relationship" :options="[
                  { value: '', label: 'Selecione' },
                  { value: 'father', label: 'Pai' },
                  { value: 'mother', label: 'Mãe' }
                ]" label="Parentesco" placeholder="Selecione" :error="index === 0 ? errors.relationship : null" />

                <FormInput v-model="contact.name" label="Nome" placeholder="Nome do contato"
                  :error="index === 0 ? errors.contact_name : null" />

                <FormInput v-model="contact.phone" label="Telefone" placeholder="Telefone do contato"
                  :error="index === 0 ? errors.phone : null" />
              </div>
            </div>

            <div class="mb-4 space-y-4">
              <h3 class="text-xl font-bold">Saúde</h3>
              <!-- OUTROS ESPORTES -->
              <div>
                <FormInput v-if="form.practices_other_sports" v-model="form.other_sports" placeholder="Quais?"
                  :error="errors.other_sports" />
              </div>

              <!-- SAÚDE -->
              <div>
                <label class="font-medium">Problemas de saúde</label>
                <textarea v-model="form.health_issues" class="input-base" />
              </div>

              <div>
                <label class="font-medium">Atestado médico</label>
                <input type="file" class="block w-full text-sm"
                  @change="e => form.medical_certificate = e.target.files[0]" />
              </div>
            </div>
            <div class="mb-4 space-y-4">
              <h3 class="text-xl font-bold">Autorizações</h3>
              <!-- IMAGEM -->
              <div>
                <label class="flex items-center gap-2 font-medium">
                  Ficha de cadastro assinada
                </label>

                <input type="file" class="block w-full text-sm"
                  @change="e => form.image_authorization_file = e.target.files[0]" />
              </div>
            </div>
            <div style="display: flex; justify-content: space-between;">
              <router-link to="/admin/students/" class="btn-primary">Voltar</router-link>
              <button :disabled="loading"
                class=" bg-blue-600 text-white p-3 rounded font-semibold hover:bg-blue-700 disabled:opacity-50">
                {{ loading ? "Salvando..." : "Cadastrar" }}
              </button>
            </div>

          </form>
        </div>
      </div>

      <!-- DADOS DE TREINO -->
      <div v-if="activeTab === 'progress-report'">
        <div class="form-group">
          <label>Faixa</label>
          <select>
            <option>Branca</option>
          </select>
        </div>

        <div class="form-group">
          <label>Faz outros esportes?</label>
          <select>
            <option>Sim</option>
            <option>Não</option>
          </select>
        </div>

        <div class="form-group">
          <label>Atestado médico</label>
          <input type="checkbox" />
        </div>
      </div>

    </div>
  </BaseLayout>
</template>

<style scoped lang="scss">
.tabs {
  display: inline-flex;
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden; // 🔥 deixa tudo colado
}

.tab {
  padding: 10px 16px;
  background: #f9f9f9;
  border: none;
  cursor: pointer;
  font-size: 14px;

  // remove espaçamento entre botões
  &:not(:last-child) {
    border-right: 1px solid #ddd;
  }

  &:hover {
    background: #f1f1f1;
  }
}

.tab.active {
  background: #111;
  color: white;
}

.tab-content {
  background: white;
  border: 1px solid #DDD;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, .3);
}

.input-base {
  width: 100%;
  border: 1px solid #d1d5db;
  /* gray-300 */
  border-radius: 0.5rem;
  padding: 0.5rem 0.75rem;
}

.input-base:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}
</style>