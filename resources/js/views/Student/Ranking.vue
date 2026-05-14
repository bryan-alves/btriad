<script setup>
import { computed, ref, watch, onMounted } from 'vue'
import axios from 'axios'
import BaseLayout from '../../layouts/BaseLayout.vue'
import Tabs from '../../components/tabs/Tabs.vue'

const loading = ref(true)

const monthLabels = [
  'Fev',
  'Mar',
  'Abr',
  'Mai',
  'Jun',
  'Jul',
  'Ago',
  'Set',
  'Out',
  'Nov',
  'Dez',
]

const attendanceLists = ref([])
const activeYearTab = ref('')
/** Mês 1–12 (string, alinhado aos ids dos toggles). */
const activeMonth = ref('')

function parseListDate(list) {
  const raw = list.class_date
  if (!raw) return null
  const d = String(raw).split('T')[0]
  const parts = d.split('-')
  const y = parseInt(parts[0], 10)
  const m = parseInt(parts[1], 10)
  if (!y || !m || m < 1 || m > 12) return null
  return { year: y, month: m }
}

function hasAttendanceInMonth(year, month) {
  return attendanceLists.value.some((list) => {
    const p = parseListDate(list)
    return p && p.year === year && p.month === month && list.students?.length
  })
}

const yearTabs = computed(() => {
  const years = new Set()
  attendanceLists.value.forEach((list) => {
    const p = parseListDate(list)
    if (p) years.add(p.year)
  })
  return Array.from(years)
    .sort((a, b) => b - a)
    .map((year) => ({ id: String(year), name: String(year) }))
})

watch(
  yearTabs,
  (tabs) => {
    if (!tabs.length) {
      activeYearTab.value = ''
      return
    }
    if (!tabs.some((t) => t.id === activeYearTab.value)) {
      activeYearTab.value = tabs[0].id
    }
  },
  { immediate: true },
)

/** Ao mudar o ano, escolhe um mês padrão: mês atual (se for esse ano) ou primeiro mês com treino. */
watch(activeYearTab, (yearStr) => {
  const y = parseInt(yearStr, 10)
  if (!y) {
    activeMonth.value = ''
    return
  }
  const now = new Date()
  if (y === now.getFullYear()) {
    const cm = now.getMonth() + 1
    if (hasAttendanceInMonth(y, cm)) {
      activeMonth.value = String(cm)
      return
    }
  }
  for (let m = 1; m <= 12; m += 1) {
    if (hasAttendanceInMonth(y, m)) {
      activeMonth.value = String(m)
      return
    }
  }
  activeMonth.value = '1'
})

const monthToggles = computed(() =>
  monthLabels.map((label, i) => ({
    id: String(i + 1),
    name: label,
  })),
)

const rankingRows = computed(() => {
  const year = parseInt(activeYearTab.value, 10)
  const month = parseInt(activeMonth.value, 10)
  if (!year || !month) return []

  const byStudent = {}

  attendanceLists.value.forEach((list) => {
    const parsed = parseListDate(list)
    if (!parsed || parsed.year !== year || parsed.month !== month || !list.students?.length) return

    list.students.forEach((student) => {
      if (!byStudent[student.id]) {
        byStudent[student.id] = {
          id: student.id,
          name: student.name,
          count: 0,
        }
      }
      byStudent[student.id].count += 1
    })
  })

  return Object.values(byStudent).sort((a, b) => {
    if (b.count !== a.count) return b.count - a.count
    return a.name.localeCompare(b.name, 'pt-BR')
  })
})

const selectedMonthLabel = computed(() => {
  const m = parseInt(activeMonth.value, 10)
  if (m < 1 || m > 12) return ''
  return monthLabels[m - 1]
})

async function loadRanking() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/attendance-lists')
    attendanceLists.value = Array.isArray(data) ? data : []
  } catch (error) {
    console.error(error)
    attendanceLists.value = []
  } finally {
    loading.value = false
  }
}

onMounted(loadRanking)
</script>

<template>
  <BaseLayout title="Ranking de treinos">
    <div class="ranking-page">
      <p class="intro">
        Alunos que mais participaram das listas de presença. Escolha o <strong>ano</strong> e o
        <strong>mês</strong>; o ranking conta só os treinos daquele mês.
      </p>

      <div v-if="loading" class="state">Carregando...</div>

      <div v-else-if="!yearTabs.length" class="state">
        Nenhum treino registrado ainda.
      </div>

      <template v-else>
        <p class="section-label">Ano</p>
        <Tabs
          :tabs="yearTabs"
          :selectedTab="activeYearTab"
          @tab="(id) => (activeYearTab = id)"
        />

        <p class="section-label section-label--month">Mês</p>
        <div class="month-toggle" role="tablist" aria-label="Filtrar por mês">
          <button
            v-for="tab in monthToggles"
            :key="tab.id"
            type="button"
            role="tab"
            :aria-selected="activeMonth === tab.id"
            :class="['month-toggle__btn', { 'month-toggle__btn--active': activeMonth === tab.id }]"
            @click="activeMonth = tab.id"
          >
            {{ tab.name }}
          </button>
        </div>

        <div class="tab-content">
          <p class="period-hint">
            {{ selectedMonthLabel }} de {{ activeYearTab }}
          </p>
          <div class="table-scroll">
            <table class="ranking-table">
              <thead>
                <tr>
                  <th class="col-rank">#</th>
                  <th class="col-name">Aluno</th>
                  <th class="col-count">Treinos</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in rankingRows" :key="item.id">
                  <td class="col-rank">{{ index + 1 }}</td>
                  <td class="col-name">{{ item.name }}</td>
                  <td class="col-count font-semibold">{{ item.count }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <p v-if="!rankingRows.length" class="empty-year">
            Nenhum treino registrado em {{ selectedMonthLabel }} de {{ activeYearTab }}.
          </p>
        </div>
      </template>
    </div>
  </BaseLayout>
</template>

<style scoped>
.ranking-page {
  max-width: 100%;
}

.intro {
  margin-bottom: 1rem;
  color: #4b5563;
  font-size: 0.9375rem;
  line-height: 1.5;
}

.state {
  padding: 2rem;
  text-align: center;
  color: #6b7280;
}

.section-label {
  margin: 0 0 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #6b7280;
}

.section-label--month {
  margin-top: 1.25rem;
}

.month-toggle {
  display: flex;
  flex-wrap: wrap;
  gap: 0;
  border: 1px solid #d1d5db;
  border-radius: 10px;
  overflow: hidden;
  background: #f3f4f6;
  width: fit-content;
  max-width: 100%;
}

.month-toggle__btn {
  flex: 1 1 auto;
  min-width: 2.75rem;
  padding: 0.5rem 0.35rem;
  border: none;
  border-right: 1px solid #d1d5db;
  background: #f9fafb;
  color: #374151;
  font-size: 0.8125rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s ease, color 0.15s ease;
}

.month-toggle__btn:last-child {
  border-right: none;
}

.month-toggle__btn:hover {
  background: #e5e7eb;
}

.month-toggle__btn--active {
  background: #111827;
  color: #fff;
}

.tab-content {
  margin-top: 1rem;
  background: white;
  border: 1px solid #ddd;
  padding: 16px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}

.period-hint {
  margin: 0 0 0.75rem;
  font-size: 0.875rem;
  color: #374151;
  font-weight: 600;
}

.ranking-table {
  border-collapse: collapse;
  font-size: 0.875rem;
  width: 100%;
  max-width: 32rem;
}

.ranking-table th,
.ranking-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #e5e7eb;
  text-align: left;
}

.ranking-table th {
  background: #f9fafb;
  font-weight: 600;
}

.ranking-table tbody tr:hover {
  background: #fafafa;
}

.col-rank {
  text-align: center;
  width: 3rem;
}

.col-name {
  min-width: 10rem;
}

.col-count {
  text-align: center;
  width: 5rem;
}

.empty-year {
  margin-top: 1rem;
  color: #6b7280;
  font-size: 0.875rem;
}
</style>
