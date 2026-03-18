<script setup lang="ts">
import BaseLayout from "../../layouts/BaseLayout.vue"
import { onMounted, reactive, ref } from "vue"
import axios from "axios"
import FormInput from "../../components/form/FormInput.vue"
import FormSelect from "../../components/form/FormSelect.vue"

const loading = ref(false)

const form = reactive({
  belt_id: null,
  photo: null,
  name: "",
  cpf: "",
  birth_date: "",
  sex: "",

  address: {
    street: "",
    number: "",
    complement: "",
    neighborhood: "",
    city: "",
    state: ""
  },

  emergency_contacts: [
    { name: "", relationship: "", phone: "" },
    { name: "", relationship: "", phone: "" }
  ],

  practices_other_sports: false,
  other_sports: "",

  health_issues: "",
  medical_certificate: null,

  image_authorization: false,
  image_authorization_file: null
})

const belts = ref([]);

const errors = ref({})

function validate() {
  const e: any = {}

  if (!form.name) e.name = "Nome é obrigatório"

  if (form.cpf && form.cpf.length < 11)
    e.cpf = "CPF inválido"

  if (!form.birth_date)
    e.birth_date = "Data de nascimento obrigatória"

  if (!form.emergency_contacts[0]?.name)
    e.emergency_contacts = "Adicione pelo menos um contato"

  errors.value = e

  return Object.keys(e).length === 0
}

async function submit() {
  //if (!validate()) return
  loading.value = true

  try {

    const data = new FormData()

    Object.entries(form).forEach(([key, value]) => {

      if (key === "address" || key === "emergency_contacts") {
        data.append(key, JSON.stringify(value))
      }

      else if (typeof value === "boolean") {
        data.append(key, value ? "1" : "0")
      }

      else if (value !== null) {
        data.append(key, value as any)
      }

    })

    await axios.post("/api/students", data, {
      headers: {
        "Content-Type": "multipart/form-data"
      }
    })

    alert("Aluno cadastrado!")

  } catch (e) {
    console.error(e)
    alert("Erro ao cadastrar")
  }

  loading.value = false
}

async function getBelts() {
  try {
    const { data } = await axios.get('/api/belts');

    belts.value = data.map(({id, name, group}) => {
      return {
        label: `${name} - ${group}`,
        value: id,
      }
    });
  } catch (error) {

  }
}

onMounted(async () => {
  await getBelts()
})
</script>

<template>
  <BaseLayout title="Novo aluno">
    <div class="mx-auto">

      <form @submit.prevent="submit">
        <!-- FOTO -->
        <div class="mb-4 space-y-4">
          <h2 class="text-xl font-bold mb-4">Dados do aluno</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="font-medium">Foto</label>
              <input type="file" class="block w-full text-sm" @change="e => form.photo = e.target.files[0]" />
            </div>
            <FormSelect v-model="form.belt_id" :options="belts" label="Graduação" placeholder="Selecione" :error="errors.state" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <FormInput v-model="form.name" label="Nome" placeholder="Nome completo" :error="errors.name" />
            <FormInput v-model="form.cpf" label="CPF" placeholder="000.000.000-00" :error="errors.cpf" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <FormInput type="date" v-model="form.birth_date" label="Data de nascimento" placeholder="Selecione a data"
              :error="errors.birth_date" />

            <FormSelect v-model="form.sex" :options="[
              { value: '', label: 'Selecione' },
              { value: 'M', label: 'Masculino' },
              { value: 'F', label: 'Feminino' }
            ]" label="Sexo" placeholder="Selecione" :error="errors.sex" />

          </div>
        </div>
        <div class="mb-4 space-y-4">
          <h3 class="text-xl font-bold">Endereço</h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <FormInput v-model="form.address.street" label="Rua" placeholder="Nome da rua" :error="errors.street" />

            <FormInput v-model="form.address.number" label="Número" placeholder="Número" :error="errors.number" />

            <FormInput v-model="form.address.complement" label="Complemento" placeholder="Complemento"
              :error="errors.complement" />

            <FormInput v-model="form.address.neighborhood" label="Bairro" placeholder="Nome do bairro"
              :error="errors.neighborhood" />
            <FormInput v-model="form.address.city" label="Cidade" placeholder="Nome da cidade" :error="errors.city" />


            <FormSelect v-model="form.address.state" :options="[
              { value: '', label: 'Selecione' },
              { value: 'AC', label: 'Acre' },
              { value: 'AL', label: 'Alagoas' },
              { value: 'AP', label: 'Amapá' },
              { value: 'AM', label: 'Amazonas' },
              { value: 'BA', label: 'Bahia' },
              { value: 'CE', label: 'Ceará' },
              { value: 'DF', label: 'Distrito Federal' },
              { value: 'ES', label: 'Espírito Santo' },
              { value: 'GO', label: 'Goiás' },
              { value: 'MA', label: 'Maranhão' },
              { value: 'MT', label: 'Mato Grosso' },
              { value: 'MS', label: 'Mato Grosso do Sul' },
              { value: 'MG', label: 'Minas Gerais' },
              { value: 'PA', label: 'Pará' },
              { value: 'PB', label: 'Paraíba' },
              { value: 'PR', label: 'Paraná' },
              { value: 'PE', label: 'Pernambuco' },
              { value: 'PI', label: 'Piauí' },
              { value: 'RJ', label: 'Rio de Janeiro' },
              { value: 'RN', label: 'Rio Grande do Norte' },
              { value: 'RS', label: 'Rio Grande do Sul' },
              { value: 'RO', label: 'Rondônia' },
              { value: 'RR', label: 'Roraima' },
              { value: 'SC', label: 'Santa Catarina' },
              { value: 'SP', label: 'São Paulo' },
              { value: 'SE', label: 'Sergipe' },
              { value: 'TO', label: 'Tocantins' }
            ]" label="Estado" placeholder="Selecione" :error="errors.state" />

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
            ]" label="Parentesco" placeholder="Selecione" :error="errors.relationship" />

            <FormInput v-model="contact.name" label="Nome" placeholder="Nome do contato" :error="errors.name" />

            <FormInput v-model="contact.phone" label="Telefone" placeholder="Telefone do contato"
              :error="errors.phone" />
          </div>
        </div>

        <div class="mb-4 space-y-4">
          <h3 class="text-xl font-bold">Saúde</h3>
          <!-- OUTROS ESPORTES -->
          <div>
            <label class="flex items-center gap-2">
              <input type="checkbox" v-model="form.practices_other_sports" />
              Pratica outros esportes
            </label>

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
          <div class="space-y-3">
            <!-- CHECKBOX -->
            <label class="flex items-center gap-2 font-medium cursor-pointer">
              <input type="checkbox" v-model="form.image_authorization"
                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
              Autorização de uso de imagem
            </label>

            <!-- FILE INPUT CUSTOM -->
            <div>
              <label
                class="flex items-center justify-center w-full px-4 py-6 border-2 border-dashed rounded-xl cursor-pointer hover:bg-gray-50 transition">
                <div class="text-center">
                  <p class="text-sm font-medium text-gray-700">
                    Clique para enviar o arquivo
                  </p>
                  <p class="text-xs text-gray-500">
                    ou arraste e solte aqui
                  </p>

                  <p v-if="form.image_authorization_file" class="mt-2 text-xs text-green-600">
                    {{ form.image_authorization_file.name }}
                  </p>
                </div>

                <input type="file" class="hidden" @change="e => form.image_authorization_file = e.target.files[0]" />
              </label>
            </div>
          </div>
        </div>
        <div style="display: flex; justify-content: space-between;">
          <router-link to="/admin/students/create" class="btn-primary">Voltar</router-link>
          <button :disabled="loading"
            class=" bg-blue-600 text-white p-3 rounded font-semibold hover:bg-blue-700 disabled:opacity-50">
            {{ loading ? "Salvando..." : "Cadastrar" }}
          </button>
        </div>

      </form>
    </div>
  </BaseLayout>
</template>

<style scoped>
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