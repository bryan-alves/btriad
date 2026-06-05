<script setup lang="ts">
import axios from 'axios'
import { onMounted, reactive, ref } from 'vue'
import BaseLayout from '../../layouts/BaseLayout.vue'
import FormInput from '../../components/form/FormInput.vue'

type Domain = {
  id: number
  domain: string
}

type Site = {
  academy_name?: string
  primary_color?: string
  logo_path?: string | null
  whatsapp?: string | null
  instagram?: string | null
  address?: string | null
  active?: boolean
}

type TenantRow = {
  id: number
  name: string
  slug: string
  domains: Domain[]
  site?: Site | null
}

const sites = ref<TenantRow[]>([])
const selected = ref<TenantRow | null>(null)
const loading = ref(false)
const saving = ref(false)
const errors = ref<Record<string, string>>({})

const form = reactive({
  name: '',
  academy_name: '',
  primary_color: '#c41e3a',
  logo_path: '',
  whatsapp: '',
  instagram: '',
  address: '',
  active: true,
})

function selectSite(site: TenantRow) {
  selected.value = site
  form.name = site.name
  form.academy_name = site.site?.academy_name || site.name
  form.primary_color = site.site?.primary_color || '#c41e3a'
  form.logo_path = site.site?.logo_path || ''
  form.whatsapp = site.site?.whatsapp || ''
  form.instagram = site.site?.instagram || ''
  form.address = site.site?.address || ''
  form.active = site.site?.active !== false
  errors.value = {}
}

async function loadSites() {
  loading.value = true

  try {
    const { data } = await axios.get('/api/site-settings')
    sites.value = Array.isArray(data) ? data : []
    if (sites.value.length) {
      selectSite(sites.value[0])
    }
  } catch (error) {
    console.error(error)
    alert('Erro ao carregar sites.')
  } finally {
    loading.value = false
  }
}

async function submit() {
  if (!selected.value) return

  saving.value = true
  errors.value = {}

  try {
    const { data } = await axios.put(`/api/site-settings/${selected.value.id}`, {
      name: form.name,
      academy_name: form.academy_name,
      primary_color: form.primary_color,
      logo_path: form.logo_path || null,
      whatsapp: form.whatsapp || null,
      instagram: form.instagram || null,
      address: form.address || null,
      active: form.active,
    })

    const index = sites.value.findIndex((site) => site.id === data.id)
    if (index >= 0) {
      sites.value[index] = data
      selectSite(data)
    }

    alert('Configurações salvas com sucesso.')
  } catch (error: any) {
    if (error.response?.data?.errors) {
      errors.value = Object.fromEntries(
        Object.entries(error.response.data.errors).map(([key, value]) => [
          key,
          Array.isArray(value) ? String(value[0]) : String(value),
        ]),
      )
    } else {
      alert(error.response?.data?.message || 'Erro ao salvar configurações.')
      console.error(error)
    }
  } finally {
    saving.value = false
  }
}

onMounted(loadSites)
</script>

<template>
  <BaseLayout title="Sites cadastrados">
    <div class="site-settings">
      <aside class="site-settings__list">
        <p v-if="loading">Carregando sites...</p>
        <button
          v-for="site in sites"
          :key="site.id"
          type="button"
          class="site-settings__item"
          :class="{ 'site-settings__item--active': selected?.id === site.id }"
          @click="selectSite(site)"
        >
          <strong>{{ site.name }}</strong>
          <span>{{ site.domains.map((domain) => domain.domain).join(', ') }}</span>
        </button>
      </aside>

      <form v-if="selected" class="site-settings__form" @submit.prevent="submit">
        <h2>{{ selected.name }}</h2>
        <p class="site-settings__domains">
          Domínios: {{ selected.domains.map((domain) => domain.domain).join(', ') }}
        </p>

        <FormInput v-model="form.name" label="Nome do tenant" :error="errors.name" />
        <FormInput v-model="form.academy_name" label="Nome exibido no site" :error="errors.academy_name" />
        <FormInput v-model="form.primary_color" label="Cor primária" placeholder="#c97716" :error="errors.primary_color" />
        <FormInput v-model="form.logo_path" label="Logo no public/" placeholder="apjiujitsu-logo.svg" :error="errors.logo_path" />
        <FormInput v-model="form.whatsapp" label="WhatsApp" placeholder="https://wa.me/..." :error="errors.whatsapp" />
        <FormInput v-model="form.instagram" label="Instagram" placeholder="https://instagram.com/..." :error="errors.instagram" />

        <label class="site-settings__field">
          <span>Endereço</span>
          <textarea v-model="form.address" rows="4" />
          <small v-if="errors.address">{{ errors.address }}</small>
        </label>

        <label class="site-settings__check">
          <input v-model="form.active" type="checkbox" />
          Site ativo
        </label>

        <button type="submit" class="btn-primary" :disabled="saving">
          {{ saving ? 'Salvando...' : 'Salvar configurações' }}
        </button>
      </form>
    </div>
  </BaseLayout>
</template>

<style scoped lang="scss">
.site-settings {
  display: grid;
  grid-template-columns: minmax(220px, 320px) minmax(0, 1fr);
  gap: 1rem;
}

.site-settings__list,
.site-settings__form {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 1rem;
}

.site-settings__list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  align-self: start;
}

.site-settings__item {
  text-align: left;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #fff;
  padding: 0.75rem;
  cursor: pointer;

  span {
    display: block;
    margin-top: 0.25rem;
    color: #6b7280;
    font-size: 0.875rem;
  }
}

.site-settings__item--active {
  border-color: #111827;
  background: #f9fafb;
}

.site-settings__form {
  display: grid;
  gap: 1rem;
}

.site-settings__domains {
  margin: 0;
  color: #6b7280;
}

.site-settings__field {
  display: grid;
  gap: 0.35rem;

  span {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
  }

  textarea {
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 0.625rem 0.75rem;
  }

  small {
    color: #dc2626;
  }
}

.site-settings__check {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

@media (max-width: 768px) {
  .site-settings {
    grid-template-columns: 1fr;
  }
}
</style>
