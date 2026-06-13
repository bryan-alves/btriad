<script setup>
import '../css/app.css'
import { onMounted, onUnmounted, ref } from 'vue'

const toast = ref(null)
let toastTimeout = null

function hideToast() {
  toast.value = null
  if (toastTimeout) {
    clearTimeout(toastTimeout)
    toastTimeout = null
  }
}

function onToast(event) {
  const detail = event.detail || {}
  const type = detail.type === 'error' ? 'danger' : (detail.type || 'info')

  toast.value = {
    message: detail.message || '',
    type,
  }

  if (toastTimeout) clearTimeout(toastTimeout)
  toastTimeout = setTimeout(hideToast, detail.duration || 3500)
}

onMounted(() => {
  window.addEventListener('app-toast', onToast)
})

onUnmounted(() => {
  window.removeEventListener('app-toast', onToast)
  if (toastTimeout) clearTimeout(toastTimeout)
})
</script>

<template>
  <RouterView />
  <Transition name="toast">
    <div
      v-if="toast"
      class="app-toast"
      :class="`app-toast--${toast.type}`"
      role="status"
      aria-live="polite"
    >
      <span class="app-toast__icon" aria-hidden="true">{{ toast.type === 'success' ? '✓' : toast.type === 'danger' ? '!' : 'i' }}</span>
      <span class="app-toast__message">{{ toast.message }}</span>
      <button type="button" class="app-toast__close" aria-label="Fechar aviso" @click="hideToast">&times;</button>
    </div>
  </Transition>
</template>

<style scoped>
.app-toast {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 9999;
  display: flex;
  align-items: center;
  gap: 0.65rem;
  max-width: min(420px, calc(100vw - 2rem));
  padding: 0.875rem 1rem;
  border-radius: 10px;
  color: #fff;
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.22);
  font-weight: 600;
  border: 1px solid transparent;
}

.app-toast__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 1.35rem;
  height: 1.35rem;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.18);
  font-size: 0.85rem;
  flex-shrink: 0;
}

.app-toast__message {
  flex: 1;
  line-height: 1.35;
}

.app-toast--success {
  background: #16a34a;
  border-color: #15803d;
}

.app-toast--danger {
  background: #dc2626;
  border-color: #b91c1c;
}

.app-toast--info {
  background: #2563eb;
  border-color: #1d4ed8;
}

.app-toast__close {
  border: none;
  background: transparent;
  color: inherit;
  cursor: pointer;
  font-size: 1.25rem;
  line-height: 1;
  padding: 0;
  flex-shrink: 0;
}

.toast-enter-active,
.toast-leave-active {
  transition: opacity 0.18s ease, transform 0.18s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
