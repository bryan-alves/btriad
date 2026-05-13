<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import BaseLayout from '../../layouts/BaseLayout.vue'
import Tabs from '../../components/tabs/Tabs.vue'

const loading = ref(true)
const trainingsLoading = ref(false)
const graduationsLoading = ref(false)
const user = ref<any>(null)
const student = ref<any>(null)
const trainings = ref<any[]>([])
const graduations = ref<any[]>([])

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
  const p = student.value?.photo
  if (!p) return null
  if (String(p).startsWith('http')) return p
  return `/storage/${p}`
})

function formatDate(iso: string | null | undefined) {
  if (!iso) return '—'
  const d = String(iso).split('T')[0]
  const [y, m, day] = d.split('-')
  if (!y || !m || !day) return iso
  return `${day}/${m}/${y}`
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

const trainingsByMonth = computed(() => {
  const map = new Map<
    string,
    { key: string; label: string; count: number; items: any[] }
  >()

  const monthNames = [
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

  for (const list of trainings.value) {
    const raw = list.class_date
    const d = String(raw).split('T')[0]
    const [y, m] = d.split('-')
    if (!y || !m) continue
    const key = `${y}-${m}`
    if (!map.has(key)) {
      const mi = Number(m, 10) - 1
      const label =
        mi >= 0 && mi < 12
          ? `${monthNames[mi]} de ${y}`
          : `${m}/${y}`
      map.set(key, { key, label, count: 0, items: [] })
    }
    const g = map.get(key)!
    g.count += 1
    g.items.push(list)
  }

  return Array.from(map.values()).sort((a, b) => (a.key < b.key ? 1 : -1))
})

async function loadProfile() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/auth/user')
    user.value = data
    student.value = data.student || null
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

async function loadTrainings() {
  if (!student.value) return
  trainingsLoading.value = true
  try {
    const { data } = await axios.get('/api/auth/student/trainings')
    trainings.value = Array.isArray(data) ? data : []
  } catch (error) {
    console.error(error)
    trainings.value = []
  } finally {
    trainingsLoading.value = false
  }
}

async function loadGraduations() {
  if (!student.value) return
  graduationsLoading.value = true
  try {
    const { data } = await axios.get('/api/auth/student/graduations')
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

onMounted(async () => {
  await loadProfile()
  await Promise.all([loadTrainings(), loadGraduations()])
})
</script>

<template>
  <BaseLayout title="Meu Perfil">
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
            <div class="mb-4 space-y-4">
              <h2 class="text-xl font-bold mb-4">Dados do aluno</h2>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="font-medium">Foto</label>
                  <div v-if="photoUrl" class="mt-2">
                    <img
                      :src="photoUrl"
                      alt="Foto do aluno"
                      class="max-h-48 rounded-lg border border-gray-200 object-cover"
                    />
                  </div>
                  <div v-else class="input-base bg-gray-50 text-gray-500 mt-2">
                    Sem foto
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="font-medium">Nome</label>
                  <div class="input-base bg-gray-50">{{ student.name || '—' }}</div>
                </div>
                <div>
                  <label class="font-medium">E-mail da conta</label>
                  <div class="input-base bg-gray-50">{{ user?.email || '—' }}</div>
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
            <div v-else-if="!trainingsByMonth.length" class="text-gray-600">
              Nenhum treino registrado ainda.
            </div>
            <div v-else class="space-y-8">
              <section
                v-for="group in trainingsByMonth"
                :key="group.key"
                class="border-b border-gray-200 pb-6 last:border-0"
              >
                <div class="flex flex-wrap items-baseline justify-between gap-2 mb-3">
                  <h3 class="text-lg font-semibold capitalize">{{ group.label }}</h3>
                  <p class="text-sm text-gray-600">
                    {{ group.count }}
                    {{ group.count === 1 ? 'treino neste mês' : 'treinos neste mês' }}
                  </p>
                </div>
                <ul class="divide-y divide-gray-100 rounded-lg border border-gray-200 overflow-hidden">
                  <li
                    v-for="item in group.items"
                    :key="item.id"
                    class="px-4 py-3 bg-white hover:bg-gray-50"
                  >
                    <div class="font-medium">
                      {{ formatDate(item.class_date) }}
                      <span class="text-gray-500 font-normal">
                        · {{ formatClassType(item.class_type) }}
                      </span>
                    </div>
                    <div v-if="item.school_class?.name" class="text-sm text-gray-600 mt-0.5">
                      Turma: {{ item.school_class.name }}
                    </div>
                    <div v-if="item.notes" class="text-sm text-gray-500 mt-1">
                      {{ item.notes }}
                    </div>
                  </li>
                </ul>
              </section>
            </div>
          </template>
        </div>

        <div v-if="activeTab === 'graduation-history'">
          <div v-if="!student" class="py-6 text-gray-600">
            Não há dados de aluno para exibir o histórico.
          </div>
          <template v-else>
            <h2 class="text-xl font-bold mb-4">Histórico de graduação</h2>
            <p class="text-sm text-gray-600 mb-4">
              Registros de faixa/grau cadastrados pela equipe, da mais recente para a mais antiga.
            </p>
            <div v-if="graduationsLoading" class="py-6 text-gray-600">
              Carregando graduações...
            </div>
            <div v-else-if="!graduations.length" class="text-gray-600">
              Nenhuma graduação registrada ainda.
            </div>
            <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
              <table class="grad-table w-full text-sm">
                <thead>
                  <tr>
                    <th>Data</th>
                    <th>Faixa</th>
                    <th>Grau</th>
                    <th>Observações</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="row in graduations" :key="row.id">
                    <td>{{ formatDate(row.graduated_at) }}</td>
                    <td>{{ beltLabel(row.belt) }}</td>
                    <td>{{ row.degree != null && String(row.degree).trim() ? row.degree : '—' }}</td>
                    <td class="notes-cell">{{ row.notes != null && String(row.notes).trim() ? row.notes : '—' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </template>
        </div>
      </template>
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
  margin-top: 0.25rem;
}

.input-base:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

.grad-table {
  border-collapse: collapse;
  background: #fff;
}

.grad-table th,
.grad-table td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #e5e7eb;
  text-align: left;
  vertical-align: top;
}

.grad-table th {
  background: #f9fafb;
  font-weight: 600;
  color: #374151;
}

.grad-table tbody tr:hover {
  background: #fafafa;
}

.grad-table .notes-cell {
  max-width: 20rem;
  white-space: pre-wrap;
  word-break: break-word;
}
</style>
