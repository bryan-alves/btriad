<script setup lang="ts">
import { computed, ref } from 'vue'
import PhotoCropPicker from './PhotoCropPicker.vue'
import type { CropPreset } from '../../utils/croppedPhotoFile'

const props = withDefaults(
  defineProps<{
    label: string
    previewUrl?: string | null
    previewAlt?: string
    previewClass?: string
    preset: CropPreset
    buttonLabel?: string
    hint?: string
    disabled?: boolean
  }>(),
  {
    previewUrl: null,
    previewAlt: '',
    previewClass: '',
    buttonLabel: '',
    hint: '',
    disabled: false,
  },
)

const emit = defineEmits<{
  cropped: [file: File]
  error: [message: string]
}>()

const pickerRef = ref<InstanceType<typeof PhotoCropPicker> | null>(null)
const localPreviewUrl = ref<string | null>(null)

const displayPreviewUrl = computed(() => localPreviewUrl.value || props.previewUrl || null)

function onCropped(file: File) {
  if (localPreviewUrl.value) {
    URL.revokeObjectURL(localPreviewUrl.value)
  }
  localPreviewUrl.value = URL.createObjectURL(file)
  emit('cropped', file)
}

function pick() {
  if (props.disabled) return
  pickerRef.value?.pick()
}
</script>

<template>
  <div class="image-crop-field">
    <span class="image-crop-field__label">{{ label }}</span>
    <div v-if="displayPreviewUrl" class="image-crop-field__preview" :class="previewClass">
      <img :src="displayPreviewUrl" :alt="previewAlt || label" />
    </div>
    <PhotoCropPicker
      ref="pickerRef"
      :aspect-ratio="preset.aspectRatio"
      :output-width="preset.outputWidth"
      :output-height="preset.outputHeight"
      :title="preset.title"
      :hint="preset.hint"
      :filename-prefix="preset.filenamePrefix"
      @cropped="onCropped"
      @error="emit('error', $event)"
    />
    <button type="button" class="image-crop-field__btn" :disabled="disabled" @click="pick">
      {{ buttonLabel || (displayPreviewUrl ? 'Alterar imagem' : 'Adicionar imagem') }}
    </button>
    <p v-if="hint" class="image-crop-field__hint">{{ hint }}</p>
  </div>
</template>

<style scoped lang="scss">
.image-crop-field {
  display: grid;
  gap: 0.5rem;
}

.image-crop-field__label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

.image-crop-field__preview {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 0.75rem;
  background: #f9fafb;
  max-width: 100%;
  overflow: hidden;

  img {
    display: block;
    max-width: 100%;
    height: auto;
    object-fit: contain;
  }
}

.image-crop-field__preview--nav img {
  max-height: 44px;
}

.image-crop-field__preview--footer img {
  max-width: 160px;
}

.image-crop-field__preview--hero img {
  max-width: 200px;
}

.image-crop-field__preview--app img {
  max-width: 150px;
  max-height: 120px;
}

.image-crop-field__preview--profile img {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
}

.image-crop-field__preview--carousel img {
  width: 100%;
  aspect-ratio: 16 / 9;
  object-fit: cover;
}

.image-crop-field__btn {
  justify-self: start;
  border: none;
  border-radius: 6px;
  background: #e5e7eb;
  color: #111827;
  cursor: pointer;
  padding: 9px 14px;
  font-size: 0.875rem;
  font-weight: 600;

  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
}

.image-crop-field__hint {
  margin: 0;
  font-size: 0.75rem;
  color: #6b7280;
  line-height: 1.4;
}
</style>
