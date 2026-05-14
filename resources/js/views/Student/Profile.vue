<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import BaseLayout from '../../layouts/BaseLayout.vue'
import Tabs from '../../components/tabs/Tabs.vue'
import FormInput from '../../components/form/FormInput.vue'
import FormSelect from '../../components/form/FormSelect.vue'

const route = useRoute()

const isAdminView = computed(() => route.name === 'AdminStudentProfile')
const adminStudentId = computed(() =>
  isAdminView.value && route.params.id ? String(route.params.id) : '',
)

const adminEditMode = ref(false)
const adminSaveLoading = ref(false)
const belts = ref<{ label: string; value: number }[]>([])
const users = ref<{ label: string; value: string | number }[]>([])

const adminForm = reactive({
  belt_id: null as number | null,
  photo: null as File | null,
  name: '',
  cpf: '',
  birth_date: '',
  sex: '',
  class_type: null as string | null,
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

const adminSavedPhotoUrl = ref<string | null>(null)
const adminLocalPhotoPreview = ref<string | null>(null)
const adminDisplayPhotoUrl = computed(
  () => adminLocalPhotoPreview.value || adminSavedPhotoUrl.value,
)

const adminFormErrors = ref({
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

const loading = ref(true)
const trainingsLoading = ref(false)
const graduationsLoading = ref(false)
const user = ref<any>(null)
const student = ref<any>(null)
const trainings = ref<any[]>([])
const graduations = ref<any[]>([])

const pageTitle = computed(() => {
  if (isAdminView.value) {
    return student.value?.name ? `Aluno: ${student.value.name}` : 'Aluno'
  }
  return 'Meu Perfil'
})

const activeTab = ref('personal-data')

const tabs = reactive([
  { id: 'personal-data', name: 'Dados do aluno' },
  { id: 'training-history', name: 'Histórico de treinos' },
  { id: 'graduation-history', name: 'Histórico de graduação' },
])

const classTypeLabels: Record<string, string> = {
  kids: 'Kids',
  adult: 'Adulto',
}

const sexLabels: Record<string, string> = {
  M: 'Masculino',
  F: 'Feminino',
}

const relationshipLabels: Record<string, string> = {
  father: 'Pai',
  mother: 'Mãe',
}

const photoUrl = computed(() => {
  if (student.value?.photo_url) return student.value.photo_url
  const p = student.value?.photo
  if (!p) return null
  if (String(p).startsWith('http')) return p
  return `/storage/${p}`
})

const photoUploading = ref(false)
const photoError = ref('')
const photoInputRef = ref<HTMLInputElement | null>(null)

const timelineAsc = computed(() => {
  return [...graduations.value]
    .filter((g) => g.graduated_at)
    .sort((a, b) => {
      const da = String(a.graduated_at).split('T')[0]
      const db = String(b.graduated_at).split('T')[0]
      return da.localeCompare(db)
    })
})

function formatDate(iso: string | null | undefined) {
  if (!iso) return '—'
  const d = String(iso).split('T')[0]
  const [y, m, day] = d.split('-')
  if (!y || !m || !day) return iso
  return `${day}/${m}/${y}`
}

/** Partes para o bloco de data nos cards (pt-BR). */
function formatTrainingCalendarParts(iso: string | null | undefined) {
  if (!iso) {
    return { day: '—', month: '', weekday: '' }
  }
  const raw = String(iso).split('T')[0]
  const d = new Date(`${raw}T12:00:00`)
  if (Number.isNaN(d.getTime())) {
    return { day: '?', month: '', weekday: '' }
  }
  const day = d.toLocaleDateString('pt-BR', { day: '2-digit' })
  const month = d
    .toLocaleDateString('pt-BR', { month: 'short' })
    .replace(/\./g, '')
  const weekday = d
    .toLocaleDateString('pt-BR', { weekday: 'short' })
    .replace(/\./g, '')
  return { day, month, weekday }
}

function formatClassType(v: string | null | undefined) {
  if (!v) return '—'
  return classTypeLabels[v] ?? v
}

function formatSex(v: string | null | undefined) {
  if (!v) return '—'
  return sexLabels[v] ?? v
}

function formatRelationship(v: string | null | undefined) {
  if (!v) return '—'
  return relationshipLabels[v] ?? v
}

function formatCpfDisplay(cpf: string | null | undefined) {
  if (!cpf) return '—'
  const n = cpf.replace(/\D/g, '')
  if (n.length !== 11) return cpf
  return n.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
}

const monthNamesPt = [
  'janeiro',
  'fevereiro',
  'março',
  'abril',
  'maio',
  'junho',
  'julho',
  'agosto',
  'setembro',
  'outubro',
  'novembro',
  'dezembro',
]

function capitalizeMonth(name: string) {
  if (!name) return name
  return name.charAt(0).toLocaleUpperCase('pt-BR') + name.slice(1)
}

/** Anos com treino (tabs), mais recente primeiro. */
const trainingYearTabs = computed(() => {
  const years = new Set<number>()
  for (const list of trainings.value) {
    const raw = list.class_date
    const d = String(raw ?? '').split('T')[0]
    const y = parseInt(d.split('-')[0] ?? '', 10)
    if (y) years.add(y)
  }
  return Array.from(years)
    .sort((a, b) => b - a)
    .map((year) => ({ id: String(year), name: String(year) }))
})

const activeTrainingYear = ref('')

watch(
  trainingYearTabs,
  (tabs) => {
    if (!tabs.length) {
      activeTrainingYear.value = ''
      return
    }
    if (!tabs.some((t) => t.id === activeTrainingYear.value)) {
      activeTrainingYear.value = tabs[0].id
    }
  },
  { immediate: true },
)

/** Meses do ano selecionado (título só com o mês; o ano vem da aba). */
const trainingsByMonthForYear = computed(() => {
  const yearFilter = activeTrainingYear.value
  if (!yearFilter) return []

  const map = new Map<
    string,
    { key: string; label: string; count: number; items: any[] }
  >()

  for (const list of trainings.value) {
    const raw = list.class_date
    const d = String(raw ?? '').split('T')[0]
    const [y, m] = d.split('-')
    if (!y || !m || y !== yearFilter) continue
    const key = `${y}-${m}`
    if (!map.has(key)) {
      const mi = parseInt(m, 10) - 1
      const label =
        mi >= 0 && mi < 12
          ? capitalizeMonth(monthNamesPt[mi])
          : `${m}/${y}`
      map.set(key, { key, label, count: 0, items: [] })
    }
    const g = map.get(key)!
    g.count += 1
    g.items.push(list)
  }

  const groups = Array.from(map.values()).sort((a, b) => (a.key < b.key ? 1 : -1))
  for (const g of groups) {
    g.items.sort((a, b) =>
      String(b.class_date ?? '').localeCompare(String(a.class_date ?? '')),
    )
  }
  return groups
})

/** Mês expandido por defeito; `false` = recolhido. */
const monthExpanded = ref<Record<string, boolean>>({})

function isMonthExpanded(key: string) {
  return monthExpanded.value[key] !== false
}

function toggleMonthSection(key: string) {
  const open = !isMonthExpanded(key)
  monthExpanded.value = { ...monthExpanded.value, [key]: open }
}

watch([activeTrainingYear, trainings], () => {
  const next = { ...monthExpanded.value }
  for (const g of trainingsByMonthForYear.value) {
    if (!(g.key in next)) next[g.key] = true
  }
  monthExpanded.value = next
})

async function loadProfile() {
  loading.value = true
  try {
    if (isAdminView.value) {
      const id = adminStudentId.value
      if (!id) {
        student.value = null
        user.value = null
        return
      }
      const { data } = await axios.get(`/api/students/${id}`)
      student.value = data
      user.value = data.user
        ? { username: data.user.username }
        : { username: '—' }
    } else {
      const { data } = await axios.get('/api/auth/user')
      user.value = data
      student.value = data.student || null
    }
  } catch (error) {
    console.error(error)
    student.value = null
    user.value = null
  } finally {
    loading.value = false
  }
}

async function loadTrainings() {
  if (isAdminView.value) {
    if (!adminStudentId.value) return
  } else if (!student.value) {
    return
  }
  trainingsLoading.value = true
  try {
    const url = isAdminView.value
      ? `/api/students/${adminStudentId.value}/trainings`
      : '/api/auth/student/trainings'
    const { data } = await axios.get(url)
    trainings.value = Array.isArray(data) ? data : []
  } catch (error) {
    console.error(error)
    trainings.value = []
  } finally {
    trainingsLoading.value = false
  }
}

async function loadGraduations() {
  if (isAdminView.value) {
    if (!adminStudentId.value) return
  } else if (!student.value) {
    return
  }
  graduationsLoading.value = true
  try {
    const url = isAdminView.value
      ? `/api/students/${adminStudentId.value}/graduations`
      : '/api/auth/student/graduations'
    const { data } = await axios.get(url)
    graduations.value = Array.isArray(data) ? data : []
  } catch (error) {
    console.error(error)
    graduations.value = []
  } finally {
    graduationsLoading.value = false
  }
}

function beltLabel(belt: { name?: string; group?: string } | null | undefined) {
  if (!belt?.name) return '—'
  return belt.group ? `${belt.name} — ${belt.group}` : belt.name
}

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

function validateAdminForm() {
  const e: any = {
    belt_id: null,
    photo: null,
    name: '',
    cpf: '',
    birth_date: '',
    sex: '',
    class_type: null,
    user_id: null,
    address: {
      cep: '',
      street: '',
      number: '',
      complement: '',
      neighborhood: '',
      city: '',
    },
  }
  if (!adminForm.name?.trim()) e.name = 'Nome é obrigatório'
  if (adminForm.cpf) {
    if (adminForm.cpf.replace(/\D/g, '').length !== 11 || !validateCPF(adminForm.cpf)) {
      e.cpf = 'CPF inválido'
    }
  }
  adminFormErrors.value = e
  return !e.name && !e.cpf
}

function fillAdminFormFromStudent() {
  const s = student.value
  if (!s) return
  if (adminLocalPhotoPreview.value) {
    URL.revokeObjectURL(adminLocalPhotoPreview.value)
    adminLocalPhotoPreview.value = null
  }
  adminForm.belt_id = s.belt_id ?? null
  adminForm.name = s.name ?? ''
  adminForm.cpf = s.cpf ?? ''
  adminForm.birth_date = s.birth_date?.split?.('T')?.[0] ?? ''
  adminForm.sex = s.sex ?? ''
  adminForm.class_type = s.class_type ?? null
  adminForm.user_id = s.user_id ?? ''
  const addr = s.address || {}
  adminForm.address.cep = addr.cep ?? ''
  adminForm.address.street = addr.street ?? ''
  adminForm.address.number = addr.number ?? ''
  adminForm.address.complement = addr.complement ?? ''
  adminForm.address.neighborhood = addr.neighborhood ?? ''
  adminForm.address.city = addr.city ?? ''
  const ec = Array.isArray(s.emergency_contacts) ? s.emergency_contacts : []
  adminForm.emergency_contacts[0] = {
    name: ec[0]?.name ?? '',
    relationship: ec[0]?.relationship ?? '',
    phone: ec[0]?.phone ?? '',
  }
  adminForm.emergency_contacts[1] = {
    name: ec[1]?.name ?? '',
    relationship: ec[1]?.relationship ?? '',
    phone: ec[1]?.phone ?? '',
  }
  adminForm.other_sports = s.other_sports ?? ''
  adminForm.health_issues = s.health_issues ?? ''
  adminForm.photo = null
  adminForm.medical_certificate = null
  adminForm.registration_form_file = null
  adminSavedPhotoUrl.value =
    s.photo_url || (s.photo ? `/storage/${s.photo}` : null)
}

function onAdminPhotoChange(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0] ?? null
  if (adminLocalPhotoPreview.value) {
    URL.revokeObjectURL(adminLocalPhotoPreview.value)
    adminLocalPhotoPreview.value = null
  }
  adminForm.photo = file
  adminLocalPhotoPreview.value = file ? URL.createObjectURL(file) : null
}

async function loadBeltsAndUsers() {
  try {
    const [bRes, uRes] = await Promise.all([
      axios.get('/api/belts'),
      axios.get('/api/users', { params: { all: 1 } }),
    ])
    belts.value = (bRes.data || []).map(({ id, name, group }: any) => ({
      label: group ? `${name} - ${group}` : name,
      value: id,
    }))
    users.value = (uRes.data || []).map((u: any) => ({
      label: `${u.name} (${u.username})`,
      value: u.id,
    }))
  } catch (err) {
    console.error(err)
  }
}

function startAdminEdit() {
  fillAdminFormFromStudent()
  adminEditMode.value = true
}

function cancelAdminEdit() {
  adminEditMode.value = false
  if (adminLocalPhotoPreview.value) {
    URL.revokeObjectURL(adminLocalPhotoPreview.value)
    adminLocalPhotoPreview.value = null
  }
  adminForm.photo = null
}

async function submitAdminStudent() {
  if (!validateAdminForm()) return
  const id = adminStudentId.value
  if (!id) return
  adminSaveLoading.value = true
  try {
    const data = new FormData()
    Object.entries(adminForm).forEach(([key, value]) => {
      if (key === 'address' || key === 'emergency_contacts') {
        data.append(key, JSON.stringify(value))
      } else if (key === 'cpf') {
        /* opcional no backend */
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
    if (adminForm.user_id !== '' && adminForm.user_id != null) {
      data.append('user_id', String(adminForm.user_id))
    }
    data.append('_method', 'PUT')
    await axios.post(`/api/students/${id}`, data, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    await loadProfile()
    if (student.value?.user) {
      user.value = { username: student.value.user.username }
    } else {
      user.value = { username: '—' }
    }
    cancelAdminEdit()
    alert('Salvo com sucesso')
  } catch (e) {
    console.error(e)
    alert('Erro ao salvar')
  } finally {
    adminSaveLoading.value = false
  }
}

function onAdminMedicalFile(e: Event) {
  const input = e.target as HTMLInputElement
  adminForm.medical_certificate = input.files?.[0] ?? null
}

function onAdminRegistrationFile(e: Event) {
  const input = e.target as HTMLInputElement
  adminForm.registration_form_file = input.files?.[0] ?? null
}

async function onProfilePhotoChange(e: Event) {
  if (isAdminView.value) return
  const input = e.target as HTMLInputElement
  const file = input.files?.[0]
  input.value = ''
  if (!file || !student.value) return
  photoError.value = ''
  photoUploading.value = true
  try {
    const fd = new FormData()
    fd.append('photo', file)
    const { data } = await axios.post('/api/auth/student/photo', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    user.value = data
    student.value = data.student || null
    try {
      localStorage.setItem('user', JSON.stringify(data))
    } catch {
      /* ignore */
    }
  } catch (err: any) {
    photoError.value =
      err.response?.data?.message || 'Não foi possível atualizar a foto.'
  } finally {
    photoUploading.value = false
  }
}

watch(
  () => (isAdminView.value ? String(route.params.id ?? '') : ''),
  async (newId, oldId) => {
    if (!isAdminView.value || !newId || newId === oldId) return
    adminEditMode.value = false
    await loadProfile()
    await Promise.all([loadTrainings(), loadGraduations()])
  },
)

onMounted(async () => {
  await loadProfile()
  await Promise.all([loadTrainings(), loadGraduations()])
  if (isAdminView.value) await loadBeltsAndUsers()
})
</script>

<template>
  <BaseLayout :title="pageTitle">
    <div v-if="isAdminView" class="admin-student-nav">
      <RouterLink to="/admin/students" class="admin-student-nav__link">← Lista de alunos</RouterLink>
      <template v-if="adminStudentId">
        <button
          v-if="!adminEditMode"
          type="button"
          class="admin-student-nav__btn admin-student-nav__btn--primary"
          @click="startAdminEdit"
        >
          Habilitar edição
        </button>
        <button
          v-else
          type="button"
          class="admin-student-nav__btn"
          @click="cancelAdminEdit"
        >
          Cancelar edição
        </button>
      </template>
    </div>
    <Tabs :tabs="tabs" :selectedTab="activeTab" @tab="(val) => (activeTab = val)" />
    <div class="tab-content">
      <div v-if="loading" class="py-8 text-center text-gray-600">
        Carregando...
      </div>

      <template v-else>
        <div v-if="activeTab === 'personal-data'">
          <div v-if="!student" class="py-6 text-gray-600">
            Não há dados de aluno disponíveis para este usuário.
          </div>

          <div v-else class="mx-auto space-y-6">
            <form
              v-if="isAdminView && adminEditMode"
              class="space-y-6"
              @submit.prevent="submitAdminStudent"
            >
              <h2 class="text-xl font-bold mb-4">Dados do aluno</h2>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-1">
                  <label class="font-medium">Foto <span class="font-normal text-gray-500">(opcional)</span></label>
                  <div v-if="adminDisplayPhotoUrl" class="mt-2 mb-2 rounded-lg overflow-hidden border border-gray-200 max-w-[200px]">
                    <img :src="adminDisplayPhotoUrl" alt="" class="w-full h-44 object-cover block" />
                  </div>
                  <input type="file" accept="image/*" class="block w-full text-sm" @change="onAdminPhotoChange" />
                  <p class="text-xs text-gray-500 mt-1">JPG, PNG ou WebP · até 2 MB</p>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormInput v-model="adminForm.name" label="Nome" placeholder="Nome completo" :error="adminFormErrors.name" />
                <FormSelect
                  v-model="adminForm.user_id"
                  :options="[{ value: '', label: 'Nenhum usuário vinculado' }, ...users]"
                  label="Usuário vinculado"
                  placeholder="Selecione"
                  :error="adminFormErrors.user_id"
                />
                <FormInput mask="###.###.###-##" v-model="adminForm.cpf" label="CPF" placeholder="000.000.000-00" :error="adminFormErrors.cpf" />
                <FormInput type="date" v-model="adminForm.birth_date" label="Data de nascimento" :error="adminFormErrors.birth_date" />
                <FormSelect
                  v-model="adminForm.sex"
                  :options="[
                    { value: '', label: 'Selecione' },
                    { value: 'M', label: 'Masculino' },
                    { value: 'F', label: 'Feminino' },
                  ]"
                  label="Sexo"
                  placeholder="Selecione"
                  :error="adminFormErrors.sex"
                />
                <FormSelect
                  v-model="adminForm.class_type"
                  :options="[
                    { value: '', label: 'Selecione' },
                    { value: 'kids', label: 'Kids' },
                    { value: 'adult', label: 'Adulto' },
                  ]"
                  label="Turma"
                  placeholder="Selecione"
                  :error="adminFormErrors.class_type"
                />
                <FormSelect v-model="adminForm.belt_id" :options="belts" label="Graduação" placeholder="Selecione" :error="adminFormErrors.belt_id" />
              </div>

              <div class="mb-4 space-y-4">
                <h3 class="text-xl font-bold">Endereço</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <FormInput v-model="adminForm.address.cep" label="CEP" :error="adminFormErrors.address.cep" />
                  <FormInput v-model="adminForm.address.street" label="Rua" :error="adminFormErrors.address.street" />
                  <FormInput v-model="adminForm.address.number" label="Número" :error="adminFormErrors.address.number" />
                  <FormInput v-model="adminForm.address.complement" label="Complemento" :error="adminFormErrors.address.complement" />
                  <FormInput v-model="adminForm.address.neighborhood" label="Bairro" :error="adminFormErrors.address.neighborhood" />
                  <FormInput v-model="adminForm.address.city" label="Cidade" :error="adminFormErrors.address.city" />
                </div>
              </div>

              <div class="mb-4 space-y-4">
                <h3 class="text-xl font-bold">Contatos de emergência</h3>
                <div
                  v-for="(contact, index) in adminForm.emergency_contacts"
                  :key="index"
                  class="grid md:grid-cols-3 gap-4 items-end"
                >
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
                  <FormInput v-model="contact.phone" label="Telefone" placeholder="Telefone" />
                </div>
              </div>

              <div class="mb-4 space-y-4">
                <h3 class="text-xl font-bold">Saúde</h3>
                <FormInput v-model="adminForm.other_sports" label="Outros esportes" placeholder="Opcional" />
                <div>
                  <label class="font-medium">Problemas de saúde</label>
                  <textarea v-model="adminForm.health_issues" class="input-base min-h-[5rem]" />
                </div>
                <div>
                  <label class="font-medium">Atestado médico</label>
                  <input type="file" class="block w-full text-sm" @change="onAdminMedicalFile" />
                </div>
              </div>

              <div class="mb-4 space-y-4">
                <h3 class="text-xl font-bold">Autorizações</h3>
                <div>
                  <label class="font-medium">Ficha de cadastro assinada</label>
                  <input type="file" class="block w-full text-sm" @change="onAdminRegistrationFile" />
                </div>
              </div>

              <div class="flex flex-wrap justify-end gap-3">
                <button type="button" class="admin-student-nav__btn" @click="cancelAdminEdit">Cancelar</button>
                <button
                  type="submit"
                  class="admin-student-nav__btn admin-student-nav__btn--primary"
                  :disabled="adminSaveLoading"
                >
                  {{ adminSaveLoading ? 'Salvando…' : 'Salvar alterações' }}
                </button>
              </div>
            </form>

            <template v-if="!isAdminView || !adminEditMode">
            <div class="mb-4 space-y-4">
              <h2 class="text-xl font-bold mb-4">Dados do aluno</h2>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-1">
                  <label class="font-medium">Foto <span class="font-normal text-gray-500">(opcional)</span></label>
                  <div v-if="photoUrl" class="mt-2">
                    <img
                      :src="photoUrl"
                      alt="Foto do aluno"
                      class="max-h-48 w-full max-w-xs rounded-lg border border-gray-200 object-cover"
                    />
                  </div>
                  <div v-else class="profile-photo-placeholder mt-2">
                    {{ (student.name || '?').charAt(0).toUpperCase() }}
                  </div>
                  <template v-if="!isAdminView">
                    <input
                      ref="photoInputRef"
                      type="file"
                      class="profile-photo-file"
                      accept="image/jpeg,image/png,image/webp,image/gif"
                      @change="onProfilePhotoChange"
                    />
                    <button
                      type="button"
                      class="profile-photo-btn"
                      :disabled="photoUploading"
                      @click="photoInputRef?.click()"
                    >
                      {{
                        photoUploading
                          ? 'Enviando…'
                          : photoUrl
                            ? 'Alterar foto'
                            : 'Adicionar foto'
                      }}
                    </button>
                    <p v-if="photoError" class="profile-photo-err">{{ photoError }}</p>
                    <p class="text-xs text-gray-500 mt-1">Opcional. JPG, PNG ou WebP · até 2 MB</p>
                  </template>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="font-medium">Nome</label>
                  <div class="input-base bg-gray-50">{{ student.name || '—' }}</div>
                </div>
                <div>
                  <label class="font-medium">Usuário (login)</label>
                  <div class="input-base bg-gray-50">{{ user?.username || '—' }}</div>
                </div>
                <div>
                  <label class="font-medium">CPF</label>
                  <div class="input-base bg-gray-50">{{ formatCpfDisplay(student.cpf) }}</div>
                </div>
                <div>
                  <label class="font-medium">Data de nascimento</label>
                  <div class="input-base bg-gray-50">
                    {{ formatDate(student.birth_date) }}
                  </div>
                </div>
                <div>
                  <label class="font-medium">Sexo</label>
                  <div class="input-base bg-gray-50">{{ formatSex(student.sex) }}</div>
                </div>
                <div>
                  <label class="font-medium">Turma</label>
                  <div class="input-base bg-gray-50">
                    {{ formatClassType(student.class_type) }}
                  </div>
                </div>
                <div>
                  <label class="font-medium">Graduação</label>
                  <div class="input-base bg-gray-50">
                    {{
                      student.belt
                        ? `${student.belt.name}${student.belt.group ? ` — ${student.belt.group}` : ''}`
                        : '—'
                    }}
                  </div>
                </div>
              </div>
            </div>

            <div class="mb-4 space-y-4">
              <h3 class="text-xl font-bold">Endereço</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="font-medium">CEP</label>
                  <div class="input-base bg-gray-50">
                    {{ student.address?.cep || '—' }}
                  </div>
                </div>
                <div>
                  <label class="font-medium">Rua</label>
                  <div class="input-base bg-gray-50">
                    {{ student.address?.street || '—' }}
                  </div>
                </div>
                <div>
                  <label class="font-medium">Número</label>
                  <div class="input-base bg-gray-50">
                    {{ student.address?.number || '—' }}
                  </div>
                </div>
                <div>
                  <label class="font-medium">Complemento</label>
                  <div class="input-base bg-gray-50">
                    {{ student.address?.complement || '—' }}
                  </div>
                </div>
                <div>
                  <label class="font-medium">Bairro</label>
                  <div class="input-base bg-gray-50">
                    {{ student.address?.neighborhood || '—' }}
                  </div>
                </div>
                <div>
                  <label class="font-medium">Cidade</label>
                  <div class="input-base bg-gray-50">
                    {{ student.address?.city || '—' }}
                  </div>
                </div>
              </div>
            </div>

            <div class="mb-4 space-y-4">
              <h3 class="text-xl font-bold">Contatos de emergência</h3>
              <div
                v-for="(contact, index) in student.emergency_contacts || []"
                :key="index"
                class="grid md:grid-cols-3 gap-4 items-end"
              >
                <div>
                  <label class="font-medium">Parentesco</label>
                  <div class="input-base bg-gray-50">
                    {{ formatRelationship(contact.relationship) }}
                  </div>
                </div>
                <div>
                  <label class="font-medium">Nome</label>
                  <div class="input-base bg-gray-50">{{ contact.name || '—' }}</div>
                </div>
                <div>
                  <label class="font-medium">Telefone</label>
                  <div class="input-base bg-gray-50">{{ contact.phone || '—' }}</div>
                </div>
              </div>
              <p
                v-if="!(student.emergency_contacts && student.emergency_contacts.length)"
                class="text-gray-500 text-sm"
              >
                Nenhum contato cadastrado.
              </p>
            </div>

            <div class="mb-4 space-y-4">
              <h3 class="text-xl font-bold">Saúde</h3>
              <div>
                <label class="font-medium">Outros esportes</label>
                <div class="input-base bg-gray-50 min-h-[2.5rem] whitespace-pre-wrap">
                  {{ student.other_sports || '—' }}
                </div>
              </div>
              <div>
                <label class="font-medium">Problemas de saúde</label>
                <textarea
                  :value="student.health_issues || ''"
                  readonly
                  class="input-base bg-gray-50 min-h-[5rem] resize-none"
                />
              </div>
            </div>
            </template>
          </div>
        </div>

        <div v-if="activeTab === 'training-history'">
          <div v-if="!student" class="py-6 text-gray-600">
            Não há dados de aluno para exibir o histórico.
          </div>
          <template v-else>
            <h2 class="text-xl font-bold mb-4">Histórico de treinos</h2>
            <div v-if="trainingsLoading" class="py-6 text-gray-600">
              Carregando treinos...
            </div>
            <div v-else-if="!trainingYearTabs.length" class="text-gray-600">
              Nenhum treino registrado ainda.
            </div>
            <template v-else>
              <p class="training-year-label">Ano</p>
              <Tabs
                :tabs="trainingYearTabs"
                :selectedTab="activeTrainingYear"
                @tab="(id) => (activeTrainingYear = id)"
              />
              <div
                v-if="!trainingsByMonthForYear.length"
                class="text-gray-600 mt-4"
              >
                Nenhum treino em {{ activeTrainingYear }}.
              </div>
              <div v-else class="training-history mt-4">
                <section
                  v-for="group in trainingsByMonthForYear"
                  :key="group.key"
                  class="training-month"
                >
                  <header class="training-month__head">
                    <h3 class="training-month__title">{{ group.label }}</h3>
                    <div class="training-month__actions">
                      <span class="training-month__badge">
                        {{ group.count }}
                        {{ group.count === 1 ? 'treino' : 'treinos' }}
                      </span>
                      <button
                        type="button"
                        class="training-month__switch"
                        role="switch"
                        :aria-checked="isMonthExpanded(group.key)"
                        :aria-label="
                          isMonthExpanded(group.key)
                            ? `Ocultar treinos de ${group.label}`
                            : `Mostrar treinos de ${group.label}`
                        "
                        @click="toggleMonthSection(group.key)"
                      >
                        <span class="training-month__switch-label">Ver</span>
                        <span
                          class="training-month__switch-track"
                          :class="{
                            'training-month__switch-track--on':
                              isMonthExpanded(group.key),
                          }"
                        >
                          <span class="training-month__switch-thumb" />
                        </span>
                      </button>
                    </div>
                  </header>
                  <div
                    v-show="isMonthExpanded(group.key)"
                    class="training-cards"
                  >
                    <template v-for="item in group.items" :key="item.id">
                      <article class="training-card">
                        <template
                          v-for="p in [formatTrainingCalendarParts(item.class_date)]"
                          :key="item.id + '-cal'"
                        >
                          <div class="training-card__cal" aria-hidden="true">
                            <span class="training-card__cal-wd">{{ p.weekday }}</span>
                            <span class="training-card__cal-day">{{ p.day }}</span>
                            <span class="training-card__cal-mon">{{ p.month }}</span>
                          </div>
                        </template>
                        <div class="training-card__body">
                          <div class="training-card__top">
                            <time
                              class="training-card__date-full"
                              :datetime="String(item.class_date || '').split('T')[0]"
                            >
                              {{ formatDate(item.class_date) }}
                            </time>
                            <span
                              class="training-chip"
                              :class="{
                                'training-chip--kids': item.school_class?.type === 'kids',
                                'training-chip--adult': item.school_class?.type === 'adult',
                              }"
                            >
                              {{ formatClassType(item.school_class?.type) }}
                            </span>
                          </div>
                          <p
                            v-if="item.school_class?.name"
                            class="training-card__class"
                          >
                            <span class="training-card__class-label">Turma</span>
                            {{ item.school_class.name }}
                          </p>
                          <a :href="item.notes" target="_blank" v-if="item.notes" class="training-card__notes">
                            {{ item.notes }}
                          </a>
                        </div>
                      </article>
                    </template>
                  </div>
                </section>
              </div>
            </template>
          </template>
        </div>

        <div v-if="activeTab === 'graduation-history'">
          <div v-if="!student" class="py-6 text-gray-600">
            Não há dados de aluno para exibir o histórico.
          </div>
          <template v-else>
            <h2 class="text-xl font-bold mb-4">Histórico de graduação</h2>
            <p class="text-sm text-gray-600 mb-4">
              <template v-if="isAdminView">
                Histórico na ordem em que foi registrado. Faixa atual do aluno:
              </template>
              <template v-else>
                Sua jornada na ordem em que foi registrada. Faixa atual:
              </template>
              <strong>{{ beltLabel(student.belt) }}</strong>.
            </p>
            <div v-if="graduationsLoading" class="py-6 text-gray-600">
              Carregando graduações...
            </div>
            <div v-else-if="!graduations.length" class="text-gray-600">
              Nenhuma graduação registrada ainda.
            </div>
            <div v-else class="prof-timeline-panel">
              <ol class="prof-timeline">
                <li
                  v-for="(row, idx) in timelineAsc"
                  :key="row.id"
                  class="prof-timeline__item"
                >
                  <span
                    class="prof-timeline__dot"
                    :class="{ 'prof-timeline__dot--last': idx === timelineAsc.length - 1 }"
                  />
                  <div class="prof-timeline__card">
                    <p class="dash-timeline__belt">{{ row.belt.slug !== 'white' && row.degree === 0 ? 'Graduado à' : '' }} Faixa {{ beltLabel(row.belt) }} {{ Number(row.degree) !== 0 ? ` - ${row.degree} ${row.degree === 1 ? 'grau' : 'graus'}` : '' }}</p>
                    <p class="prof-timeline__date">{{ formatDate(row.graduated_at) }}</p>
                    <p
                      v-if="row.notes != null && String(row.notes).trim()"
                      class="prof-timeline__notes"
                    >
                      {{ row.notes }}
                    </p>
                  </div>
                </li>
              </ol>
            </div>
          </template>
        </div>
      </template>
    </div>
  </BaseLayout>
</template>

<style scoped lang="scss">
.admin-student-nav {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.admin-student-nav__link {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 0.875rem;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 600;
  text-decoration: none;
  color: #374151;
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
}

.admin-student-nav__link:hover {
  background: #e5e7eb;
}

.admin-student-nav__link--primary {
  color: #fff;
  background: #2563eb;
  border-color: #1d4ed8;
}

.admin-student-nav__link--primary:hover {
  background: #1d4ed8;
}

.admin-student-nav__btn {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 0.875rem;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  border: 1px solid #e5e7eb;
  background: #f3f4f6;
  color: #374151;
}

.admin-student-nav__btn:hover {
  background: #e5e7eb;
}

.admin-student-nav__btn--primary {
  color: #fff;
  background: #2563eb;
  border-color: #1d4ed8;
}

.admin-student-nav__btn--primary:hover:not(:disabled) {
  background: #1d4ed8;
}

.admin-student-nav__btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

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
  margin-top: 0.25rem;
}

.input-base:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

.profile-photo-placeholder {
  width: 100%;
  max-width: 12rem;
  height: 10rem;
  border-radius: 0.5rem;
  border: 1px dashed #d1d5db;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  font-weight: 800;
  color: #9ca3af;
  background: #f9fafb;
}

.profile-photo-file {
  position: absolute;
  width: 0;
  height: 0;
  opacity: 0;
  pointer-events: none;
}

.profile-photo-btn {
  margin-top: 0.5rem;
  font-size: 0.8125rem;
  font-weight: 600;
  padding: 0.45rem 0.9rem;
  border-radius: 0.5rem;
  border: 1px solid #d1d5db;
  background: #fff;
  color: #374151;
  cursor: pointer;

  &:disabled {
    opacity: 0.6;
    cursor: wait;
  }

  &:hover:not(:disabled) {
    background: #f3f4f6;
  }
}

.profile-photo-err {
  margin: 0.35rem 0 0;
  font-size: 0.8125rem;
  color: #b91c1c;
}

.prof-timeline-panel {
  padding: 1.1rem 1.2rem 1.25rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 0.875rem;
}

.prof-timeline {
  list-style: none;
  margin: 0;
  padding: 0;
}

.prof-timeline__item {
  position: relative;
  padding-left: 2rem;
  padding-bottom: 1.25rem;

  &::before {
    content: '';
    position: absolute;
    left: 0.55rem;
    top: 0.5rem;
    bottom: -0.2rem;
    width: 2px;
    background: #e5e7eb;
  }

  &:last-child::before {
    bottom: 50%;
  }
}

.prof-timeline__dot {
  position: absolute;
  left: 0.2rem;
  top: 0.35rem;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: #2563eb;
  border: 2px solid #fff;
  box-shadow: 0 0 0 2px #bfdbfe;
  z-index: 1;
}

.prof-timeline__dot--last {
  background: #059669;
  box-shadow: 0 0 0 2px #a7f3d0;
}

.prof-timeline__card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 0.75rem;
  padding: 0.85rem 1rem;
  box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
}

.prof-timeline__belt {
  margin: 0;
  font-weight: 700;
  font-size: 0.95rem;
  color: #111827;
}

.prof-timeline__date {
  margin: 0.2rem 0 0;
  font-size: 0.8125rem;
  color: #6b7280;
}

.prof-timeline__deg {
  margin: 0.35rem 0 0;
  font-size: 0.8125rem;
  color: #374151;
}

.prof-timeline__notes {
  margin: 0.35rem 0 0;
  font-size: 0.8125rem;
  color: #6b7280;
  line-height: 1.35;
}

/* —— Histórico de treinos (cards, mobile-first) —— */
.training-history {
  display: flex;
  flex-direction: column;
  gap: 1.75rem;
}

.training-year-label {
  margin: 0 0 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #6b7280;
}

.training-month__head {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem 0.75rem;
  margin-bottom: 0.875rem;
}

.training-month__actions {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  flex-shrink: 0;
}

.training-month__switch {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  margin: 0;
  padding: 0.15rem 0;
  border: none;
  background: transparent;
  cursor: pointer;
  font: inherit;
  color: #374151;
}

.training-month__switch:focus-visible {
  outline: 2px solid #6366f1;
  outline-offset: 2px;
  border-radius: 6px;
}

.training-month__switch-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #6b7280;
}

.training-month__switch-track {
  position: relative;
  width: 2.75rem;
  height: 1.375rem;
  border-radius: 9999px;
  background: #d1d5db;
  flex-shrink: 0;
  transition: background 0.2s ease;
}

.training-month__switch-track--on {
  background: #111827;
}

.training-month__switch-thumb {
  position: absolute;
  left: 2px;
  top: 50%;
  width: 1.125rem;
  height: 1.125rem;
  border-radius: 50%;
  background: #fff;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.12);
  transform: translateY(-50%);
  transition: left 0.2s ease;
}

.training-month__switch-track--on .training-month__switch-thumb {
  left: calc(100% - 2px - 1.125rem);
}

.training-month__title {
  margin: 0;
  font-size: clamp(1.05rem, 3.5vw, 1.2rem);
  font-weight: 700;
  color: #111827;
  text-transform: capitalize;
}

.training-month__badge {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #4b5563;
  background: #f3f4f6;
  padding: 0.35rem 0.65rem;
  border-radius: 9999px;
  white-space: nowrap;
}

.training-cards {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.75rem;
}

@media (min-width: 520px) {
  .training-cards {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.875rem;
  }
}

@media (min-width: 900px) {
  .training-cards {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

.training-card {
  display: flex;
  align-items: stretch;
  gap: 0.75rem;
  padding: 0.875rem 0.9rem;
  min-height: 5.5rem;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 0.875rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
  transition: box-shadow 0.15s ease, border-color 0.15s ease;
}

.training-card:active {
  border-color: #d1d5db;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.training-card__cal {
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 3.35rem;
  padding: 0.45rem 0.35rem;
  background: linear-gradient(160deg, #f8fafc 0%, #eef2ff 100%);
  border-radius: 0.65rem;
  border: 1px solid #e0e7ff;
}

.training-card__cal-wd {
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #6366f1;
  line-height: 1.2;
  text-align: center;
}

.training-card__cal-day {
  font-size: 1.35rem;
  font-weight: 800;
  line-height: 1.1;
  color: #111827;
}

.training-card__cal-mon {
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: lowercase;
  color: #6b7280;
  margin-top: 0.1rem;
}

.training-card__body {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 0.35rem;
}

.training-card__top {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.35rem 0.5rem;
}

.training-card__date-full {
  font-size: 0.8125rem;
  color: #6b7280;
  font-variant-numeric: tabular-nums;
}

.training-chip {
  display: inline-flex;
  align-items: center;
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
  margin-left: auto;
  background: #f3f4f6;
  color: #4b5563;
}

.training-chip--kids {
  background: #fffbeb;
  color: #b45309;
}

.training-chip--adult {
  background: #eff6ff;
  color: #1d4ed8;
}

.training-card__class {
  margin: 0;
  font-size: 0.8125rem;
  line-height: 1.35;
  color: #374151;
}

.training-card__class-label {
  display: inline-block;
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #9ca3af;
  margin-right: 0.35rem;
}

.training-card__notes {
  margin: 0;
  margin-top: 0.15rem;
  font-size: 0.8125rem;
  line-height: 1.4;
  color: #6b7280;
  display: -webkit-box;
  line-clamp: 4;
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
  overflow: hidden;
  word-break: break-word;
}
</style>
