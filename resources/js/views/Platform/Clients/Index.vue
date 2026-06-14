<script setup lang="ts">
import axios from 'axios'
import { computed, onMounted, reactive, ref } from 'vue'
import BaseLayout from '../../../layouts/BaseLayout.vue'
import FormInput from '../../../components/form/FormInput.vue'
import FormSelect from '../../../components/form/FormSelect.vue'
import { toastDanger, toastSuccess } from '../../../utils/toast'

type TenantOption = {
  id: number
  name: string
  slug: string
}

type PlatformClientRow = {
  id: number
  name: string
  logo_url?: string | null
  display_website_url?: string | null
  client_tenant_id?: number | null
  sort_order: number
}

const clients = ref<PlatformClientRow[]>([])
const tenants = ref<TenantOption[]>([])
const loading = ref(false)
const saving = ref(false)
const editingId = ref<number | null>(null)
const errors = ref<Record<string, string>>({})

const form = reactive({
  client_tenant_id: '' as string | number,
  sort_order: 0,
})

const usedTenantIds = computed(() => {
  return new Set(
    clients.value
      .filter((client) => client.id !== editingId.value)
      .map((client) => client.client_tenant_id)
      .filter(Boolean),
  )
})

const tenantOptions = computed(() =>
  tenants.value
    .filter((tenant) => !usedTenantIds.value.has(tenant.id))
    .map((tenant) => ({
      value: tenant.id,
      label: tenant.name,
    })),
)

function resetForm() {
  editingId.value = null
  form.client_tenant_id = ''
  form.sort_order = clients.value.length
  errors.value = {}
}

async function loadData() {
  loading.value = true
  try {
    const [clientsRes, tenantsRes] = await Promise.all([
      axios.get('/api/platform/clients'),
      axios.get('/api/platform/tenants'),
    ])
    clients.value = Array.isArray(clientsRes.data) ? clientsRes.data : []
    tenants.value = Array.isArray(tenantsRes.data) ? tenantsRes.data : []
  } catch (error) {
    console.error(error)
    toastDanger('Erro ao carregar clientes.')
  } finally {
    loading.value = false
  }
}

function editClient(client: PlatformClientRow) {
  editingId.value = client.id
  form.client_tenant_id = client.client_tenant_id ?? ''
  form.sort_order = client.sort_order
  errors.value = {}
}

async function submit() {
  saving.value = true
  errors.value = {}

  const payload = {
    client_tenant_id: Number(form.client_tenant_id),
    sort_order: Number(form.sort_order),
  }

  try {
    if (editingId.value) {
      await axios.put(`/api/platform/clients/${editingId.value}`, payload)
      toastSuccess('Cliente atualizado.')
    } else {
      await axios.post('/api/platform/clients', payload)
      toastSuccess('Cliente adicionado.')
    }

    resetForm()
    await loadData()
  } catch (error: any) {
    if (error.response?.data?.errors) {
      errors.value = Object.fromEntries(
        Object.entries(error.response.data.errors).map(([key, value]) => [
          key,
          Array.isArray(value) ? String(value[0]) : String(value),
        ]),
      )
    } else {
      toastDanger(error.response?.data?.message || 'Erro ao salvar cliente.')
    }
  } finally {
    saving.value = false
  }
}

async function deleteClient(client: PlatformClientRow) {
  if (!confirm(`Remover "${client.name}" da vitrine do Tatameiro?`)) return

  try {
    await axios.delete(`/api/platform/clients/${client.id}`)
    toastSuccess('Cliente removido.')
    if (editingId.value === client.id) {
      resetForm()
    }
    await loadData()
  } catch (error: any) {
    toastDanger(error.response?.data?.message || 'Erro ao remover cliente.')
  }
}

async function moveClient(index: number, direction: -1 | 1) {
  const target = index + direction
  if (target < 0 || target >= clients.value.length) return

  const items = [...clients.value]
  const [moved] = items.splice(index, 1)
  items.splice(target, 0, moved)
  clients.value = items

  try {
    await axios.post('/api/platform/clients/reorder', {
      order: items.map((item) => item.id),
    })
    toastSuccess('Ordem atualizada.')
  } catch (error) {
    toastDanger('Erro ao reordenar clientes.')
    await loadData()
  }
}

onMounted(loadData)
</script>

<template>
  <BaseLayout title="Clientes do site">
    <p v-if="loading">Carregando clientes...</p>

    <div v-else class="platform-clients">
      <p class="platform-clients__intro">
        Selecione a academia e a ordem de exibição na seção <strong>Clientes</strong> do site.
        Nome, logo e site vêm automaticamente da academia vinculada.
      </p>

      <div class="platform-clients__layout">
        <form class="platform-clients__form" @submit.prevent="submit">
          <h2>{{ editingId ? 'Editar' : 'Adicionar academia' }}</h2>

          <FormSelect
            v-model="form.client_tenant_id"
            label="Academia"
            :options="tenantOptions"
            :error="errors.client_tenant_id"
          />
          <FormInput v-model="form.sort_order" type="number" label="Ordem" :error="errors.sort_order" />

          <div class="platform-clients__actions">
            <button v-if="editingId" type="button" class="btn-secondary" @click="resetForm">Cancelar</button>
            <button type="submit" class="btn-primary" :disabled="saving || !form.client_tenant_id">
              {{ saving ? 'Salvando...' : editingId ? 'Salvar' : 'Adicionar' }}
            </button>
          </div>
        </form>

        <section class="platform-clients__list">
          <h2>Exibidos no site ({{ clients.length }})</h2>

          <p v-if="!clients.length" class="platform-clients__empty">Nenhuma academia selecionada ainda.</p>

          <article v-for="(client, index) in clients" :key="client.id" class="platform-client-card">
            <div class="platform-client-card__head">
              <img
                v-if="client.logo_url"
                :src="client.logo_url"
                :alt="`Logo ${client.name}`"
                class="platform-client-card__logo"
              >
              <div>
                <strong>{{ client.name }}</strong>
                <p v-if="client.display_website_url">{{ client.display_website_url }}</p>
                <small>Ordem {{ client.sort_order }}</small>
              </div>
            </div>
            <div class="platform-client-card__actions">
              <button type="button" class="btn-secondary" :disabled="index === 0" @click="moveClient(index, -1)">↑</button>
              <button type="button" class="btn-secondary" :disabled="index === clients.length - 1" @click="moveClient(index, 1)">↓</button>
              <button type="button" class="btn-secondary" @click="editClient(client)">Editar</button>
              <button type="button" class="btn-danger" @click="deleteClient(client)">Remover</button>
            </div>
          </article>
        </section>
      </div>
    </div>
  </BaseLayout>
</template>

<style scoped>
.platform-clients__intro {
  margin: 0 0 1.5rem;
  color: #64748b;
}

.platform-clients__layout {
  display: grid;
  grid-template-columns: minmax(16rem, 20rem) minmax(0, 1fr);
  gap: 1.5rem;
  align-items: start;
}

.platform-clients__form,
.platform-clients__list {
  padding: 1.25rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.875rem;
  background: #fff;
}

.platform-clients__form {
  display: grid;
  gap: 1rem;
}

.platform-clients__form h2,
.platform-clients__list h2 {
  margin: 0 0 0.5rem;
  font-size: 1.05rem;
}

.platform-clients__actions {
  display: flex;
  gap: 0.75rem;
}

.platform-clients__empty {
  color: #64748b;
}

.platform-client-card {
  display: grid;
  gap: 0.75rem;
  padding: 1rem 0;
  border-top: 1px solid #e2e8f0;
}

.platform-client-card:first-of-type {
  border-top: 0;
  padding-top: 0;
}

.platform-client-card__head {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.platform-client-card__logo {
  width: 7rem;
  height: 2.5rem;
  object-fit: contain;
  background: #0a0a0a;
  border-radius: 0.5rem;
  padding: 0.35rem;
}

.platform-client-card__head p,
.platform-client-card__head small {
  margin: 0.15rem 0 0;
  color: #64748b;
  word-break: break-all;
}

.platform-client-card__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

@media (max-width: 900px) {
  .platform-clients__layout {
    grid-template-columns: 1fr;
  }
}
</style>
