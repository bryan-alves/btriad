<script setup lang="ts">
import axios from 'axios'
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import BaseLayout from '../../../layouts/BaseLayout.vue'
import FormInput from '../../../components/form/FormInput.vue'
import { toastDanger, toastSuccess } from '../../../utils/toast'

type Domain = {
  id: number
  domain: string
}

type TenantRow = {
  id: number
  name: string
  slug: string
  plan: 'app' | 'digital'
  domains: Domain[]
  site?: { academy_name?: string | null } | null
}

const router = useRouter()
const tenants = ref<TenantRow[]>([])
const loading = ref(false)
const savingAdmin = ref(false)
const adminTenant = ref<TenantRow | null>(null)
const adminErrors = ref<Record<string, string>>({})

const adminForm = reactive({
  name: '',
  username: '',
  password: '',
})

const planLabels: Record<TenantRow['plan'], string> = {
  app: 'Tatameiro App',
  digital: 'Academia Digital',
}

async function loadTenants() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/platform/tenants')
    tenants.value = Array.isArray(data) ? data : []
  } catch (error) {
    console.error(error)
    toastDanger('Erro ao carregar academias.')
  } finally {
    loading.value = false
  }
}

function domainsLabel(tenant: TenantRow) {
  return tenant.domains.map((domain) => domain.domain).join(', ')
}

function openAdminForm(tenant: TenantRow) {
  adminTenant.value = tenant
  adminForm.name = ''
  adminForm.username = ''
  adminForm.password = ''
  adminErrors.value = {}
}

function closeAdminForm() {
  adminTenant.value = null
}

async function submitAdmin() {
  if (!adminTenant.value) return

  savingAdmin.value = true
  adminErrors.value = {}

  try {
    await axios.post(`/api/platform/tenants/${adminTenant.value.id}/admins`, { ...adminForm })
    toastSuccess(`Admin criado para ${adminTenant.value.name}.`)
    closeAdminForm()
  } catch (error: any) {
    if (error.response?.data?.errors) {
      adminErrors.value = Object.fromEntries(
        Object.entries(error.response.data.errors).map(([key, value]) => [
          key,
          Array.isArray(value) ? String(value[0]) : String(value),
        ]),
      )
    } else {
      toastDanger(error.response?.data?.message || 'Erro ao criar admin.')
    }
  } finally {
    savingAdmin.value = false
  }
}

onMounted(loadTenants)
</script>

<template>
  <BaseLayout title="Academias" action="Nova academia" action-route="/admin/platform/tenants/create">
    <p v-if="loading">Carregando academias...</p>

    <div v-else class="platform-tenants">
      <p class="platform-tenants__intro">
        Gerencie as academias clientes, configure sites e crie usuários administradores.
      </p>

      <div v-if="!tenants.length" class="platform-tenants__empty">
        Nenhuma academia cadastrada ainda.
      </div>

      <div v-else class="platform-tenants__grid">
        <article v-for="tenant in tenants" :key="tenant.id" class="platform-card">
          <div class="platform-card__head">
            <div>
              <h2>{{ tenant.name }}</h2>
              <p class="platform-card__slug">{{ tenant.slug }}</p>
            </div>
            <span class="platform-card__plan">{{ planLabels[tenant.plan] }}</span>
          </div>

          <p class="platform-card__domains">{{ domainsLabel(tenant) }}</p>

          <div class="platform-card__actions">
            <button type="button" class="btn-primary" @click="router.push(`/admin/platform/tenants/${tenant.id}/site-settings`)">
              Configurar site
            </button>
            <button type="button" class="btn-secondary" @click="router.push(`/admin/platform/tenants/${tenant.id}/edit`)">
              Editar
            </button>
            <button type="button" class="btn-secondary" @click="openAdminForm(tenant)">
              Criar admin
            </button>
          </div>
        </article>
      </div>
    </div>

    <div v-if="adminTenant" class="platform-modal" role="dialog" aria-modal="true">
      <div class="platform-modal__backdrop" @click="closeAdminForm" />
      <form class="platform-modal__card" @submit.prevent="submitAdmin">
        <h2>Criar admin — {{ adminTenant.name }}</h2>
        <FormInput v-model="adminForm.name" label="Nome" :error="adminErrors.name" />
        <FormInput v-model="adminForm.username" label="Usuário" :error="adminErrors.username" />
        <FormInput v-model="adminForm.password" type="password" label="Senha" :error="adminErrors.password" />
        <div class="platform-modal__actions">
          <button type="button" class="btn-secondary" @click="closeAdminForm">Cancelar</button>
          <button type="submit" class="btn-primary" :disabled="savingAdmin">
            {{ savingAdmin ? 'Salvando...' : 'Criar admin' }}
          </button>
        </div>
      </form>
    </div>
  </BaseLayout>
</template>

<style scoped>
.platform-tenants__intro {
  margin: 0 0 1.5rem;
  color: #64748b;
}

.platform-tenants__empty {
  padding: 2rem;
  border: 1px dashed #cbd5e1;
  border-radius: 0.75rem;
  text-align: center;
  color: #64748b;
}

.platform-tenants__grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(18rem, 1fr));
  gap: 1rem;
}

.platform-card {
  padding: 1.25rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.875rem;
  background: #fff;
}

.platform-card__head {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: flex-start;
}

.platform-card h2 {
  margin: 0;
  font-size: 1.1rem;
}

.platform-card__slug {
  margin: 0.25rem 0 0;
  color: #64748b;
  font-size: 0.9rem;
}

.platform-card__plan {
  padding: 0.25rem 0.65rem;
  border-radius: 999px;
  background: #111827;
  color: #fff;
  font-size: 0.75rem;
  font-weight: 600;
  white-space: nowrap;
}

.platform-card__domains {
  margin: 1rem 0;
  color: #475569;
  font-size: 0.88rem;
  word-break: break-all;
}

.platform-card__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.platform-modal {
  position: fixed;
  inset: 0;
  z-index: 200;
  display: grid;
  place-items: center;
  padding: 1rem;
}

.platform-modal__backdrop {
  position: absolute;
  inset: 0;
  background: rgba(15, 23, 42, 0.45);
}

.platform-modal__card {
  position: relative;
  width: min(100%, 28rem);
  padding: 1.5rem;
  border-radius: 0.875rem;
  background: #fff;
  display: grid;
  gap: 1rem;
}

.platform-modal__card h2 {
  margin: 0;
  font-size: 1.1rem;
}

.platform-modal__actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}
</style>
