<script setup lang="ts">
import { ref } from 'vue'
import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'
import { blobToCroppedPhotoFile, PHOTO_OUTPUT_SIZE } from '../../utils/croppedPhotoFile'

const props = defineProps({
  open: { type: Boolean, default: false },
  src: { type: String, default: '' },
})

const emit = defineEmits<{
  confirm: [file: File]
  cancel: []
}>()

const cropperRef = ref<InstanceType<typeof Cropper> | null>(null)
const processing = ref(false)

function onCancel() {
  if (processing.value) return
  emit('cancel')
}

function onConfirm() {
  if (!cropperRef.value || processing.value) return
  processing.value = true
  try {
    const result = cropperRef.value.getResult({
      size: {
        width: PHOTO_OUTPUT_SIZE,
        height: PHOTO_OUTPUT_SIZE,
      },
    })
    const canvas = result?.canvas
    if (!canvas) {
      processing.value = false
      return
    }
    canvas.toBlob(
      (blob) => {
        processing.value = false
        if (!blob) return
        emit('confirm', blobToCroppedPhotoFile(blob))
      },
      'image/jpeg',
      0.92,
    )
  } catch {
    processing.value = false
  }
}
</script>

<template>
  <Teleport to="body">
    <div v-if="open && src" class="photo-crop-modal" role="dialog" aria-modal="true" aria-labelledby="photo-crop-title">
      <button type="button" class="photo-crop-modal__backdrop" aria-label="Fechar" @click="onCancel" />
      <div class="photo-crop-modal__panel">
        <h3 id="photo-crop-title" class="photo-crop-modal__title">Ajustar foto (1:1)</h3>
        <p class="photo-crop-modal__hint">
          Enquadre o rosto no quadrado. A foto será usada na escola em formato 1:1.
        </p>
        <div class="photo-crop-modal__cropper-wrap">
          <Cropper
            ref="cropperRef"
            :src="src"
            :stencil-props="{ aspectRatio: 1 }"
            :default-size="{
              width: 280,
              height: 280,
            }"
            class="photo-crop-modal__cropper"
          />
        </div>
        <div class="photo-crop-modal__actions">
          <button type="button" class="photo-crop-modal__btn photo-crop-modal__btn--ghost" :disabled="processing" @click="onCancel">
            Cancelar
          </button>
          <button type="button" class="photo-crop-modal__btn photo-crop-modal__btn--primary" :disabled="processing" @click="onConfirm">
            {{ processing ? 'Processando…' : 'Usar foto' }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.photo-crop-modal {
  position: fixed;
  inset: 0;
  z-index: 10050;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.photo-crop-modal__backdrop {
  position: absolute;
  inset: 0;
  border: none;
  background: rgba(15, 23, 42, 0.65);
  cursor: pointer;
}

.photo-crop-modal__panel {
  position: relative;
  z-index: 1;
  width: min(100%, 28rem);
  background: #fff;
  border-radius: 12px;
  padding: 1.25rem;
  box-shadow: 0 24px 48px rgba(0, 0, 0, 0.2);
}

.photo-crop-modal__title {
  margin: 0 0 0.35rem;
  font-size: 1.125rem;
  font-weight: 700;
  color: #111827;
}

.photo-crop-modal__hint {
  margin: 0 0 1rem;
  font-size: 0.8125rem;
  color: #6b7280;
  line-height: 1.45;
}

.photo-crop-modal__cropper-wrap {
  width: 100%;
  height: min(55vh, 22rem);
  background: #111827;
  border-radius: 8px;
  overflow: hidden;
}

.photo-crop-modal__cropper {
  height: 100%;
}

.photo-crop-modal__actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 1rem;
}

.photo-crop-modal__btn {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  font-weight: 600;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  cursor: pointer;
}

.photo-crop-modal__btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.photo-crop-modal__btn--ghost {
  background: #fff;
  color: #374151;
}

.photo-crop-modal__btn--primary {
  background: #111827;
  color: #fff;
  border-color: #111827;
}
</style>
