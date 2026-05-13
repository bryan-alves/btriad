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

  const groups = Array.from(map.values()).sort((a, b) => (a.key < b.key ? 1 : -1))
  for (const g of groups) {
    g.items.sort((a, b) =>
      String(b.class_date ?? '').localeCompare(String(a.class_date ?? '')),
    )
  }
  return groups
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

async function onProfilePhotoChange(e: Event) {
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
                <div class="md:col-span-1">
                  <label class="font-medium">Foto</label>
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
                    {{ photoUploading ? 'Enviando…' : 'Alterar foto' }}
                  </button>
                  <p v-if="photoError" class="profile-photo-err">{{ photoError }}</p>
                  <p class="text-xs text-gray-500 mt-1">JPG, PNG ou WebP · até 2 MB</p>
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
            <div v-else class="training-history">
              <section
                v-for="group in trainingsByMonth"
                :key="group.key"
                class="training-month"
              >
                <header class="training-month__head">
                  <h3 class="training-month__title">{{ group.label }}</h3>
                  <span class="training-month__badge">
                    {{ group.count }}
                    {{ group.count === 1 ? 'treino' : 'treinos' }}
                  </span>
                </header>
                <div class="training-cards">
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
                              'training-chip--kids': item.class_type === 'kids',
                              'training-chip--adult': item.class_type === 'adult',
                            }"
                          >
                            {{ formatClassType(item.class_type) }}
                          </span>
                        </div>
                        <p
                          v-if="item.school_class?.name"
                          class="training-card__class"
                        >
                          <span class="training-card__class-label">Turma</span>
                          {{ item.school_class.name }}
                        </p>
                        <p v-if="item.notes" class="training-card__notes">
                          {{ item.notes }}
                        </p>
                      </div>
                    </article>
                  </template>
                </div>
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
              Sua jornada na ordem em que foi registrada. Faixa atual:
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
                    <p class="prof-timeline__belt">{{ beltLabel(row.belt) }}</p>
                    <p class="prof-timeline__date">{{ formatDate(row.graduated_at) }}</p>
                    <p
                      v-if="row.degree != null && String(row.degree).trim()"
                      class="prof-timeline__deg"
                    >
                      {{ row.degree }}
                    </p>
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

.training-month__head {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem 0.75rem;
  margin-bottom: 0.875rem;
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
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
  overflow: hidden;
  word-break: break-word;
}
</style>
