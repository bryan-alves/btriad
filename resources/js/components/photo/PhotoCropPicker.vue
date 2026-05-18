<script setup lang="ts">
import { ref } from 'vue'
import PhotoCropModal from './PhotoCropModal.vue'
import { validatePhotoFile } from '../../utils/croppedPhotoFile'

const emit = defineEmits<{
  cropped: [file: File]
  error: [message: string]
}>()

const inputRef = ref<HTMLInputElement | null>(null)
const cropOpen = ref(false)
const cropSrc = ref('')
let objectUrl: string | null = null

function cleanupSrc() {
  if (objectUrl) {
    URL.revokeObjectURL(objectUrl)
    objectUrl = null
  }
  cropSrc.value = ''
}

function pick() {
  inputRef.value?.click()
}

function onFileChange(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0]
  input.value = ''
  if (!file) return

  const validationError = validatePhotoFile(file)
  if (validationError) {
    emit('error', validationError)
    return
  }

  cleanupSrc()
  objectUrl = URL.createObjectURL(file)
  cropSrc.value = objectUrl
  cropOpen.value = true
}

function onConfirm(file: File) {
  cropOpen.value = false
  cleanupSrc()
  emit('cropped', file)
}

function onCancel() {
  cropOpen.value = false
  cleanupSrc()
}

defineExpose({ pick })
</script>

<template>
  <input
    ref="inputRef"
    type="file"
    class="photo-crop-picker__input"
    accept="image/jpeg,image/png,image/webp,image/gif"
    @change="onFileChange"
  />
  <PhotoCropModal :open="cropOpen" :src="cropSrc" @confirm="onConfirm" @cancel="onCancel" />
</template>

<style scoped>
.photo-crop-picker__input {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}
</style>
