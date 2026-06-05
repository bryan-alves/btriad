<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import { getAcademyName } from '../../utils/publicTenant'

defineProps({
  menuOpen: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['toggle-sidebar'])

const academyName = ref(getAcademyName())

function refreshTenantBrand() {
  academyName.value = getAcademyName()
}

onMounted(() => {
  window.addEventListener('app-tenant-updated', refreshTenantBrand)
})

onUnmounted(() => {
  window.removeEventListener('app-tenant-updated', refreshTenantBrand)
})
</script>

<template>
  <header class="header">
    <button
      type="button"
      class="menu-toggle"
      :aria-expanded="menuOpen"
      aria-controls="app-sidebar"
      aria-label="Abrir ou fechar menu"
      @click="$emit('toggle-sidebar')"
    >
      <span class="menu-toggle__bar" />
      <span class="menu-toggle__bar" />
      <span class="menu-toggle__bar" />
    </button>
    <h1 class="header__title">{{ academyName }}</h1>
  </header>
</template>

<style scoped lang="scss">
$breakpoint: 768px;

.header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  width: 100%;
  min-height: 56px;
  background-color: var(--app-header-color, #1b1b18);
  box-sizing: border-box;
}

.header__title {
  margin: 0;
  color: #fff;
  font-size: 1.125rem;
  font-weight: 600;
}

.menu-toggle {
  display: none;
  flex-direction: column;
  justify-content: center;
  gap: 5px;
  width: 44px;
  height: 44px;
  padding: 10px;
  border: none;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.08);
  cursor: pointer;
  flex-shrink: 0;

  &:hover {
    background: rgba(255, 255, 255, 0.14);
  }

  @media (max-width: ($breakpoint - 1px)) {
    display: flex;
  }
}

.menu-toggle__bar {
  display: block;
  height: 2px;
  width: 100%;
  background: #fff;
  border-radius: 1px;
}
</style>
