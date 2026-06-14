<script setup lang="ts">
import axios from 'axios'
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import BaseLayout from '../../../layouts/BaseLayout.vue'
import FormInput from '../../../components/form/FormInput.vue'
import FormSelect from '../../../components/form/FormSelect.vue'
import { toastDanger, toastSuccess } from '../../../utils/toast'

type TenantRow = {
  id: number
  name: string
  slug: string
  plan?: 'app' | 'digital'
  primary_domain?: string | null
  domains: Array<{ id: number; domain: string }>
}

const route = useRoute()
const router = useRouter()
const loading = ref(false)
const saving = ref(false)
const errors = ref<Record<string, string>>({})

const isEdit = computed(() => route.name === 'PlatformTenantsEdit')
const tenantId = computed(() => {
  const raw = route.params.id as string | undefined
  const id = Number(raw)
  return Number.isFinite(id) ? id : null
})

const form = reactive({
  name: '',
  slug: '',
  plan: 'digital' as TenantRow['plan'],
  domainsText: '',
  primaryDomain: '',
})

const planOptions = [
  { value: 'app', label: 'Tatameiro App — só gestão e portal (R$ 99/mês)' },
  { value: 'digital', label: 'Academia Digital — app + site no domínio próprio (R$ 199/mês)' },
]

const domainsHint = computed(() => {
  if (form.plan === 'digital') {
    return 'Informe o domínio próprio da academia (ex.: suaacademia.com.br). Auxiliamos na configuração do DNS.'
  }

  return 'Use o subdomínio Tatameiro para painel e portal (ex.: slug.tatameiro.com.br).'
})

function parseDomains(text: string) {
  return text
    .split(/[\n,]+/)
    .map((item) => item.trim().toLowerCase())
    .filter(Boolean)
}

const domainOptions = computed(() => parseDomains(form.domainsText))

watch(domainOptions, (domains) => {
  if (!domains.length) {
    form.primaryDomain = ''
    return
  }

  if (!domains.includes(form.primaryDomain)) {
    form.primaryDomain = domains.length === 1 ? domains[0] : ''
  }
})

async function loadTenant() {
  if (!tenantId.value) return

  loading.value = true
  try {
    const { data } = await axios.get(`/api/platform/tenants/${tenantId.value}`)
    form.name = data.name
    form.slug = data.slug
    form.plan = data.plan
    form.domainsText = (data.domains || []).map((domain: { domain: string }) => domain.domain).join('\n')
    form.primaryDomain = data.primary_domain || domainOptions.value[0] || ''
  } catch (error) {
    console.error(error)
    toastDanger('Erro ao carregar academia.')
  } finally {
    loading.value = false
  }
}

async function submit() {
  saving.value = true
  errors.value = {}

  const payload = {
    name: form.name,
    slug: form.slug,
    plan: form.plan,
    domains: parseDomains(form.domainsText),
    primary_domain: form.primaryDomain || null,
  }

  try {
    if (isEdit.value && tenantId.value) {
      await axios.put(`/api/platform/tenants/${tenantId.value}`, payload)
      toastSuccess('Academia atualizada.')
      router.push('/admin/platform/tenants')
      return
    }

    const { data } = await axios.post('/api/platform/tenants', payload)
    toastSuccess('Academia criada.')
    router.push(`/admin/platform/tenants/${data.id}/site-settings`)
  } catch (error: any) {
    if (error.response?.data?.errors) {
      errors.value = Object.fromEntries(
        Object.entries(error.response.data.errors).map(([key, value]) => [
          key,
          Array.isArray(value) ? String(value[0]) : String(value),
        ]),
      )
    } else {
      toastDanger(error.response?.data?.message || 'Erro ao salvar academia.')
    }
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  if (isEdit.value) {
    loadTenant()
  }
})
</script>

<template>
  <BaseLayout :title="isEdit ? 'Editar academia' : 'Nova academia'">
    <p v-if="loading">Carregando...</p>

    <form v-else class="platform-form" @submit.prevent="submit">
      <FormInput v-model="form.name" label="Nome da academia" :error="errors.name" />
      <FormInput v-model="form.slug" label="Slug (subdomínio)" :error="errors.slug" />
      <FormSelect v-model="form.plan" label="Plano" :options="planOptions" :error="errors.plan" />

      <label class="platform-form__field">
        <span>Domínios (um por linha)</span>
        <textarea
          v-model="form.domainsText"
          rows="4"
          :placeholder="form.plan === 'digital' ? 'suaacademia.com.br' : 'suaacademia.tatameiro.com.br'"
        />
        <small v-if="errors.domains">{{ errors.domains }}</small>
        <small v-else-if="errors['domains.0']">{{ errors['domains.0'] }}</small>
        <small v-else class="platform-form__hint">{{ domainsHint }}</small>
      </label>

      <FormSelect
        v-if="domainOptions.length > 1"
        v-model="form.primaryDomain"
        label="Domínio principal"
        :options="domainOptions.map((domain) => ({ value: domain, label: domain }))"
        :error="errors.primary_domain"
      />
      <p v-else-if="domainOptions.length === 1" class="platform-form__hint">
        Domínio principal: {{ domainOptions[0] }}
      </p>

      <div class="platform-form__actions">
        <button type="button" class="btn-secondary" @click="router.push('/admin/platform/tenants')">
          Voltar
        </button>
        <button type="submit" class="btn-primary" :disabled="saving">
          {{ saving ? 'Salvando...' : isEdit ? 'Salvar' : 'Criar academia' }}
        </button>
      </div>
    </form>
  </BaseLayout>
</template>

<style scoped>
.platform-form {
  max-width: 36rem;
  display: grid;
  gap: 1rem;
}

.platform-form__field {
  display: grid;
  gap: 0.35rem;
}

.platform-form__field textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #cbd5e1;
  border-radius: 0.5rem;
  font: inherit;
}

.platform-form__hint {
  color: #64748b;
}

.platform-form__field small {
  color: #dc2626;
}

.platform-form__actions {
  display: flex;
  gap: 0.75rem;
}
</style>
