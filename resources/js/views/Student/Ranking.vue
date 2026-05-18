<script setup>
import { computed, ref, watch, onMounted } from 'vue'
import axios from 'axios'
import BaseLayout from '../../layouts/BaseLayout.vue'
import Tabs from '../../components/tabs/Tabs.vue'
import {
  RANKING_FULL_YEAR_MONTH,
  RANKING_MONTH_LABELS,
  isRankingFullYear,
  monthsWithAttendanceInYear,
  parseRankingPeriodsPayload,
  pickDefaultRankingMonth,
  rankPodiumMeta,
} from '../../utils/ranking'

const loading = ref(true)
const rankingLoading = ref(false)
const periods = ref({ years: [], months_by_year: {} })
const rankingRows = ref([])
const hasTrainingInPeriod = ref(false)
const activeYearTab = ref('')
const activeMonth = ref('')

const monthLabels = RANKING_MONTH_LABELS

const yearTabs = computed(() =>
  periods.value.years.map((year) => ({
    id: String(year),
    name: String(year),
  })),
)

const monthsInActiveYear = computed(() => {
  const year = parseInt(activeYearTab.value, 10)
  if (!year) return []
  return monthsWithAttendanceInYear(periods.value, year)
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

watch(activeYearTab, (yearStr) => {
  const year = parseInt(yearStr, 10)
  if (!year) {
    activeMonth.value = ''
    return
  }
  if (activeMonth.value === String(RANKING_FULL_YEAR_MONTH)) {
    return
  }
  const months = monthsInActiveYear.value
  const current = parseInt(activeMonth.value, 10)
  if (current && months.includes(current)) {
    return
  }
  activeMonth.value = pickDefaultRankingMonth(year, months)
})

const monthToggles = computed(() => {
  const yearHasTraining = monthsInActiveYear.value.length > 0
  const toggles = [
    {
      id: String(RANKING_FULL_YEAR_MONTH),
      name: 'Ano todo',
      disabled: !yearHasTraining,
    },
  ]
  for (let i = 0; i < monthLabels.length; i += 1) {
    const monthNum = i + 1
    toggles.push({
      id: String(monthNum),
      name: monthLabels[i],
      disabled: !monthsInActiveYear.value.includes(monthNum),
    })
  }
  return toggles
})

const selectedMonthLabel = computed(() => {
  const m = parseInt(activeMonth.value, 10)
  if (isRankingFullYear(m)) return 'Ano todo'
  if (m < 1 || m > 12) return ''
  return monthLabels[m - 1]
})

const isFullYearRanking = computed(() =>
  isRankingFullYear(parseInt(activeMonth.value, 10)),
)

const showRankingTable = computed(
  () => !rankingLoading.value && hasTrainingInPeriod.value && rankingRows.value.length > 0,
)

async function loadPeriods() {
  const { data } = await axios.get('/api/attendance-lists/ranking-periods')
  periods.value = parseRankingPeriodsPayload(data)
}

async function loadRankingForPeriod() {
  const year = parseInt(activeYearTab.value, 10)
  const month = parseInt(activeMonth.value, 10)
  if (!year || activeMonth.value === '') {
    rankingRows.value = []
    hasTrainingInPeriod.value = false
    return
  }

  if (!isRankingFullYear(month) && !monthsInActiveYear.value.includes(month)) {
    rankingRows.value = []
    hasTrainingInPeriod.value = false
    return
  }

  if (isRankingFullYear(month) && !monthsInActiveYear.value.length) {
    rankingRows.value = []
    hasTrainingInPeriod.value = false
    return
  }

  rankingLoading.value = true
  try {
    const { data } = await axios.get('/api/attendance-lists/ranking', {
      params: { year, month: isRankingFullYear(month) ? RANKING_FULL_YEAR_MONTH : month },
    })
    rankingRows.value = Array.isArray(data?.ranking) ? data.ranking : []
    hasTrainingInPeriod.value = Boolean(data?.has_training)
  } catch (error) {
    console.error(error)
    rankingRows.value = []
    hasTrainingInPeriod.value = false
  } finally {
    rankingLoading.value = false
  }
}

async function loadRanking() {
  loading.value = true
  try {
    await loadPeriods()
  } catch (error) {
    console.error(error)
    periods.value = { years: [], months_by_year: {} }
  } finally {
    loading.value = false
  }
}

watch([activeYearTab, activeMonth], () => {
  if (!loading.value) {
    loadRankingForPeriod()
  }
})

onMounted(async () => {
  await loadRanking()
  await loadRankingForPeriod()
})

function selectMonth(monthId) {
  const monthNum = parseInt(monthId, 10)
  if (isRankingFullYear(monthNum)) {
    if (!monthsInActiveYear.value.length) return
    activeMonth.value = monthId
    return
  }
  if (!monthsInActiveYear.value.includes(monthNum)) return
  activeMonth.value = monthId
}
</script>

<template>
  <BaseLayout title="Ranking de treinos">
    <div class="ranking-page">
      <p class="intro">
        Alunos que mais treinaram no período. Escolha o <strong>ano</strong> e o
        <strong>mês</strong> ou <strong>ano todo</strong>. Cada dia com presença conta uma vez (várias turmas no mesmo dia não somam em dobro).
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
            :disabled="tab.disabled"
            :class="[
              'month-toggle__btn',
              {
                'month-toggle__btn--active': activeMonth === tab.id,
                'month-toggle__btn--disabled': tab.disabled,
              },
            ]"
            @click="selectMonth(tab.id)"
          >
            {{ tab.name }}
          </button>
        </div>

        <div class="tab-content">
          <p class="period-hint">
            <template v-if="isFullYearRanking">Ano todo de {{ activeYearTab }}</template>
            <template v-else>{{ selectedMonthLabel }} de {{ activeYearTab }}</template>
          </p>

          <div v-if="rankingLoading" class="state state--inline">Carregando ranking...</div>

          <template v-else>
            <div v-if="showRankingTable" class="table-scroll">
              <table class="ranking-table">
                <thead>
                  <tr>
                    <th class="col-rank">#</th>
                    <th class="col-name">Aluno</th>
                    <th class="col-count">Treinos</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(item, index) in rankingRows"
                    :key="item.id"
                    :class="rankPodiumMeta(index + 1)?.className"
                  >
                    <td class="col-rank">
                      <span v-if="rankPodiumMeta(index + 1)" class="rank-podium__badge">
                        <span class="rank-podium__icon" aria-hidden="true">{{ rankPodiumMeta(index + 1).icon }}</span>
                        <span class="rank-podium__pos">{{ index + 1 }}º</span>
                      </span>
                      <span v-else>{{ index + 1 }}</span>
                    </td>
                    <td class="col-name">{{ item.name }}</td>
                    <td class="col-count font-semibold">{{ item.count }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <p v-if="!hasTrainingInPeriod || !rankingRows.length" class="empty-year">
              <template v-if="isFullYearRanking">
                Nenhum treino registrado em {{ activeYearTab }}.
              </template>
              <template v-else>
                Nenhum treino registrado em {{ selectedMonthLabel }} de {{ activeYearTab }}.
              </template>
            </p>
          </template>
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

.state--inline {
  padding: 1rem 0;
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

.month-toggle__btn:first-child {
  min-width: 4.5rem;
}

.month-toggle__btn:last-child {
  border-right: none;
}

.month-toggle__btn:hover:not(:disabled) {
  background: #e5e7eb;
}

.month-toggle__btn--active {
  background: #111827;
  color: #fff;
}

.month-toggle__btn--disabled,
.month-toggle__btn:disabled {
  background: #f3f4f6;
  color: #9ca3af;
  cursor: not-allowed;
  opacity: 0.65;
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

.rank-podium--gold {
  background: linear-gradient(90deg, #fffbeb 0%, #ffffff 100%);
}

.rank-podium--silver {
  background: linear-gradient(90deg, #f8fafc 0%, #ffffff 100%);
}

.rank-podium--bronze {
  background: linear-gradient(90deg, #fff7ed 0%, #ffffff 100%);
}

.rank-podium__badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  font-weight: 700;
}

.rank-podium__icon {
  font-size: 1.125rem;
  line-height: 1;
}

.rank-podium--gold .rank-podium__pos {
  color: #b45309;
}

.rank-podium--silver .rank-podium__pos {
  color: #475569;
}

.rank-podium--bronze .rank-podium__pos {
  color: #c2410c;
}

.col-rank {
  text-align: center;
  width: 4.5rem;
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
