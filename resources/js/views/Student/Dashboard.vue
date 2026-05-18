<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import axios from 'axios'
import BaseLayout from '../../layouts/BaseLayout.vue'
import {
  annualRankForStudent,
  currentDayStreak,
  currentWeekStreak,
  attendanceFrequencyPercent,
  collectClassesFromTrainings,
  filterTrainingsByClassId,
  lastMonthsTrainingCounts,
  lastTrainingDate,
  maxDayStreak,
  parseStudentTrainingsPayload,
  sessionsForClassMonth,
  trainingsInMonth,
} from '../../utils/studentDashboard'

const loading = ref(true)
const user = ref<any>(null)
const student = ref<any>(null)
const trainings = ref<any[]>([])
const academySessionsByMonth = ref<Record<string, number>>({})
const academySessionsByClassMonth = ref<Record<string, Record<string, number>>>({})
const graduations = ref<any[]>([])
const attendanceLists = ref<any[]>([])
const photoUploading = ref(false)
const photoError = ref('')
const fileInputRef = ref<HTMLInputElement | null>(null)

const now = new Date()
const currentYear = now.getFullYear()
const currentMonth = now.getMonth() + 1

const trainingClasses = computed(() => collectClassesFromTrainings(trainings.value))

const primaryTrainingClassId = computed(() => {
  const options = trainingClasses.value
  if (!options.length) return null
  if (options.length === 1) return options[0].id
  const monthPrefix = `${currentYear}-${String(currentMonth).padStart(2, '0')}`
  let bestId = options[0].id
  let bestCount = 0
  for (const opt of options) {
    const n = filterTrainingsByClassId(trainings.value, opt.id).filter((t) => {
      const k = String(t.class_date ?? '').split('T')[0]
      return k.startsWith(monthPrefix)
    }).length
    if (n > bestCount) {
      bestCount = n
      bestId = opt.id
    }
  }
  return bestId
})

const trainingsThisMonth = computed(() => {
  const classId = primaryTrainingClassId.value
  const list = classId ? filterTrainingsByClassId(trainings.value, classId) : trainings.value
  return trainingsInMonth(list, currentYear, currentMonth)
})

const academySessionsThisMonth = computed(() => {
  const key = `${currentYear}-${String(currentMonth).padStart(2, '0')}`
  const classId = primaryTrainingClassId.value
  if (classId) {
    return sessionsForClassMonth(academySessionsByClassMonth.value, classId, key)
  }
  return academySessionsByMonth.value[key] ?? 0
})

const frequencyThisMonth = computed(() =>
  attendanceFrequencyPercent(trainingsThisMonth.value, academySessionsThisMonth.value),
)

const showFrequencyThisMonth = computed(
  () => trainingClasses.value.length <= 1 && academySessionsThisMonth.value > 0,
)

const lastTraining = computed(() => lastTrainingDate(trainings.value))

const dayStreak = computed(() => {
  const sid = student.value?.id
  if (!sid) return 0
  return currentDayStreak(sid, attendanceLists.value)
})
const bestDayStreak = computed(() => {
  const sid = student.value?.id
  if (!sid) return 0
  return maxDayStreak(sid, attendanceLists.value)
})
const weekStreak = computed(() => currentWeekStreak(trainings.value))

const annualRank = computed(() => {
  const sid = student.value?.id
  if (!sid) return { rank: null as number | null, totalRanked: 0, totalTrainingsYear: 0 }
  return annualRankForStudent(attendanceLists.value, sid, currentYear)
})

const chartMonths = computed(() => lastMonthsTrainingCounts(trainings.value, 6))
const chartMax = computed(() => Math.max(1, ...chartMonths.value.map((m) => m.count)))

const timelineAsc = computed(() => {
  return [...graduations.value]
    .filter((g) => g.graduated_at)
    .sort((a, b) => {
      const da = String(a.graduated_at).split('T')[0]
      const db = String(b.graduated_at).split('T')[0]
      return da.localeCompare(db)
    })
})

function formatShortDate(iso: string | null | undefined) {
  if (!iso) return '—'
  const d = String(iso).split('T')[0]
  const [y, m, day] = d.split('-')
  if (!y || !m || !day) return iso
  return `${day}/${m}/${y}`
}

function beltName(b: { name?: string; group?: string } | null) {
  if (!b?.name) return '—'
  return b.group ? `${b.name} (${b.group})` : b.name
}

const photoUrl = computed(() => {
  if (student.value?.photo_url) return student.value.photo_url
  const p = student.value?.photo
  if (!p) return null
  if (String(p).startsWith('http')) return p
  return `/storage/${p}`
})

async function loadAll() {
  loading.value = true
  try {
    const [u, t, g, lists] = await Promise.all([
      axios.get('/api/auth/user'),
      axios.get('/api/auth/student/trainings'),
      axios.get('/api/auth/student/graduations'),
      axios.get('/api/attendance-lists'),
    ])
    user.value = u.data
    student.value = u.data.student || null
    const parsed = parseStudentTrainingsPayload(t.data)
    trainings.value = parsed.trainings
    academySessionsByMonth.value = parsed.academySessionsByMonth
    academySessionsByClassMonth.value = parsed.academySessionsByClassMonth
    graduations.value = Array.isArray(g.data) ? g.data : []
    attendanceLists.value = Array.isArray(lists.data) ? lists.data : []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function syncUserLocalStorage() {
  if (user.value) {
    try {
      localStorage.setItem('user', JSON.stringify(user.value))
    } catch {
      /* ignore */
    }
  }
}

async function onPhotoSelected(e: Event) {
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
    syncUserLocalStorage()
  } catch (err: any) {
    photoError.value =
      err.response?.data?.message || 'Não foi possível atualizar a foto. Tente outra imagem.'
  } finally {
    photoUploading.value = false
  }
}

onMounted(loadAll)
</script>

<template>
  <BaseLayout title="Início">
    <div class="dash">
      <div v-if="loading" class="dash__loading">Carregando seu painel…</div>

      <template v-else-if="!student">
        <p class="dash__muted">Não há aluno vinculado a esta conta.</p>
      </template>

      <template v-else>
        <header class="dash__hero">
          <div class="dash__hero-text">
            <p class="dash__hello">Olá, {{ student.name?.split(' ')[0] || 'atleta' }}</p>
            <h2 class="dash__title">Seu progresso</h2>
            <p class="dash__sub">
              Resumo do mês, sequências e ranking.
            </p>
            <div class="dash__hero-actions">
              <RouterLink to="/student/profile" class="dash__link">Perfil completo</RouterLink>
              <RouterLink to="/student/ranking" class="dash__link dash__link--secondary">
                Ranking
              </RouterLink>
            </div>
          </div>
          <div class="dash__hero-photo">
            <div class="dash__avatar-wrap">
              <img
                v-if="photoUrl"
                :src="photoUrl"
                alt=""
                class="dash__avatar"
              />
              <div v-else class="dash__avatar dash__avatar--placeholder">
                {{ (student.name || '?').charAt(0).toUpperCase() }}
              </div>
              <button
                type="button"
                class="dash__photo-btn"
                :disabled="photoUploading"
                @click="fileInputRef?.click()"
              >
                {{
                  photoUploading
                    ? '…'
                    : photoUrl
                      ? 'Alterar foto'
                      : 'Adicionar foto'
                }}
              </button>
              <input
                ref="fileInputRef"
                type="file"
                class="dash__file"
                accept="image/jpeg,image/png,image/webp,image/gif"
                @change="onPhotoSelected"
              />
            </div>
            <p class="dash__photo-hint">Foto opcional</p>
            <p v-if="photoError" class="dash__photo-err">{{ photoError }}</p>
          </div>
        </header>

        <section class="dash__grid">
          <article class="dash-card dash-card--accent">
            <p class="dash-card__label">Treinos este mês</p>
            <p class="dash-card__value">{{ trainingsThisMonth }}</p>
            <p v-if="showFrequencyThisMonth" class="dash-card__hint">
              {{ trainingsThisMonth }} de {{ academySessionsThisMonth }} aulas no mês
              <template v-if="trainingClasses.length === 1">
                ({{ trainingClasses[0].label }})
              </template>
              · {{ frequencyThisMonth }}% de frequência
            </p>
            <p v-else-if="trainingClasses.length > 1" class="dash-card__hint">
              {{ trainingsThisMonth }} treino(s) no mês. Veja a frequência por turma no histórico de treinos.
            </p>
            <p v-else class="dash-card__hint">
              Quantidade de listas de presença em que você apareceu no mês atual.
            </p>
          </article>

          <article class="dash-card">
            <p class="dash-card__label">Último treino</p>
            <p class="dash-card__value dash-card__value--md">
              {{ lastTraining ? formatShortDate(lastTraining) : '—' }}
            </p>
            <p class="dash-card__hint">Data da última presença registrada</p>
          </article>

          <article class="dash-card dash-card--fire">
            <p class="dash-card__label">Sequência atual</p>
            <p class="dash-card__value">
              <span class="dash-card__emoji" aria-hidden="true">🔥</span>
              {{ dayStreak }}
            </p>
            <p class="dash-card__hint">
              Conta dias em que havia treino(s) registrado(s) e você compareceu em pelo menos um.
              Dia sem lista de presença não quebra a sequência.
            </p>
            <div class="dash-streak-extra">
              <p>
                <span class="dash-streak-extra__label">Maior sequência de dias</span>
                <span class="dash-streak-extra__val">{{ bestDayStreak }}</span>
              </p>
              <p>
                <span class="dash-streak-extra__label">Semanas seguidas com treino</span>
                <span class="dash-streak-extra__val">{{ weekStreak }}</span>
              </p>
            </div>
          </article>

          <article class="dash-card">
            <p class="dash-card__label">Ranking {{ currentYear }}</p>
            <p v-if="annualRank.rank != null" class="dash-card__value">
              #{{ annualRank.rank }}
            </p>
            <p v-else class="dash-card__value dash-card__value--md">—</p>
            <p class="dash-card__hint">
              <template v-if="annualRank.totalRanked">
                entre {{ annualRank.totalRanked }} atletas · {{ annualRank.totalTrainingsYear }} treinos
              </template>
              <template v-else>Nenhum dado de ranking ainda</template>
            </p>
          </article>

          <article class="dash-card dash-card--wide">
            <p class="dash-card__label">Últimos 6 meses</p>
            <div class="dash-bars" aria-label="Gráfico de treinos por mês">
              <div v-for="m in chartMonths" :key="m.key" class="dash-bar">
                <div
                  class="dash-bar__fill"
                  :style="{ height: Math.round((m.count / chartMax) * 100) + '%' }"
                />
                <span class="dash-bar__n">{{ m.count }}</span>
                <span class="dash-bar__lbl">{{ m.label }}</span>
              </div>
            </div>
          </article>
        </section>

        <section class="dash-timeline-section dash-timeline-section--panel">
          <h3 class="dash-section-title">Linha do tempo de graduação</h3>
          <p class="dash-section-sub">
            Conquistas registradas pela equipe — a faixa atual é
            <strong>{{ beltName(student.belt) }}</strong>.
          </p>

          <div v-if="!timelineAsc.length" class="dash__muted dash__muted--box">
            Quando houver graduações cadastradas, elas aparecem aqui.
          </div>
          <ol v-else class="dash-timeline">
            <li v-for="(row, idx) in timelineAsc" :key="row.id" class="dash-timeline__item">
              <span class="dash-timeline__dot" :class="{ 'dash-timeline__dot--last': idx === timelineAsc.length - 1 }" />
              <div class="dash-timeline__card">
                <p class="dash-timeline__belt">{{ row.belt.slug !== 'white' && row.degree === 0 ? 'Graduado à' : '' }} Faixa {{ beltName(row.belt) }} {{ Number(row.degree) !== 0 ? ` - ${row.degree} ${row.degree === 1 ? 'grau' : 'graus'}` : '' }}</p>
                <p class="dash-timeline__date">{{ formatShortDate(row.graduated_at) }}</p>
              </div>
            </li>
          </ol>
        </section>
      </template>
    </div>
  </BaseLayout>
</template>

<style scoped lang="scss">
.dash {
  max-width: 1100px;
  margin: 0 auto;
}

.dash__loading {
  text-align: center;
  padding: 3rem 1rem;
  color: #6b7280;
}

.dash__muted {
  color: #6b7280;
  font-size: 0.9375rem;
}

.dash__muted--box {
  padding: 1.25rem;
  background: #f9fafb;
  border-radius: 0.75rem;
  border: 1px dashed #d1d5db;
}

.dash__hero {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  margin-bottom: 2rem;
  padding: 1.25rem;
  background: linear-gradient(135deg, #111827 0%, #1e3a5f 55%, #0f172a 100%);
  border-radius: 1rem;
  color: #fff;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.18);

  @media (min-width: 640px) {
    flex-direction: row;
    align-items: flex-start;
    justify-content: space-between;
    padding: 1.75rem 2rem;
  }
}

.dash__hello {
  margin: 0 0 0.25rem;
  font-size: 0.9rem;
  opacity: 0.9;
}

.dash__title {
  margin: 0 0 0.35rem;
  font-size: clamp(1.5rem, 4vw, 2rem);
  font-weight: 800;
  letter-spacing: -0.02em;
}

.dash__sub {
  margin: 0 0 1rem;
  font-size: 0.9rem;
  line-height: 1.45;
  opacity: 0.88;
  max-width: 36rem;
}

.dash__hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.dash__link {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  background: #fff;
  color: #111827;
  text-decoration: none;

  &:hover {
    background: #e5e7eb;
  }
}

.dash__link--secondary {
  background: rgba(255, 255, 255, 0.12);
  color: #fff;

  &:hover {
    background: rgba(255, 255, 255, 0.2);
  }
}

.dash__hero-photo {
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.35rem;
}

.dash__avatar-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.dash__avatar {
  width: 112px;
  height: 112px;
  border-radius: 1rem;
  object-fit: cover;
  border: 3px solid rgba(255, 255, 255, 0.35);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
}

.dash__avatar--placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.15);
  font-size: 2.5rem;
  font-weight: 800;
  color: #fff;
}

.dash__photo-btn {
  font-size: 0.8125rem;
  font-weight: 600;
  padding: 0.4rem 0.85rem;
  border-radius: 9999px;
  border: 1px solid rgba(255, 255, 255, 0.35);
  background: rgba(0, 0, 0, 0.2);
  color: #fff;
  cursor: pointer;

  &:disabled {
    opacity: 0.6;
    cursor: wait;
  }

  &:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.12);
  }
}

.dash__file {
  position: absolute;
  width: 0;
  height: 0;
  opacity: 0;
  pointer-events: none;
}

.dash__photo-hint {
  margin: 0;
  font-size: 0.6875rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.75);
  text-align: center;
  max-width: 12rem;
}

.dash__photo-err {
  margin: 0;
  font-size: 0.75rem;
  color: #fecaca;
  text-align: center;
  max-width: 12rem;
}

.dash__grid {
  display: grid;
  gap: 1rem;
  grid-template-columns: 1fr;

  @media (min-width: 640px) {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  @media (min-width: 960px) {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

.dash-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 1rem;
  padding: 1.15rem 1.25rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.dash-card--accent {
  border-color: #bfdbfe;
  background: linear-gradient(160deg, #fff 0%, #eff6ff 100%);
}

.dash-card--fire {
  border-color: #fed7aa;
  background: linear-gradient(160deg, #fff 0%, #fffbeb 100%);
}

.dash-card--wide {
  @media (min-width: 640px) {
    grid-column: span 2;
  }

  @media (min-width: 960px) {
    grid-column: span 2;
  }
}

.dash-card__label {
  margin: 0 0 0.35rem;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #6b7280;
}

.dash-card__value {
  margin: 0;
  font-size: 2.25rem;
  font-weight: 800;
  line-height: 1.1;
  color: #111827;
}

.dash-card__value--md {
  font-size: 1.75rem;
}

.dash-card__emoji {
  margin-right: 0.15rem;
}

.dash-card__hint {
  margin: 0.35rem 0 0;
  font-size: 0.8125rem;
  color: #6b7280;
  line-height: 1.35;
}

.dash-streak-extra {
  margin-top: 0.85rem;
  padding-top: 0.85rem;
  border-top: 1px solid rgba(251, 191, 36, 0.35);
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.dash-streak-extra p {
  margin: 0;
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  justify-content: space-between;
  gap: 0.35rem 0.75rem;
  font-size: 0.8125rem;
  color: #374151;
  line-height: 1.4;
}

.dash-streak-extra__label {
  font-weight: 600;
  color: #4b5563;
}

.dash-streak-extra__val {
  font-weight: 800;
  font-size: 1rem;
  color: #b45309;
  font-variant-numeric: tabular-nums;
}

.dash-bars {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 0.35rem;
  min-height: 140px;
  padding-top: 0.5rem;
}

.dash-bar {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  min-width: 0;
}

.dash-bar__fill {
  width: 100%;
  max-width: 36px;
  margin: 0 auto;
  min-height: 4px;
  border-radius: 6px 6px 0 0;
  background: linear-gradient(180deg, #60a5fa, #2563eb);
  align-self: flex-end;
}

.dash-bar__n {
  font-size: 0.75rem;
  font-weight: 700;
  color: #374151;
}

.dash-bar__lbl {
  font-size: 0.65rem;
  color: #6b7280;
  text-align: center;
  line-height: 1.2;
  text-transform: lowercase;
}

.dash-timeline-section {
  margin-top: 2.25rem;
}

.dash-timeline-section--panel {
  padding: 1.25rem 1.35rem 1.35rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 1rem;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
}

.dash-section-title {
  margin: 0 0 0.35rem;
  font-size: 1.25rem;
  font-weight: 800;
  color: #111827;
}

.dash-section-sub {
  margin: 0 0 1.25rem;
  font-size: 0.9rem;
  color: #6b7280;
  line-height: 1.45;
}

.dash-timeline {
  list-style: none;
  margin: 0;
  padding: 0;
  position: relative;
}

.dash-timeline__item {
  position: relative;
  padding-left: 2rem;
  padding-bottom: 1.35rem;

  &::before {
    content: '';
    position: absolute;
    left: 0.55rem;
    top: 0.5rem;
    bottom: -0.25rem;
    width: 2px;
    background: #e5e7eb;
  }

  &:last-child::before {
    bottom: 50%;
  }
}

.dash-timeline__dot {
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

.dash-timeline__dot--last {
  background: #059669;
  box-shadow: 0 0 0 2px #a7f3d0;
}

.dash-timeline__card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 0.75rem;
  padding: 0.85rem 1rem;
  box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
}

.dash-timeline__belt {
  margin: 0;
  font-weight: 700;
  font-size: 0.95rem;
  color: #111827;
}

.dash-timeline__date {
  margin: 0.2rem 0 0;
  font-size: 0.8125rem;
  color: #6b7280;
}

.dash-timeline__deg {
  margin: 0.35rem 0 0;
  font-size: 0.8125rem;
  color: #374151;
}

.dash-timeline__notes {
  margin: 0.35rem 0 0;
  font-size: 0.8125rem;
  color: #6b7280;
  line-height: 1.35;
}
</style>
