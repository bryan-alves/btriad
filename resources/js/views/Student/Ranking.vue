<script setup>
import { computed, ref, watch, onMounted } from 'vue'
import axios from 'axios'
import BaseLayout from '../../layouts/BaseLayout.vue'
import Tabs from '../../components/tabs/Tabs.vue'

const monthLabels = [
  'Jan',
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

const loading = ref(true)
const attendanceLists = ref([])
const activeYearTab = ref('')

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

const rankingRows = computed(() => {
  const year = parseInt(activeYearTab.value, 10)
  if (!year) return []

  const byStudent = {}

  attendanceLists.value.forEach((list) => {
    const parsed = parseListDate(list)
    if (!parsed || parsed.year !== year || !list.students?.length) return

    const monthIdx = parsed.month - 1

    list.students.forEach((student) => {
      if (!byStudent[student.id]) {
        byStudent[student.id] = {
          id: student.id,
          name: student.name,
          months: Array(12).fill(0),
          total: 0,
        }
      }
      const row = byStudent[student.id]
      row.months[monthIdx] += 1
      row.total += 1
    })
  })

  return Object.values(byStudent).sort((a, b) => {
    if (b.total !== a.total) return b.total - a.total
    return a.name.localeCompare(b.name, 'pt-BR')
  })
})

/** Totais por mês no ano selecionado (soma de todos os alunos). */
const monthTotals = computed(() => {
  const totals = Array(12).fill(0)
  for (const row of rankingRows.value) {
    for (let i = 0; i < 12; i += 1) {
      totals[i] += row.months[i]
    }
  }
  return totals
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
        Alunos que mais participaram das listas de presença. Use as abas para filtrar por ano; cada coluna é a quantidade de treinos naquele mês.
      </p>

      <div v-if="loading" class="state">Carregando...</div>

      <div v-else-if="!yearTabs.length" class="state">
        Nenhum treino registrado ainda.
      </div>

      <template v-else>
        <Tabs
          :tabs="yearTabs"
          :selectedTab="activeYearTab"
          @tab="(id) => (activeYearTab = id)"
        />

        <div class="tab-content">
          <div class="table-scroll">
            <table class="ranking-table">
              <thead>
                <tr>
                  <th class="col-rank">#</th>
                  <th class="col-name">Aluno</th>
                  <th v-for="(label, idx) in monthLabels" :key="label" class="month-cell">
                    {{ label }}
                  </th>
                  <th class="total-col">Total</th>
                </tr>
                <tr class="subhead">
                  <th class="col-rank" />
                  <th class="col-name text-left text-gray-600 font-normal">
                    Todos (soma)
                  </th>
                  <th
                    v-for="(label, idx) in monthLabels"
                    :key="'sum-' + label"
                    class="month-cell text-gray-600 font-normal"
                  >
                    {{ monthTotals[idx] || '—' }}
                  </th>
                  <th class="total-col text-gray-700 font-semibold">
                    {{ monthTotals.reduce((a, b) => a + b, 0) }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in rankingRows" :key="item.id">
                  <td class="col-rank">{{ index + 1 }}</td>
                  <td class="col-name">{{ item.name }}</td>
                  <td
                    v-for="(label, idx) in monthLabels"
                    :key="item.id + '-' + label"
                    class="month-cell"
                  >
                    {{ item.months[idx] || '—' }}
                  </td>
                  <td class="total-col font-semibold">{{ item.total }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <p v-if="!rankingRows.length" class="empty-year">
            Nenhum treino registrado em {{ activeYearTab }}.
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

.tab-content {
  margin-top: 1rem;
  background: white;
  border: 1px solid #ddd;
  padding: 16px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}

.ranking-table {
  border-collapse: collapse;
  font-size: 0.875rem;
}

.ranking-table th,
.ranking-table td {
  padding: 10px 8px;
  border-bottom: 1px solid #e5e7eb;
  text-align: center;
  white-space: nowrap;
}

.ranking-table th {
  background: #f9fafb;
  font-weight: 600;
}

.ranking-table tbody tr:hover {
  background: #fafafa;
}

.subhead th {
  background: #f3f4f6;
  border-bottom: 2px solid #d1d5db;
  font-size: 0.8125rem;
}

.col-rank {
  text-align: center;
  min-width: 2.5rem;
}

.col-name {
  text-align: left;
  min-width: 10rem;
  max-width: 14rem;
  overflow: hidden;
  text-overflow: ellipsis;
}

.month-cell {
  min-width: 2.75rem;
}

.total-col {
  min-width: 3.5rem;
  background: #f9fafb;
}

.empty-year {
  margin-top: 1rem;
  color: #6b7280;
  font-size: 0.875rem;
}
</style>
