<script setup lang="ts">
import axios from 'axios'
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import BaseLayout from '../../layouts/BaseLayout.vue'
import FormInput from '../../components/form/FormInput.vue'
import ImageCropField from '../../components/photo/ImageCropField.vue'
import PhotoCropPicker from '../../components/photo/PhotoCropPicker.vue'
import { CROP_PRESETS } from '../../utils/croppedPhotoFile'
import { type SchoolClassRow } from '../../utils/classSchedule'
import { toastDanger, toastSuccess } from '../../utils/toast'

type Domain = {
  id: number
  domain: string
}

type ScheduleMatrix = {
  weekdays: Array<{ weekday: number; label: string }>
  rows: Array<{ class_id?: number; class_name: string; times: string[][] }>
}

type ScheduleRow = ScheduleMatrix['rows'][number]

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
  pwa_logo_path?: string | null
  pwa_logo_url?: string | null
  nav_logo_path?: string | null
  nav_logo_url?: string | null
  footer_logo_path?: string | null
  footer_logo_url?: string | null
  hero_logo_path?: string | null
  hero_logo_url?: string | null
  carousel_images?: string[] | null
  carousel_image_urls?: string[] | null
  whatsapp?: string | null
  instagram?: string | null
  youtube?: string | null
  address?: string | null
  schedule?: ScheduleRow[] | null
  active?: boolean
}

type TenantRow = {
  id: number
  name: string
  slug: string
  plan?: 'app' | 'digital'
  primary_domain?: string | null
  domains: Domain[]
  site?: Site | null
}

type SiteReview = {
  id: number
  author_name: string
  author_photo_path?: string | null
  author_photo_url?: string | null
  rating: number
  comment: string
  status: 'pending' | 'approved' | 'rejected'
  active: boolean
  sort_order: number
  student_id?: number | null
}

type CarouselPending = {
  file: File
  previewUrl: string
}

const route = useRoute()
const router = useRouter()
const tenant = ref<TenantRow | null>(null)
const reviews = ref<SiteReview[]>([])
const classSchedule = ref<ScheduleMatrix>({ weekdays: [], rows: [] })
const classOrder = ref<SchoolClassRow[]>([])
const scheduleReordering = ref(false)
const loading = ref(false)
const scheduleLoading = ref(false)
const saving = ref(false)
const savingReview = ref(false)
const reviewActionBusyId = ref<number | null>(null)
const editingReviewId = ref<number | null>(null)
const errors = ref<Record<string, string>>({})
const reviewErrors = ref<Record<string, string>>({})
const imageCropError = ref('')
const logoFile = ref<File | null>(null)
const pwaLogoFile = ref<File | null>(null)
const navLogoFile = ref<File | null>(null)
const footerLogoFile = ref<File | null>(null)
const heroLogoFile = ref<File | null>(null)
const carouselFiles = ref<CarouselPending[]>([])
const carouselCropPickerRef = ref<InstanceType<typeof PhotoCropPicker> | null>(null)
const activeTab = ref<'info' | 'visual' | 'reviews' | 'schedule' | 'system'>('info')

const platformTenantId = computed(() => {
  const id = Number(route.params.tenantId)
  return Number.isFinite(id) ? id : null
})

const isPlatformMode = computed(() => platformTenantId.value !== null)

const apiBase = computed(() => {
  if (isPlatformMode.value) {
    return `/api/platform/tenants/${platformTenantId.value}`
  }

  return '/api'
})

const planLabels: Record<'app' | 'digital', string> = {
  app: 'Tatameiro App',
  digital: 'Academia Digital',
}

const planLabel = computed(() => {
  const plan = tenant.value?.plan
  return plan ? planLabels[plan] : ''
})

const isAppPlan = computed(() => tenant.value?.plan === 'app')
const hasPublicSite = computed(() => tenant.value?.plan === 'digital')

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
  pwa_logo_path: '',
  nav_logo_path: '',
  footer_logo_path: '',
  hero_logo_path: '',
  carousel_images: [] as string[],
  whatsapp: '',
  instagram: '',
  youtube: '',
  address: '',
  active: true,
})

const reviewForm = reactive({
  author_name: '',
  author_photo_path: '' as string | null,
  rating: 5,
  comment: '',
  active: true,
  sort_order: 0,
})

const REVIEW_COMMENT_MAX = 200

const authorPhotoFile = ref<File | null>(null)
const editingAuthorPhotoUrl = ref<string | null>(null)
const reviewPhotoFieldKey = ref(0)

const primaryDomain = computed(() => tenant.value?.primary_domain || tenant.value?.domains[0]?.domain || '')
const pwaLogoPreviewUrl = computed(() => (
  tenant.value?.site?.pwa_logo_url
  || tenant.value?.site?.logo_url
  || ''
))
const usesCustomPwaLogo = computed(() => Boolean(tenant.value?.site?.pwa_logo_path || pwaLogoFile.value))
const secondaryDomains = computed(() => {
  const primary = primaryDomain.value
  return tenant.value?.domains
    .map((domain) => domain.domain)
    .filter((domain) => domain !== primary)
    .join(', ') || ''
})
const carouselCount = computed(() => form.carousel_images.length + carouselFiles.value.length)
const carouselFull = computed(() => carouselCount.value >= 5)

function clearCarouselPreviews() {
  carouselFiles.value.forEach((item) => URL.revokeObjectURL(item.previewUrl))
}

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
  form.pwa_logo_path = site.site?.pwa_logo_path || ''
  form.nav_logo_path = site.site?.nav_logo_path || ''
  form.footer_logo_path = site.site?.footer_logo_path || ''
  form.hero_logo_path = site.site?.hero_logo_path || ''
  form.carousel_images = site.site?.carousel_images?.length ? [...site.site.carousel_images] : []
  form.whatsapp = site.site?.whatsapp || ''
  form.instagram = site.site?.instagram || ''
  form.youtube = site.site?.youtube || ''
  form.address = site.site?.address || ''
  form.active = site.site?.active !== false
  activeTab.value = site.plan === 'app' ? 'system' : 'info'
  logoFile.value = null
  pwaLogoFile.value = null
  navLogoFile.value = null
  footerLogoFile.value = null
  heroLogoFile.value = null
  clearCarouselPreviews()
  carouselFiles.value = []
  imageCropError.value = ''
  errors.value = {}
}

function onImageCropError(message: string) {
  imageCropError.value = message
}

function onAppLogoCropped(file: File) {
  logoFile.value = file
  imageCropError.value = ''
}

function onPwaLogoCropped(file: File) {
  pwaLogoFile.value = file
  imageCropError.value = ''
}

function clearPwaLogo() {
  form.pwa_logo_path = ''
  pwaLogoFile.value = null
  if (tenant.value?.site) {
    tenant.value.site.pwa_logo_path = null
    tenant.value.site.pwa_logo_url = null
  }
  imageCropError.value = ''
}

function onNavLogoCropped(file: File) {
  navLogoFile.value = file
  imageCropError.value = ''
}

function onFooterLogoCropped(file: File) {
  footerLogoFile.value = file
  imageCropError.value = ''
}

function onHeroLogoCropped(file: File) {
  heroLogoFile.value = file
  imageCropError.value = ''
}

function onCarouselCropped(file: File) {
  if (carouselFull.value) {
    imageCropError.value = 'O carrossel pode ter no máximo 5 fotos.'
    return
  }

  carouselFiles.value.push({
    file,
    previewUrl: URL.createObjectURL(file),
  })
  imageCropError.value = ''
}

function pickCarouselPhoto() {
  if (carouselFull.value) return
  carouselCropPickerRef.value?.pick()
}

function removeSavedCarouselImage(index: number) {
  form.carousel_images.splice(index, 1)
  tenant.value?.site?.carousel_image_urls?.splice(index, 1)
}

function removeNewCarouselImage(index: number) {
  const [removed] = carouselFiles.value.splice(index, 1)
  if (removed) {
    URL.revokeObjectURL(removed.previewUrl)
  }
}

async function loadClassSchedule() {
  scheduleLoading.value = true

  try {
    const [scheduleRes, classesRes] = await Promise.all([
      axios.get(`${apiBase.value}/classes/schedule`),
      axios.get(`${apiBase.value}/classes`),
    ])

    const data = scheduleRes.data
    classSchedule.value = data?.rows
      ? (data as ScheduleMatrix)
      : { weekdays: [], rows: Array.isArray(data) ? [] : [] }
    classOrder.value = Array.isArray(classesRes.data) ? classesRes.data : []
  } catch (error) {
    console.error(error)
    classSchedule.value = { weekdays: [], rows: [] }
    classOrder.value = []
  } finally {
    scheduleLoading.value = false
  }
}

async function persistClassOrder() {
  if (!classOrder.value.length) return

  scheduleReordering.value = true

  try {
    await axios.post(`${apiBase.value}/classes/reorder`, {
      order: classOrder.value.map((item) => item.id),
    })

    const { data } = await axios.get(`${apiBase.value}/classes/schedule`)
    classSchedule.value = data?.rows
      ? (data as ScheduleMatrix)
      : { weekdays: [], rows: [] }

    toastSuccess('Ordem das turmas atualizada.')
  } catch (error: any) {
    toastDanger(error.response?.data?.message || 'Erro ao atualizar a ordem das turmas.')
    await loadClassSchedule()
  } finally {
    scheduleReordering.value = false
  }
}

function moveClass(index: number, direction: -1 | 1) {
  const target = index + direction
  if (target < 0 || target >= classOrder.value.length) return

  const items = [...classOrder.value]
  const [moved] = items.splice(index, 1)
  items.splice(target, 0, moved)
  classOrder.value = items
  persistClassOrder()
}

async function loadSettings() {
  loading.value = true

  try {
    const { data } = await axios.get(`${apiBase.value}/site-settings`)
    fillForm(data)
  } catch (error) {
    console.error(error)
    toastDanger('Erro ao carregar o site deste domínio.')
  } finally {
    loading.value = false
  }
}

async function loadReviews() {
  const { data } = await axios.get(`${apiBase.value}/site-reviews`)
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
  appendPayload(payload, 'app_primary_color', form.app_primary_color)
  appendPayload(payload, 'app_header_color', form.app_header_color)
  appendPayload(payload, 'app_background_color', form.app_background_color)
  appendPayload(payload, 'app_login_background_color', form.app_login_background_color)
  appendPayload(payload, 'logo_path', form.logo_path)
  appendPayload(payload, 'pwa_logo_path', form.pwa_logo_path)

  if (hasPublicSite.value) {
    appendPayload(payload, 'page_title', form.page_title)
    appendPayload(payload, 'hero_title', form.hero_title)
    appendPayload(payload, 'hero_subtitle', form.hero_subtitle)
    appendPayload(payload, 'primary_color', form.primary_color)
    appendPayload(payload, 'header_color', form.header_color)
    appendPayload(payload, 'background_color', form.background_color)
    appendPayload(payload, 'trial_button_color', form.trial_button_color)
    appendPayload(payload, 'portal_button_color', form.portal_button_color)
    appendPayload(payload, 'nav_logo_path', form.nav_logo_path)
    appendPayload(payload, 'footer_logo_path', form.footer_logo_path)
    appendPayload(payload, 'hero_logo_path', form.hero_logo_path)
    appendPayload(payload, 'whatsapp', form.whatsapp)
    appendPayload(payload, 'instagram', form.instagram)
    appendPayload(payload, 'youtube', form.youtube)
    appendPayload(payload, 'address', form.address)
    appendPayload(payload, 'active', form.active ? 1 : 0)
    payload.append('carousel_images', JSON.stringify(form.carousel_images))
  }

  if (logoFile.value) {
    payload.append('logo', logoFile.value)
  }

  if (pwaLogoFile.value) {
    payload.append('pwa_logo', pwaLogoFile.value)
  }

  if (hasPublicSite.value) {
    if (navLogoFile.value) {
      payload.append('nav_logo', navLogoFile.value)
    }

    if (footerLogoFile.value) {
      payload.append('footer_logo', footerLogoFile.value)
    }

    if (heroLogoFile.value) {
      payload.append('hero_logo', heroLogoFile.value)
    }

    carouselFiles.value.forEach((item) => {
      payload.append('carousel_photos[]', item.file)
    })
  }

  try {
    const { data } = await axios.post(
      isPlatformMode.value
        ? `${apiBase.value}/site-settings`
        : `/api/site-settings/${tenant.value.id}`,
      payload,
    )
    fillForm(data)
    toastSuccess('Configurações salvas com sucesso.')
  } catch (error: any) {
    if (error.response?.data?.errors) {
      errors.value = Object.fromEntries(
        Object.entries(error.response.data.errors).map(([key, value]) => [
          key,
          Array.isArray(value) ? String(value[0]) : String(value),
        ]),
      )
    } else {
      toastDanger(error.response?.data?.message || 'Erro ao salvar configurações.')
      console.error(error)
    }
  } finally {
    saving.value = false
  }
}

function resetReviewForm() {
  editingReviewId.value = null
  reviewForm.author_name = ''
  reviewForm.author_photo_path = ''
  reviewForm.rating = 5
  reviewForm.comment = ''
  reviewForm.active = true
  reviewForm.sort_order = 0
  authorPhotoFile.value = null
  editingAuthorPhotoUrl.value = null
  reviewPhotoFieldKey.value += 1
  reviewErrors.value = {}
}

function onAuthorPhotoCropped(file: File) {
  authorPhotoFile.value = file
}

function removeAuthorPhoto() {
  reviewForm.author_photo_path = ''
  authorPhotoFile.value = null
  editingAuthorPhotoUrl.value = null
  reviewPhotoFieldKey.value += 1
}

function editReview(review: SiteReview) {
  editingReviewId.value = review.id
  reviewForm.author_name = review.author_name
  reviewForm.author_photo_path = review.author_photo_path ?? ''
  reviewForm.rating = review.rating
  reviewForm.comment = review.comment.slice(0, REVIEW_COMMENT_MAX)
  reviewForm.active = review.active
  reviewForm.sort_order = review.sort_order
  authorPhotoFile.value = null
  editingAuthorPhotoUrl.value = review.author_photo_url ?? null
  reviewErrors.value = {}
}

async function submitReview() {
  savingReview.value = true
  reviewErrors.value = {}

  const payload = new FormData()
  payload.append('author_name', reviewForm.author_name)
  payload.append('rating', String(Number(reviewForm.rating)))
  payload.append('comment', reviewForm.comment)
  payload.append('active', reviewForm.active ? '1' : '0')
  payload.append('sort_order', String(Number(reviewForm.sort_order) || 0))

  if (editingReviewId.value) {
    payload.append('author_photo_path', reviewForm.author_photo_path ?? '')
  }

  if (authorPhotoFile.value) {
    payload.append('author_photo', authorPhotoFile.value)
  }

  try {
    if (editingReviewId.value) {
      payload.append('_method', 'PUT')
      await axios.post(`${apiBase.value}/site-reviews/${editingReviewId.value}`, payload)
    } else {
      await axios.post(`${apiBase.value}/site-reviews`, payload)
    }

    resetReviewForm()
    await loadReviews()
    toastSuccess('Avaliação salva com sucesso.')
  } catch (error: any) {
    if (error.response?.data?.errors) {
      reviewErrors.value = Object.fromEntries(
        Object.entries(error.response.data.errors).map(([key, value]) => [
          key,
          Array.isArray(value) ? String(value[0]) : String(value),
        ]),
      )
    } else {
      toastDanger(error.response?.data?.message || 'Erro ao salvar avaliação.')
      console.error(error)
    }
  } finally {
    savingReview.value = false
  }
}

async function deleteReview(review: SiteReview) {
  if (!confirm(`Remover a avaliação de ${review.author_name}?`)) return

  await axios.delete(`${apiBase.value}/site-reviews/${review.id}`)
  await loadReviews()
}

function reviewStatusLabel(review: SiteReview) {
  if (review.status === 'pending') return 'Pendente'
  if (review.status === 'rejected') return 'Rejeitada'
  if (review.active) return 'Ativa no site'
  return 'Oculta'
}

async function approveReview(review: SiteReview) {
  reviewActionBusyId.value = review.id
  try {
    await axios.post(`${apiBase.value}/site-reviews/${review.id}/approve`)
    await loadReviews()
    toastSuccess('Avaliação aprovada e publicada no site.')
  } catch (error: any) {
    toastDanger(error.response?.data?.message || 'Erro ao aprovar avaliação.')
  } finally {
    reviewActionBusyId.value = null
  }
}

async function rejectReview(review: SiteReview) {
  if (!confirm(`Rejeitar a avaliação de ${review.author_name}?`)) return

  reviewActionBusyId.value = review.id
  try {
    await axios.post(`${apiBase.value}/site-reviews/${review.id}/reject`)
    await loadReviews()
    toastSuccess('Avaliação rejeitada.')
  } catch (error: any) {
    toastDanger(error.response?.data?.message || 'Erro ao rejeitar avaliação.')
  } finally {
    reviewActionBusyId.value = null
  }
}

onMounted(async () => {
  await loadSettings()
  if (hasPublicSite.value) {
    await loadReviews()
    await loadClassSchedule()
  }
})

watch(activeTab, (tab) => {
  if (tab === 'schedule') {
    loadClassSchedule()
  }
})
</script>

<template>
  <BaseLayout title="Configuração do site">
    <p v-if="loading">Carregando site...</p>

    <div v-else-if="tenant" class="site-settings">
      <div v-if="isPlatformMode" class="site-settings__toolbar">
        <button type="button" class="btn-secondary" @click="router.push('/admin/platform/tenants')">
          Voltar para academias
        </button>
        <span v-if="planLabel" class="site-settings__plan">{{ planLabel }}</span>
      </div>
      <div v-else-if="planLabel" class="site-settings__toolbar">
        <span class="site-settings__plan">{{ planLabel }}</span>
      </div>

      <div class="site-settings__tabs" role="tablist" aria-label="Configurações do site">
        <button
          v-if="isAppPlan"
          type="button"
          class="site-settings__tab"
          :class="{ 'site-settings__tab--active': activeTab === 'system' }"
          role="tab"
          :aria-selected="activeTab === 'system'"
          @click="activeTab = 'system'"
        >
          Identidade do sistema
        </button>
        <template v-if="hasPublicSite">
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
            :class="{ 'site-settings__tab--active': activeTab === 'visual' }"
            role="tab"
            :aria-selected="activeTab === 'visual'"
            @click="activeTab = 'visual'"
          >
            Visual
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
        </template>
      </div>

      <form v-if="activeTab === 'system'" class="site-settings__card" @submit.prevent="submit">
        <div class="site-settings__heading">
          <div>
            <h2>{{ tenant.name }}</h2>
            <p>Domínio principal: {{ primaryDomain }}</p>
            <p v-if="secondaryDomains">Outros domínios: {{ secondaryDomains }}</p>
            <p v-if="isAppPlan" class="site-settings__hint">Plano App — configure apenas o painel e o portal do aluno.</p>
            <p v-else class="site-settings__hint">Plano Academia Digital — site público no domínio próprio da academia.</p>
          </div>
        </div>

        <section class="site-settings__section">
          <h3>Identidade</h3>
          <div class="site-settings__grid">
            <FormInput v-model="form.name" label="Nome interno" :error="errors.name" />
            <FormInput v-model="form.academy_name" label="Nome exibido" :error="errors.academy_name" />
          </div>

          <ImageCropField
            label="Logo do sistema (login e painel)"
            :preview-url="tenant.site?.logo_url"
            preview-class="image-crop-field__preview--app"
            :preset="CROP_PRESETS.appLogo"
            hint="Proporção 1:1 · login e menu lateral"
            @cropped="onAppLogoCropped"
            @error="onImageCropError"
          />

          <div class="site-settings__pwa-logo">
            <ImageCropField
              label="Logo do PWA (atalho no celular)"
              :preview-url="pwaLogoPreviewUrl"
              preview-class="image-crop-field__preview--app"
              :preset="CROP_PRESETS.appLogo"
              hint="Proporção 1:1 · ícone ao instalar o painel na tela inicial"
              @cropped="onPwaLogoCropped"
              @error="onImageCropError"
            />
            <p class="site-settings__hint">
              {{ usesCustomPwaLogo ? 'Logo personalizado para o atalho no celular.' : 'Usando o logo do sistema por padrão.' }}
            </p>
            <button
              v-if="usesCustomPwaLogo"
              type="button"
              class="btn-secondary site-settings__pwa-logo-reset"
              @click="clearPwaLogo"
            >
              Usar logo do sistema
            </button>
          </div>
          <small v-if="errors.pwa_logo">{{ errors.pwa_logo }}</small>
        </section>

        <section class="site-settings__section site-settings__section--divider">
          <h3>Visual do sistema interno</h3>
          <div class="site-settings__grid site-settings__grid--colors">
            <FormInput v-model="form.app_primary_color" type="color" label="Cor principal dos botões internos" :error="errors.app_primary_color" />
            <FormInput v-model="form.app_header_color" type="color" label="Cor do header/menu interno" :error="errors.app_header_color" />
            <FormInput v-model="form.app_background_color" type="color" label="Cor de fundo do painel" :error="errors.app_background_color" />
            <FormInput v-model="form.app_login_background_color" type="color" label="Cor de fundo do login" :error="errors.app_login_background_color" />
          </div>
        </section>

        <button type="submit" class="btn-primary" :disabled="saving">
          {{ saving ? 'Salvando...' : 'Salvar identidade do sistema' }}
        </button>
      </form>

      <form v-else-if="activeTab === 'info'" class="site-settings__card" @submit.prevent="submit">
        <div class="site-settings__heading">
          <div>
            <h2>{{ tenant.name }}</h2>
            <p>Domínio principal: {{ primaryDomain }}</p>
            <p v-if="secondaryDomains">Outros domínios: {{ secondaryDomains }}</p>
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
          <h3>Carrossel da seção Sobre</h3>
          <p class="site-settings__hint">Cadastre até 5 fotos em 16:9 para aparecerem no topo do site.</p>

          <div class="carousel-editor">
            <figure
              v-for="(imageUrl, index) in tenant.site?.carousel_image_urls || []"
              :key="`${imageUrl}-${index}`"
              class="carousel-editor__item"
            >
              <img :src="imageUrl" alt="Foto atual do carrossel" />
              <button type="button" class="btn-danger" @click="removeSavedCarouselImage(index)">Remover</button>
            </figure>

            <figure
              v-for="(item, index) in carouselFiles"
              :key="`${item.file.name}-${index}`"
              class="carousel-editor__item"
            >
              <img :src="item.previewUrl" alt="Nova foto do carrossel" />
              <button type="button" class="btn-danger" @click="removeNewCarouselImage(index)">Remover</button>
            </figure>
          </div>

          <PhotoCropPicker
            ref="carouselCropPickerRef"
            v-bind="CROP_PRESETS.carousel"
            @cropped="onCarouselCropped"
            @error="onImageCropError"
          />
          <button type="button" class="btn-secondary" :disabled="carouselFull" @click="pickCarouselPhoto">
            {{ carouselFull ? 'Limite de 5 fotos atingido' : 'Adicionar foto ao carrossel' }}
          </button>
          <small>{{ carouselCount }}/5 fotos selecionadas</small>
          <small v-if="errors.carousel_photos">{{ errors.carousel_photos }}</small>
        </section>

        <section class="site-settings__section">
          <h3>Logos</h3>
          <p class="site-settings__hint">
            Cada logo é usado em um lugar diferente do site ou do sistema. Todas as imagens passam pelo recorte antes do envio.
          </p>

          <div class="site-settings__logo-grid">
            <ImageCropField
              label="Logo do header (navegação)"
              :preview-url="tenant.site?.nav_logo_url"
              preview-class="image-crop-field__preview--nav"
              :preset="CROP_PRESETS.navLogo"
              hint="Proporção 3:1 · exibido na barra superior"
              @cropped="onNavLogoCropped"
              @error="onImageCropError"
            />
            <ImageCropField
              label="Logo do footer"
              :preview-url="tenant.site?.footer_logo_url"
              preview-class="image-crop-field__preview--footer"
              :preset="CROP_PRESETS.footerLogo"
              hint="Proporção 3:1 · exibido no rodapé"
              @cropped="onFooterLogoCropped"
              @error="onImageCropError"
            />
            <ImageCropField
              label="Logo da seção Sobre"
              :preview-url="tenant.site?.hero_logo_url"
              preview-class="image-crop-field__preview--hero"
              :preset="CROP_PRESETS.heroLogo"
              hint="Recorte livre · usado quando não houver carrossel"
              @cropped="onHeroLogoCropped"
              @error="onImageCropError"
            />
            <ImageCropField
              label="Logo do sistema (login e painel)"
              :preview-url="tenant.site?.logo_url"
              preview-class="image-crop-field__preview--app"
              :preset="CROP_PRESETS.appLogo"
              hint="Proporção 1:1 · login e menu lateral"
              @cropped="onAppLogoCropped"
              @error="onImageCropError"
            />
            <div class="site-settings__pwa-logo">
              <ImageCropField
                label="Logo do PWA (atalho no celular)"
                :preview-url="pwaLogoPreviewUrl"
                preview-class="image-crop-field__preview--app"
                :preset="CROP_PRESETS.appLogo"
                hint="Proporção 1:1 · ícone ao instalar o painel na tela inicial"
                @cropped="onPwaLogoCropped"
                @error="onImageCropError"
              />
              <p class="site-settings__hint">
                {{ usesCustomPwaLogo ? 'Logo personalizado para o atalho no celular.' : 'Usando o logo do sistema por padrão.' }}
              </p>
              <button
                v-if="usesCustomPwaLogo"
                type="button"
                class="btn-secondary site-settings__pwa-logo-reset"
                @click="clearPwaLogo"
              >
                Usar logo do sistema
              </button>
            </div>
          </div>
          <small v-if="imageCropError" class="site-settings__crop-error">{{ imageCropError }}</small>
          <small v-if="errors.logo">{{ errors.logo }}</small>
          <small v-if="errors.pwa_logo">{{ errors.pwa_logo }}</small>
          <small v-if="errors.nav_logo">{{ errors.nav_logo }}</small>
          <small v-if="errors.footer_logo">{{ errors.footer_logo }}</small>
          <small v-if="errors.hero_logo">{{ errors.hero_logo }}</small>
        </section>

        <section class="site-settings__section">
          <h3>Contato e localização</h3>
          <div class="site-settings__grid">
            <FormInput v-model="form.youtube" label="YouTube" placeholder="https://youtube.com/..." :error="errors.youtube" />
            <FormInput v-model="form.instagram" label="Instagram" placeholder="https://instagram.com/..." :error="errors.instagram" />
            <FormInput v-model="form.whatsapp" label="Link WhatsApp" placeholder="https://wa.me/..." :error="errors.whatsapp" />
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

      <form v-else-if="activeTab === 'visual'" class="site-settings__card" @submit.prevent="submit">
        <section class="site-settings__section">
          <h2>Visual do site</h2>
          <p class="site-settings__hint">
            Essas cores são usadas somente no site público externo.
          </p>

          <div class="site-settings__grid site-settings__grid--colors">
            <FormInput v-model="form.primary_color" type="color" label="Cor primária do site" :error="errors.primary_color" />
            <FormInput v-model="form.header_color" type="color" label="Cor do header do site" :error="errors.header_color" />
            <FormInput v-model="form.background_color" type="color" label="Cor de fundo do site" :error="errors.background_color" />
            <FormInput v-model="form.trial_button_color" type="color" label="Botão aula experimental" :error="errors.trial_button_color" />
            <FormInput v-model="form.portal_button_color" type="color" label="Botão portal do aluno" :error="errors.portal_button_color" />
          </div>
        </section>

        <section class="site-settings__section site-settings__section--divider">
          <h2>Visual do sistema interno</h2>
          <p class="site-settings__hint">
            Essas cores são usadas no painel administrativo e na tela de login.
          </p>

          <div class="site-settings__grid site-settings__grid--colors">
            <FormInput v-model="form.app_primary_color" type="color" label="Cor principal dos botões internos" :error="errors.app_primary_color" />
            <FormInput v-model="form.app_header_color" type="color" label="Cor do header/menu interno" :error="errors.app_header_color" />
            <FormInput v-model="form.app_background_color" type="color" label="Cor de fundo do painel" :error="errors.app_background_color" />
            <FormInput v-model="form.app_login_background_color" type="color" label="Cor de fundo do login" :error="errors.app_login_background_color" />
          </div>
        </section>

        <button type="submit" class="btn-primary" :disabled="saving">
          {{ saving ? 'Salvando...' : 'Salvar visual' }}
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

          <ImageCropField
            :key="reviewPhotoFieldKey"
            label="Foto de quem avaliou"
            :preview-url="editingAuthorPhotoUrl"
            preview-class="image-crop-field__preview--profile"
            :preset="CROP_PRESETS.profile"
            hint="Proporção 1:1 · exibida no card de avaliação do site"
            @cropped="onAuthorPhotoCropped"
            @error="onImageCropError"
          />
          <button
            v-if="editingAuthorPhotoUrl || authorPhotoFile"
            type="button"
            class="btn-secondary review-form__remove-photo"
            @click="removeAuthorPhoto"
          >
            Remover foto
          </button>
          <small v-if="reviewErrors.author_photo">{{ reviewErrors.author_photo }}</small>

          <label class="site-settings__field">
            <span>Comentário</span>
            <textarea v-model="reviewForm.comment" rows="4" :maxlength="REVIEW_COMMENT_MAX" />
            <small class="site-settings__hint">{{ reviewForm.comment.length }}/{{ REVIEW_COMMENT_MAX }} caracteres</small>
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
          <article
            v-for="review in reviews"
            :key="review.id"
            class="review-list__item"
            :class="{ 'review-list__item--pending': review.status === 'pending' }"
          >
            <div class="review-list__content">
              <div class="review-list__head">
                <img
                  v-if="review.author_photo_url"
                  class="review-list__photo"
                  :src="review.author_photo_url"
                  :alt="`Foto de ${review.author_name}`"
                />
                <div>
                  <strong>{{ review.author_name }}</strong>
                  <span>{{ '★'.repeat(review.rating) }}{{ '☆'.repeat(5 - review.rating) }}</span>
                </div>
              </div>
              <p>{{ review.comment }}</p>
              <small>
                {{ reviewStatusLabel(review) }}
                <template v-if="review.student_id"> · enviada por aluno</template>
                · ordem {{ review.sort_order }}
              </small>
            </div>
            <div class="review-list__actions">
              <template v-if="review.status === 'pending'">
                <button
                  type="button"
                  class="btn-primary"
                  :disabled="reviewActionBusyId === review.id"
                  @click="approveReview(review)"
                >
                  {{ reviewActionBusyId === review.id ? '…' : 'Aprovar' }}
                </button>
                <button
                  type="button"
                  class="btn-secondary"
                  :disabled="reviewActionBusyId === review.id"
                  @click="rejectReview(review)"
                >
                  Rejeitar
                </button>
              </template>
              <button type="button" class="btn-secondary" @click="editReview(review)">Editar</button>
              <button type="button" class="btn-danger" @click="deleteReview(review)">Excluir</button>
            </div>
          </article>

          <p v-if="!reviews.length">Nenhuma avaliação cadastrada ainda.</p>
        </div>
      </section>

      <section v-else-if="activeTab === 'schedule'" class="site-settings__card">
        <div class="site-settings__section-head">
          <div>
            <h2>Dias e horários</h2>
            <p class="site-settings__hint">
              A grade é montada automaticamente a partir das turmas cadastradas (dia da semana, tipo e horário).
              Use as setas para definir a ordem das linhas no site.
            </p>
          </div>
          <RouterLink to="/admin/classes" class="btn-secondary">Gerenciar turmas</RouterLink>
        </div>

        <p v-if="scheduleLoading">Carregando horários…</p>

        <template v-else>
          <div v-if="classOrder.length" class="schedule-order">
            <h3 class="schedule-order__title">Ordem no site</h3>
            <ul class="schedule-order__list">
              <li
                v-for="(classItem, index) in classOrder"
                :key="classItem.id"
                class="schedule-order__item"
                :class="{ 'schedule-order__item--inactive': classItem.active === false }"
              >
                <span class="schedule-order__name">{{ classItem.name }}</span>
                <span v-if="classItem.active === false" class="schedule-order__badge">Inativa</span>
                <div class="schedule-order__actions">
                  <button
                    type="button"
                    class="schedule-order__btn"
                    :disabled="index === 0 || scheduleReordering"
                    aria-label="Subir turma"
                    @click="moveClass(index, -1)"
                  >
                    ↑
                  </button>
                  <button
                    type="button"
                    class="schedule-order__btn"
                    :disabled="index === classOrder.length - 1 || scheduleReordering"
                    aria-label="Descer turma"
                    @click="moveClass(index, 1)"
                  >
                    ↓
                  </button>
                </div>
              </li>
            </ul>
          </div>

          <div class="schedule-preview">
            <table v-if="classSchedule.rows.length" class="schedule-preview__table schedule-preview__table--matrix">
              <thead>
                <tr>
                  <th scope="col">Turma</th>
                  <th v-for="weekday in classSchedule.weekdays" :key="weekday.weekday" scope="col">
                    {{ weekday.label }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in classSchedule.rows" :key="row.class_id ?? row.class_name">
                  <th scope="row">{{ row.class_name }}</th>
                  <td v-for="(slots, index) in row.times" :key="`${row.class_name}-${index}`">
                    <div v-if="slots.length" class="schedule-preview__times">
                      <span
                        v-for="(slot, slotIndex) in slots"
                        :key="`${row.class_name}-${index}-${slotIndex}`"
                        class="schedule-preview__time"
                      >
                        {{ slot }}
                      </span>
                    </div>
                    <template v-else>—</template>
                  </td>
                </tr>
              </tbody>
            </table>
            <p v-else class="site-settings__hint">
              Nenhum horário disponível. Cadastre turmas ativas com dia da semana em
              <RouterLink to="/admin/classes">Turmas</RouterLink>.
            </p>
          </div>
        </template>
      </section>
    </div>
  </BaseLayout>
</template>

<style scoped lang="scss">
.site-settings {
  display: grid;
  gap: 1rem;
  width: 100%;
  max-width: 100%;
  min-width: 0;
}

.site-settings__toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.site-settings__plan {
  padding: 0.35rem 0.75rem;
  border-radius: 999px;
  background: #111827;
  color: #fff;
  font-size: 0.8rem;
  font-weight: 600;
}

.site-settings__tabs {
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
  overflow-y: hidden;
  border-bottom: 1px solid #e5e7eb;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
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
  max-width: 100%;
  min-width: 0;
  overflow-x: hidden;
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
.site-settings__section h2,
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

.site-settings__section--divider {
  padding-top: 1.25rem;
  border-top: 1px solid #e5e7eb;
}

.site-settings__pwa-logo {
  display: grid;
  gap: 0.75rem;
}

.site-settings__pwa-logo-reset {
  justify-self: start;
}

.site-settings__grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr));
  gap: 1rem;
}

.site-settings__grid--colors {
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 160px), 1fr));
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
    max-width: 100%;
    box-sizing: border-box;
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

.site-settings__logo-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 240px), 1fr));
  gap: 1rem;
}

.site-settings__crop-error {
  color: #dc2626;
}

.schedule-preview__table {
  width: 100%;
  border-collapse: collapse;

  th,
  td {
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    text-align: center;
  }

  thead th {
    background: #f9fafb;
    font-weight: 600;
  }

  tbody th {
    font-weight: 600;
    background: #fff;
  }
}

.schedule-preview__table--matrix th:first-child,
.schedule-preview__table--matrix tbody th {
  text-align: left;
  white-space: nowrap;
}

.schedule-preview__times {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
}

.schedule-preview__time {
  white-space: nowrap;
}

.schedule-order {
  display: grid;
  gap: 0.75rem;
}

.schedule-order__title {
  margin: 0;
  font-size: 1rem;
}

.schedule-order__list {
  display: grid;
  gap: 0.5rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

.schedule-order__item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.65rem 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #f9fafb;
}

.schedule-order__item--inactive {
  opacity: 0.72;
}

.schedule-order__name {
  flex: 1;
  font-weight: 600;
  color: #111827;
}

.schedule-order__badge {
  font-size: 0.75rem;
  color: #6b7280;
}

.schedule-order__actions {
  display: flex;
  gap: 0.35rem;
}

.schedule-order__btn {
  width: 2rem;
  height: 2rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: #fff;
  cursor: pointer;
  font-size: 0.95rem;
  line-height: 1;
}

.schedule-order__btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.schedule-order__btn:not(:disabled):hover {
  background: #f3f4f6;
}

.carousel-editor {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 160px));
  justify-content: start;
  gap: 0.75rem;
}

.carousel-editor__item {
  display: grid;
  gap: 0.5rem;
  margin: 0;
  width: 100%;
  max-width: 160px;
}

.carousel-editor__item img,
.carousel-editor__placeholder {
  width: 100%;
  aspect-ratio: 16 / 9;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  object-fit: cover;
  background: #f3f4f6;
}

.carousel-editor__placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem;
  color: #4b5563;
  font-size: 0.8rem;
  text-align: center;
  word-break: break-word;
}

.btn-secondary,
a.btn-secondary {
  border: none;
  border-radius: 6px;
  cursor: pointer;
  padding: 9px 14px;
  font-size: 0.875rem;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
}

.btn-secondary {
  background: #e5e7eb;
  color: #111827;
}

.btn-danger {
  border: none;
  border-radius: 6px;
  cursor: pointer;
  padding: 9px 14px;
  font-size: 0.875rem;
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
  min-width: 0;

  span,
  small {
    display: block;
    color: #6b7280;
  }

  p {
    margin: 0.35rem 0;
  }
}

.review-list__item--pending {
  border-color: #fcd34d;
  background: #fffbeb;
}

.review-list__content {
  min-width: 0;
  flex: 1;
}

.review-list__head {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.review-list__photo {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.review-form__remove-photo {
  justify-self: start;
}

.review-list__actions {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  flex-shrink: 0;
  flex-wrap: wrap;
}

.btn-primary {
  justify-self: start;
  max-width: 100%;
}

@media (max-width: 1024px) {
  .site-settings__section-head {
    flex-wrap: wrap;
  }
}

@media (max-width: 768px) {
  .site-settings__tab {
    padding: 0.625rem 0.75rem;
    font-size: 0.875rem;
  }

  .site-settings__card {
    padding: 0.875rem;
    border-radius: 8px;
  }

  .site-settings__heading,
  .site-settings__section-head {
    flex-direction: column;
    align-items: stretch;
  }

  .site-settings__heading .btn-primary,
  .site-settings__section-head .btn-secondary,
  .site-settings__section-head .btn-danger,
  .site-settings form > .btn-primary {
    width: 100%;
    text-align: center;
  }

  .site-settings__grid,
  .site-settings__grid--colors {
    grid-template-columns: 1fr;
  }

  .site-settings__logo-grid {
    grid-template-columns: 1fr;
  }

  .review-list__item {
    flex-direction: column;
  }

  .review-list__actions {
    width: 100%;
  }

  .review-list__actions .btn-secondary,
  .review-list__actions .btn-danger {
    flex: 1;
    min-width: 0;
  }
}

</style>
