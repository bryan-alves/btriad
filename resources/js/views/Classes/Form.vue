<script setup lang="ts">
import axios from 'axios'
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute } from 'vue-router'
import BaseLayout from '../../layouts/BaseLayout.vue'
import FormInput from '../../components/form/FormInput.vue'
import FormSelect from '../../components/form/FormSelect.vue'
import { toastDanger, toastSuccess } from '../../utils/toast'
import {
  detectScheduleConflicts,
  emptyScheduleSlot,
  normalizeScheduleSlots,
  type ScheduleSlot,
  type SchoolClassRow,
  WEEKDAY_OPTIONS,
} from '../../utils/classSchedule'

const route = useRoute()
const loading = ref(false)
const isEdit = ref(false)
const allClasses = ref<SchoolClassRow[]>([])

const form = reactive({
  name: '',
  type: 'kids',
  schedule_slots: [emptyScheduleSlot()] as ScheduleSlot[],
  active: true,
})

const errors = ref<Record<string, string>>({})

const scheduleConflicts = computed(() =>
  detectScheduleConflicts(
    form.schedule_slots,
    allClasses.value,
    isEdit.value ? Number(route.params.id) : null,
  ),
)

const conflictsBySlot = computed(() => {
  const map: Record<number, string[]> = {}

  scheduleConflicts.value.forEach((conflict) => {
    if (!map[conflict.slot_index]) {
      map[conflict.slot_index] = []
    }
    map[conflict.slot_index].push(conflict.message)
  })

  return map
})

function validate() {
  const e: Record<string, string> = {}

  if (!form.name.trim()) e.name = 'Nome da turma é obrigatório'
  if (!form.type) e.type = 'Tipo da turma é obrigatório'

  const normalized = normalizeScheduleSlots(form.schedule_slots)
  if (!normalized.length) {
    e.schedule_slots = 'Adicione ao menos um dia e horário.'
  }

  normalized.forEach((slot, index) => {
    if (!slot.start_time) {
      e[`schedule_slots.${index}.start_time`] = 'Horário de início é obrigatório'
    }
  })

  errors.value = e
  return Object.keys(e).length === 0
}

function addScheduleSlot() {
  form.schedule_slots.push(emptyScheduleSlot())
}

function removeScheduleSlot(index: number) {
  if (form.schedule_slots.length === 1) return
  form.schedule_slots.splice(index, 1)
}

async function loadAllClasses() {
  try {
    const { data } = await axios.get('/api/classes')
    allClasses.value = Array.isArray(data) ? data : []
  } catch (error) {
    console.error(error)
    allClasses.value = []
  }
}

async function submit() {
  if (!validate()) return
  loading.value = true

  const payload = {
    name: form.name.trim(),
    type: form.type,
    schedule_slots: normalizeScheduleSlots(form.schedule_slots).map((slot) => ({
      weekday: slot.weekday,
      start_time: slot.start_time,
      end_time: slot.end_time || null,
    })),
    active: form.active,
  }

  try {
    if (isEdit.value) {
      await axios.put(`/api/classes/${route.params.id}`, payload)
      toastSuccess('Turma atualizada com sucesso!')
    } else {
      await axios.post('/api/classes', payload)
      toastSuccess('Turma cadastrada com sucesso!')
      form.name = ''
      form.type = 'kids'
      form.schedule_slots = [emptyScheduleSlot()]
      form.active = true
    }
  } catch (e) {
    toastDanger(isEdit.value ? 'Erro ao atualizar turma' : 'Erro ao cadastrar turma')
    console.log(e)
  }

  loading.value = false
}

async function getClass() {
  try {
    const { data } = await axios.get(`/api/classes/${route.params.id}`)

    form.name = data.name
    form.type = data.type || 'kids'
    form.schedule_slots = normalizeScheduleSlots(data.schedule_slots)
    form.active = data.active

    if (!form.schedule_slots.length) {
      form.schedule_slots = [emptyScheduleSlot()]
    }
  } catch (error) {
    console.error(error)
    toastDanger('Erro ao carregar turma')
  }
}

onMounted(async () => {
  isEdit.value = !!route.params.id
  await loadAllClasses()

  if (isEdit.value) {
    await getClass()
  }
})
</script>

<template>
  <BaseLayout :title="isEdit ? 'Editar Turma' : 'Nova Turma'">
    <div class="mx-auto max-w-3xl">
      <form @submit.prevent="submit" class="space-y-6">
        <div class="space-y-4">
          <h2 class="text-xl font-bold">Informações da Turma</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <FormInput v-model="form.name" label="Nome da turma" :error="errors.name" />
            <FormSelect
              v-model="form.type"
              :options="[
                { value: 'kids', label: 'Kids' },
                { value: 'adult', label: 'Adulto' },
              ]"
              label="Tipo da turma"
              placeholder="Selecione"
              :error="errors.type"
            />
          </div>

          <div class="class-slots">
            <div class="class-slots__head">
              <h3 class="class-slots__title">Dias e horários</h3>
              <button type="button" class="class-slots__add" @click="addScheduleSlot">Adicionar horário</button>
            </div>

            <p v-if="errors.schedule_slots" class="class-slots__error">{{ errors.schedule_slots }}</p>

            <div
              v-for="(slot, index) in form.schedule_slots"
              :key="index"
              class="class-slots__row"
              :class="{ 'class-slots__row--conflict': conflictsBySlot[index]?.length }"
            >
              <FormSelect
                v-model="slot.weekday"
                :options="WEEKDAY_OPTIONS"
                label="Dia da semana"
                placeholder="Selecione"
              />
              <FormInput
                v-model="slot.start_time"
                type="time"
                label="Início"
                :error="errors[`schedule_slots.${index}.start_time`]"
              />
              <FormInput v-model="slot.end_time" type="time" label="Término" />
              <button
                type="button"
                class="class-slots__remove"
                :disabled="form.schedule_slots.length === 1"
                @click="removeScheduleSlot(index)"
              >
                Remover
              </button>

              <p v-for="message in conflictsBySlot[index] || []" :key="message" class="class-slots__warn">
                {{ message }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <input id="active" type="checkbox" v-model="form.active" class="h-4 w-4 rounded border-gray-300" />
            <label for="active" class="font-medium">Turma ativa</label>
          </div>
        </div>

        <div style="display: flex; justify-content: space-between; gap: 10px;">
          <router-link to="/admin/classes" class="btn-primary">Voltar</router-link>
          <button
            :disabled="loading"
            class="bg-blue-600 text-white px-6 py-3 rounded font-semibold hover:bg-blue-700 disabled:opacity-50"
          >
            {{ loading ? 'Salvando...' : isEdit ? 'Atualizar Turma' : 'Cadastrar Turma' }}
          </button>
        </div>
      </form>
    </div>
  </BaseLayout>
</template>

<style scoped lang="scss">
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

.class-slots {
  display: grid;
  gap: 0.75rem;
}

.class-slots__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.class-slots__title {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
}

.class-slots__add,
.class-slots__remove {
  border: none;
  border-radius: 6px;
  padding: 8px 12px;
  font-size: 0.875rem;
  cursor: pointer;
}

.class-slots__add {
  background: #e5e7eb;
  color: #111827;
}

.class-slots__remove {
  background: #fee2e2;
  color: #991b1b;
  align-self: end;

  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
}

.class-slots__row {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr)) auto;
  gap: 0.75rem;
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  background: #fff;
}

.class-slots__row--conflict {
  border-color: #f59e0b;
  background: #fffbeb;
}

.class-slots__warn {
  grid-column: 1 / -1;
  margin: 0;
  font-size: 0.8125rem;
  color: #b45309;
}

.class-slots__error {
  margin: 0;
  color: #dc2626;
  font-size: 0.875rem;
}

@media (max-width: 768px) {
  .class-slots__row {
    grid-template-columns: 1fr;
  }

  .class-slots__remove {
    width: 100%;
  }
}
</style>
