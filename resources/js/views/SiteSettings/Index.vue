<script setup lang="ts">
import axios from 'axios'
import { computed, onMounted, reactive, ref } from 'vue'
import BaseLayout from '../../layouts/BaseLayout.vue'
import FormInput from '../../components/form/FormInput.vue'
import { setPublicTenant } from '../../utils/publicTenant'

type Domain = {
  id: number
  domain: string
}

type ScheduleRow = {
  day: string
  kids_time: string
  adults_time: string
}

type Site = {
  academy_name?: string
  page_title?: string | null
  hero_title?: string | null
  hero_subtitle?: string | null
  primary_color?: string
  header_color?: string
  background_color?: string
  trial_button_color?: string
  portal_button_color?: string
  app_primary_color?: string
  app_header_color?: string
  app_background_color?: string
  app_login_background_color?: string
  logo_path?: string | null
  logo_url?: string | null
  whatsapp?: string | null
  instagram?: string | null
  address?: string | null
  schedule?: ScheduleRow[] | null
  active?: boolean
}

type TenantRow = {
  id: number
  name: string
  slug: string
  domains: Domain[]
  site?: Site | null
}

type SiteReview = {
  id: number
  author_name: string
  rating: number
  comment: string
  active: boolean
  sort_order: number
}

const tenant = ref<TenantRow | null>(null)
const reviews = ref<SiteReview[]>([])
const loading = ref(false)
const saving = ref(false)
const savingReview = ref(false)
const editingReviewId = ref<number | null>(null)
const errors = ref<Record<string, string>>({})
const reviewErrors = ref<Record<string, string>>({})
const logoFile = ref<File | null>(null)
const activeTab = ref<'info' | 'appVisual' | 'siteVisual' | 'reviews' | 'schedule'>('info')

const form = reactive({
  name: '',
  academy_name: '',
  page_title: '',
  hero_title: '',
  hero_subtitle: '',
  primary_color: '#c41e3a',
  header_color: '#1b1b18',
  background_color: '#3d3d3d',
  trial_button_color: '#c41e3a',
  portal_button_color: '#2563eb',
  app_primary_color: '#111827',
  app_header_color: '#1b1b18',
  app_background_color: '#f8fafc',
  app_login_background_color: '#333333',
  logo_path: '',
  whatsapp: '',
  instagram: '',
  address: '',
  active: true,
  schedule: [] as ScheduleRow[],
})

const reviewForm = reactive({
  author_name: '',
  rating: 5,
  comment: '',
  active: true,
  sort_order: 0,
})

const domains = computed(() => tenant.value?.domains.map((domain) => domain.domain).join(', ') || '')

function fillForm(site: TenantRow) {
  tenant.value = site
  form.name = site.name
  form.academy_name = site.site?.academy_name || site.name
  form.page_title = site.site?.page_title || ''
  form.hero_title = site.site?.hero_title || ''
  form.hero_subtitle = site.site?.hero_subtitle || ''
  form.primary_color = site.site?.primary_color || '#c41e3a'
  form.header_color = site.site?.header_color || '#1b1b18'
  form.background_color = site.site?.background_color || '#3d3d3d'
  form.trial_button_color = site.site?.trial_button_color || '#c41e3a'
  form.portal_button_color = site.site?.portal_button_color || '#2563eb'
  form.app_primary_color = site.site?.app_primary_color || '#111827'
  form.app_header_color = site.site?.app_header_color || '#1b1b18'
  form.app_background_color = site.site?.app_background_color || '#f8fafc'
  form.app_login_background_color = site.site?.app_login_background_color || '#333333'
  form.logo_path = site.site?.logo_path || ''
  form.whatsapp = site.site?.whatsapp || ''
  form.instagram = site.site?.instagram || ''
  form.address = site.site?.address || ''
  form.active = site.site?.active !== false
  form.schedule = site.site?.schedule?.length
    ? site.site.schedule.map((row) => ({ ...row }))
    : [
        { day: 'Segunda-feira', kids_time: '', adults_time: '' },
        { day: 'Quarta-feira', kids_time: '', adults_time: '' },
        { day: 'Sexta-feira', kids_time: '', adults_time: '' },
      ]
  logoFile.value = null
  errors.value = {}
}

function onLogoChange(event: Event) {
  const input = event.target as HTMLInputElement
  logoFile.value = input.files?.[0] || null
}

function addScheduleRow() {
  form.schedule.push({ day: '', kids_time: '', adults_time: '' })
}

function removeScheduleRow(index: number) {
  form.schedule.splice(index, 1)
}

async function loadSettings() {
  loading.value = true

  try {
    const { data } = await axios.get('/api/site-settings')
    fillForm(data)
  } catch (error) {
    console.error(error)
    alert('Erro ao carregar o site deste domínio.')
  } finally {
    loading.value = false
  }
}

async function loadReviews() {
  const { data } = await axios.get('/api/site-reviews')
  reviews.value = Array.isArray(data) ? data : []
}

function appendPayload(payload: FormData, key: string, value: string | boolean | number | null) {
  payload.append(key, value === null ? '' : String(value))
}

async function submit() {
  if (!tenant.value) return

  saving.value = true
  errors.value = {}

  const payload = new FormData()
  payload.append('_method', 'PUT')
  appendPayload(payload, 'name', form.name)
  appendPayload(payload, 'academy_name', form.academy_name)
  appendPayload(payload, 'page_title', form.page_title)
  appendPayload(payload, 'hero_title', form.hero_title)
  appendPayload(payload, 'hero_subtitle', form.hero_subtitle)
  appendPayload(payload, 'primary_color', form.primary_color)
  appendPayload(payload, 'header_color', form.header_color)
  appendPayload(payload, 'background_color', form.background_color)
  appendPayload(payload, 'trial_button_color', form.trial_button_color)
  appendPayload(payload, 'portal_button_color', form.portal_button_color)
  appendPayload(payload, 'app_primary_color', form.app_primary_color)
  appendPayload(payload, 'app_header_color', form.app_header_color)
  appendPayload(payload, 'app_background_color', form.app_background_color)
  appendPayload(payload, 'app_login_background_color', form.app_login_background_color)
  appendPayload(payload, 'logo_path', form.logo_path)
  appendPayload(payload, 'whatsapp', form.whatsapp)
  appendPayload(payload, 'instagram', form.instagram)
  appendPayload(payload, 'address', form.address)
  appendPayload(payload, 'active', form.active ? 1 : 0)
  payload.append('schedule', JSON.stringify(form.schedule))

  if (logoFile.value) {
    payload.append('logo', logoFile.value)
  }

  try {
    const { data } = await axios.post(`/api/site-settings/${tenant.value.id}`, payload)
    fillForm(data)
    setPublicTenant(data)
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

function resetReviewForm() {
  editingReviewId.value = null
  reviewForm.author_name = ''
  reviewForm.rating = 5
  reviewForm.comment = ''
  reviewForm.active = true
  reviewForm.sort_order = 0
  reviewErrors.value = {}
}

function editReview(review: SiteReview) {
  editingReviewId.value = review.id
  reviewForm.author_name = review.author_name
  reviewForm.rating = review.rating
  reviewForm.comment = review.comment
  reviewForm.active = review.active
  reviewForm.sort_order = review.sort_order
  reviewErrors.value = {}
}

async function submitReview() {
  savingReview.value = true
  reviewErrors.value = {}

  const payload = {
    author_name: reviewForm.author_name,
    rating: Number(reviewForm.rating),
    comment: reviewForm.comment,
    active: reviewForm.active,
    sort_order: Number(reviewForm.sort_order) || 0,
  }

  try {
    if (editingReviewId.value) {
      await axios.put(`/api/site-reviews/${editingReviewId.value}`, payload)
    } else {
      await axios.post('/api/site-reviews', payload)
    }

    resetReviewForm()
    await loadReviews()
  } catch (error: any) {
    if (error.response?.data?.errors) {
      reviewErrors.value = Object.fromEntries(
        Object.entries(error.response.data.errors).map(([key, value]) => [
          key,
          Array.isArray(value) ? String(value[0]) : String(value),
        ]),
      )
    } else {
      alert(error.response?.data?.message || 'Erro ao salvar avaliação.')
      console.error(error)
    }
  } finally {
    savingReview.value = false
  }
}

async function deleteReview(review: SiteReview) {
  if (!confirm(`Remover a avaliação de ${review.author_name}?`)) return

  await axios.delete(`/api/site-reviews/${review.id}`)
  await loadReviews()
}

onMounted(async () => {
  await loadSettings()
  await loadReviews()
})
</script>

<template>
  <BaseLayout title="Configuração do site">
    <p v-if="loading">Carregando site...</p>

    <div v-else-if="tenant" class="site-settings">
      <div class="site-settings__tabs" role="tablist" aria-label="Configurações do site">
        <button
          type="button"
          class="site-settings__tab"
          :class="{ 'site-settings__tab--active': activeTab === 'info' }"
          role="tab"
          :aria-selected="activeTab === 'info'"
          @click="activeTab = 'info'"
        >
          Informações do site
        </button>
        <button
          type="button"
          class="site-settings__tab"
          :class="{ 'site-settings__tab--active': activeTab === 'appVisual' }"
          role="tab"
          :aria-selected="activeTab === 'appVisual'"
          @click="activeTab = 'appVisual'"
        >
          Visual do sistema
        </button>
        <button
          type="button"
          class="site-settings__tab"
          :class="{ 'site-settings__tab--active': activeTab === 'siteVisual' }"
          role="tab"
          :aria-selected="activeTab === 'siteVisual'"
          @click="activeTab = 'siteVisual'"
        >
          Visual do site
        </button>
        <button
          type="button"
          class="site-settings__tab"
          :class="{ 'site-settings__tab--active': activeTab === 'reviews' }"
          role="tab"
          :aria-selected="activeTab === 'reviews'"
          @click="activeTab = 'reviews'"
        >
          Avaliações
        </button>
        <button
          type="button"
          class="site-settings__tab"
          :class="{ 'site-settings__tab--active': activeTab === 'schedule' }"
          role="tab"
          :aria-selected="activeTab === 'schedule'"
          @click="activeTab = 'schedule'"
        >
          Dias e horários
        </button>
      </div>

      <form v-if="activeTab === 'info'" class="site-settings__card" @submit.prevent="submit">
        <div class="site-settings__heading">
          <div>
            <h2>{{ tenant.name }}</h2>
            <p>Domínio atual: {{ domains }}</p>
          </div>

          <label class="site-settings__check">
            <input v-model="form.active" type="checkbox" />
            Site ativo
          </label>
        </div>

        <section class="site-settings__section">
          <h3>Identidade</h3>
          <div class="site-settings__grid">
            <FormInput v-model="form.name" label="Nome interno" :error="errors.name" />
            <FormInput v-model="form.academy_name" label="Nome exibido" :error="errors.academy_name" />
            <FormInput v-model="form.page_title" label="Título da aba/SEO" :error="errors.page_title" />
            <FormInput v-model="form.hero_title" label="Título principal" :error="errors.hero_title" />
          </div>

          <label class="site-settings__field">
            <span>Texto principal</span>
            <textarea v-model="form.hero_subtitle" rows="3" />
            <small v-if="errors.hero_subtitle">{{ errors.hero_subtitle }}</small>
          </label>
        </section>

        <section class="site-settings__section">
          <h3>Logo</h3>
          <div class="site-settings__logo-row">
            <img v-if="tenant.site?.logo_url" :src="tenant.site.logo_url" alt="Logo atual" class="site-settings__logo" />
            <div class="site-settings__logo-fields">
              <FormInput v-model="form.logo_path" label="Logo atual/caminho no public" placeholder="logo.png" :error="errors.logo_path" />
              <label class="site-settings__field">
                <span>Enviar novo logo</span>
                <input type="file" accept="image/*" @change="onLogoChange" />
                <small v-if="errors.logo">{{ errors.logo }}</small>
              </label>
            </div>
          </div>
        </section>

        <section class="site-settings__section">
          <h3>Contato e localização</h3>
          <div class="site-settings__grid">
            <FormInput v-model="form.whatsapp" label="Link WhatsApp" placeholder="https://wa.me/..." :error="errors.whatsapp" />
            <FormInput v-model="form.instagram" label="Instagram" placeholder="https://instagram.com/..." :error="errors.instagram" />
          </div>

          <label class="site-settings__field">
            <span>Endereço</span>
            <textarea v-model="form.address" rows="4" />
            <small v-if="errors.address">{{ errors.address }}</small>
          </label>
        </section>

        <button type="submit" class="btn-primary" :disabled="saving">
          {{ saving ? 'Salvando...' : 'Salvar informações do site' }}
        </button>
      </form>

      <form v-else-if="activeTab === 'appVisual'" class="site-settings__card" @submit.prevent="submit">
        <section class="site-settings__section">
          <h2>Visual do sistema interno</h2>
          <p class="site-settings__hint">
            Essas cores são usadas no painel administrativo e na tela de login.
          </p>
        </section>

        <section class="site-settings__section">
          <h3>Cores internas</h3>
          <div class="site-settings__grid site-settings__grid--colors">
            <FormInput v-model="form.app_primary_color" type="color" label="Cor principal dos botões internos" :error="errors.app_primary_color" />
            <FormInput v-model="form.app_header_color" type="color" label="Cor do header/menu interno" :error="errors.app_header_color" />
            <FormInput v-model="form.app_background_color" type="color" label="Cor de fundo do painel" :error="errors.app_background_color" />
            <FormInput v-model="form.app_login_background_color" type="color" label="Cor de fundo do login" :error="errors.app_login_background_color" />
          </div>
        </section>

        <button type="submit" class="btn-primary" :disabled="saving">
          {{ saving ? 'Salvando...' : 'Salvar visual do sistema' }}
        </button>
      </form>

      <form v-else-if="activeTab === 'siteVisual'" class="site-settings__card" @submit.prevent="submit">
        <section class="site-settings__section">
          <h2>Visual do site</h2>
          <p class="site-settings__hint">
            Essas cores são usadas somente no site público externo.
          </p>
        </section>

        <section class="site-settings__section">
          <h3>Cores do site externo</h3>
          <div class="site-settings__grid site-settings__grid--colors">
            <FormInput v-model="form.primary_color" type="color" label="Cor primária do site" :error="errors.primary_color" />
            <FormInput v-model="form.header_color" type="color" label="Cor do header do site" :error="errors.header_color" />
            <FormInput v-model="form.background_color" type="color" label="Cor de fundo do site" :error="errors.background_color" />
            <FormInput v-model="form.trial_button_color" type="color" label="Botão aula experimental" :error="errors.trial_button_color" />
            <FormInput v-model="form.portal_button_color" type="color" label="Botão portal do aluno" :error="errors.portal_button_color" />
          </div>
        </section>

        <button type="submit" class="btn-primary" :disabled="saving">
          {{ saving ? 'Salvando...' : 'Salvar visual do site' }}
        </button>
      </form>

      <section v-else-if="activeTab === 'reviews'" class="site-settings__card">
        <div class="site-settings__section-head">
          <h2>Avaliações</h2>
          <button v-if="editingReviewId" type="button" class="btn-secondary" @click="resetReviewForm">Nova avaliação</button>
        </div>

        <form class="review-form" @submit.prevent="submitReview">
          <div class="site-settings__grid">
            <FormInput v-model="reviewForm.author_name" label="Nome" :error="reviewErrors.author_name" />
            <FormInput v-model="reviewForm.rating" type="number" label="Nota (1 a 5)" :error="reviewErrors.rating" />
            <FormInput v-model="reviewForm.sort_order" type="number" label="Ordem" :error="reviewErrors.sort_order" />
          </div>

          <label class="site-settings__field">
            <span>Comentário</span>
            <textarea v-model="reviewForm.comment" rows="4" />
            <small v-if="reviewErrors.comment">{{ reviewErrors.comment }}</small>
          </label>

          <label class="site-settings__check">
            <input v-model="reviewForm.active" type="checkbox" />
            Avaliação ativa no site
          </label>

          <button type="submit" class="btn-primary" :disabled="savingReview">
            {{ savingReview ? 'Salvando...' : editingReviewId ? 'Atualizar avaliação' : 'Cadastrar avaliação' }}
          </button>
        </form>

        <div class="review-list">
          <article v-for="review in reviews" :key="review.id" class="review-list__item">
            <div>
              <strong>{{ review.author_name }}</strong>
              <span>{{ '★'.repeat(review.rating) }}{{ '☆'.repeat(5 - review.rating) }}</span>
              <p>{{ review.comment }}</p>
              <small>{{ review.active ? 'Ativa' : 'Oculta' }} · ordem {{ review.sort_order }}</small>
            </div>
            <div class="review-list__actions">
              <button type="button" class="btn-secondary" @click="editReview(review)">Editar</button>
              <button type="button" class="btn-danger" @click="deleteReview(review)">Excluir</button>
            </div>
          </article>

          <p v-if="!reviews.length">Nenhuma avaliação cadastrada ainda.</p>
        </div>
      </section>

      <form v-else class="site-settings__card" @submit.prevent="submit">
        <section class="site-settings__section">
          <div class="site-settings__section-head">
            <h3>Dias e horários</h3>
            <button type="button" class="btn-secondary" @click="addScheduleRow">Adicionar horário</button>
          </div>

          <div class="schedule-editor">
            <div v-for="(row, index) in form.schedule" :key="index" class="schedule-editor__row">
              <FormInput v-model="row.day" label="Dia" :error="errors[`schedule.${index}.day`]" />
              <FormInput v-model="row.kids_time" label="Crianças" placeholder="18h - 19h" :error="errors[`schedule.${index}.kids_time`]" />
              <FormInput v-model="row.adults_time" label="Adultos" placeholder="19h - 20h" :error="errors[`schedule.${index}.adults_time`]" />
              <button type="button" class="btn-danger" @click="removeScheduleRow(index)">Remover</button>
            </div>
          </div>
        </section>

        <button type="submit" class="btn-primary" :disabled="saving">
          {{ saving ? 'Salvando...' : 'Salvar dias e horários' }}
        </button>
      </form>
    </div>
  </BaseLayout>
</template>

<style scoped lang="scss">
.site-settings {
  display: grid;
  gap: 1rem;
}

.site-settings__tabs {
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
  border-bottom: 1px solid #e5e7eb;
}

.site-settings__tab {
  border: 1px solid transparent;
  border-bottom: none;
  border-radius: 8px 8px 0 0;
  background: transparent;
  color: #4b5563;
  cursor: pointer;
  font-weight: 600;
  padding: 0.75rem 1rem;
  white-space: nowrap;
}

.site-settings__tab--active {
  background: #fff;
  border-color: #e5e7eb;
  color: #111827;
}

.site-settings__card {
  display: grid;
  gap: 1.25rem;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 1rem;
}

.site-settings__heading,
.site-settings__section-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}

.site-settings__heading h2,
.site-settings__section h3,
.site-settings__section-head h2,
.site-settings__section-head h3 {
  margin: 0;
}

.site-settings__heading p {
  margin: 0.25rem 0 0;
  color: #6b7280;
}

.site-settings__hint {
  margin: 0;
  color: #6b7280;
}

.site-settings__section,
.review-form {
  display: grid;
  gap: 1rem;
}

.site-settings__grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
}

.site-settings__grid--colors {
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
}

.site-settings__field {
  display: grid;
  gap: 0.35rem;

  span {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
  }

  textarea,
  input[type='file'] {
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

.site-settings__logo-row {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 1rem;
  align-items: center;
}

.site-settings__logo {
  width: 120px;
  max-height: 120px;
  object-fit: contain;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 0.5rem;
}

.site-settings__logo-fields {
  display: grid;
  gap: 1rem;
}

.schedule-editor {
  display: grid;
  gap: 0.75rem;
}

.schedule-editor__row {
  display: grid;
  grid-template-columns: repeat(3, minmax(140px, 1fr)) auto;
  gap: 0.75rem;
  align-items: end;
}

.btn-secondary,
.btn-danger {
  border: none;
  border-radius: 6px;
  cursor: pointer;
  padding: 9px 14px;
  font-size: 0.875rem;
}

.btn-secondary {
  background: #e5e7eb;
  color: #111827;
}

.btn-danger {
  background: #dc2626;
  color: #fff;
}

.review-list {
  display: grid;
  gap: 0.75rem;
}

.review-list__item {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 0.75rem;

  span,
  small {
    display: block;
    color: #6b7280;
  }

  p {
    margin: 0.35rem 0;
  }
}

.review-list__actions {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
}

@media (max-width: 768px) {
  .site-settings__logo-row,
  .schedule-editor__row {
    grid-template-columns: 1fr;
  }

  .review-list__item {
    flex-direction: column;
  }
}
</style>
