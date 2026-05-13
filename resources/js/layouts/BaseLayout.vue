<script setup>
import { RouterLink, useRoute } from 'vue-router'
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import Header from '../components/layout/Header.vue'
import SideBar from '../components/layout/SideBar.vue'

const props = defineProps({
  title: {
    type: String,
    default: 'Dashboard',
  },
  action: {
    type: String,
    default: '',
  },
  actionRoute: {
    type: String,
    default: '',
  },
})

const route = useRoute()
const sidebarOpen = ref(false)

const defaultActionRoute = computed(() => {
  if (props.actionRoute) {
    return props.actionRoute
  }

  if (route.path.startsWith('/admin/attendance-lists')) {
    return '/admin/attendance-lists/create'
  }

  if (route.path.startsWith('/admin/student-graduations')) {
    return '/admin/student-graduations/create'
  }

  if (route.path.startsWith('/admin/classes')) {
    return '/admin/classes/create'
  }

  if (route.path.startsWith('/admin/students')) {
    return '/admin/students/create'
  }

  return '/admin/students/create'
})

function closeSidebar() {
  sidebarOpen.value = false
}

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
}

watch(
  () => route.fullPath,
  () => {
    closeSidebar()
  },
)

watch(sidebarOpen, (open) => {
  if (typeof document === 'undefined') return
  const isMobile = window.matchMedia('(max-width: 767px)').matches
  document.body.style.overflow = open && isMobile ? 'hidden' : ''
})

function onResize() {
  if (typeof window === 'undefined') return
  if (window.matchMedia('(min-width: 768px)').matches) {
    closeSidebar()
    document.body.style.overflow = ''
  }
}

onMounted(() => {
  window.addEventListener('resize', onResize)
  window.addEventListener('keydown', onEscape)
})

onUnmounted(() => {
  window.removeEventListener('resize', onResize)
  window.removeEventListener('keydown', onEscape)
  document.body.style.overflow = ''
})

function onEscape(e) {
  if (e.key === 'Escape') {
    closeSidebar()
  }
}
</script>

<template>
  <div class="app-shell">
    <div
      v-show="sidebarOpen"
      class="sidebar-backdrop"
      aria-hidden="true"
      @click="closeSidebar"
    />

    <aside
      id="app-sidebar"
      class="sidebar-aside"
      :class="{ 'sidebar-aside--open': sidebarOpen }"
    >
      <SideBar />
    </aside>

    <div class="main-column">
      <Header :menu-open="sidebarOpen" @toggle-sidebar="toggleSidebar" />
      <div class="main-column__body">
        <div class="page-heading">
          <h1 class="page-heading__title">{{ title }}</h1>

          <RouterLink
            v-if="action"
            :to="defaultActionRoute"
            class="btn-primary"
          >
            + {{ action }}
          </RouterLink>
        </div>
        <slot />
      </div>
    </div>
  </div>
</template>

<style lang="scss">
$sidebar-width: 250px;
$breakpoint: 768px;
$header-height: 56px;

.app-shell {
  display: flex;
  min-height: 100vh;
  position: relative;
}

.sidebar-backdrop {
  display: none;
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  top: $header-height;
  z-index: 90;
  background: rgba(0, 0, 0, 0.45);

  @media (max-width: ($breakpoint - 1px)) {
    display: block;
  }

  @media (min-width: $breakpoint) {
    display: none !important;
    pointer-events: none;
  }
}

.sidebar-aside {
  flex-shrink: 0;
  width: $sidebar-width;
  z-index: 100;

  @media (max-width: ($breakpoint - 1px)) {
    position: fixed;
    top: $header-height;
    left: 0;
    height: calc(100vh - #{$header-height});
    width: min(280px, 88vw);
    max-width: $sidebar-width;
    transform: translateX(-100%);
    transition: transform 0.22s ease;
    box-shadow: 4px 0 24px rgba(0, 0, 0, 0.25);
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;

    &--open {
      transform: translateX(0);
    }
  }
}

.main-column {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-width: 0;
  width: 100%;
}

.main-column__body {
  padding: 1.5rem 1rem;

  @media (min-width: $breakpoint) {
    padding: 2rem 2.5rem;
  }
}

.page-heading {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.75rem;
  width: 100%;
  border-bottom: 1px solid #ddd;
  padding-bottom: 0.5rem;
  margin-bottom: 0.5rem;
}

.page-heading__title {
  margin: 0;
  font-size: clamp(1.25rem, 4vw, 2.25rem);
  font-weight: 600;
}

.header {
  width: 100%;
  height: auto;
  min-height: $header-height;
  background-color: #1b1b18;
  display: flex;
  align-items: center;
  position: relative;
  z-index: 110;
  flex-shrink: 0;
}

.sidebar {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
  min-height: 100vh;
  background-color: #1b1b18;
  padding: 20px 20px;
  box-sizing: border-box;
}

@media (max-width: ($breakpoint - 1px)) {
  .sidebar-aside .sidebar {
    min-height: 100%;
  }
}

.sidebar ul {
  list-style: none;
  padding: 0;
  width: 100%;
}

.sidebar li {
  margin: 10px 0;
  background-color: #333;
  width: 100%;
  padding: 10px;

  a {
    width: 100%;
    display: block;
    color: #fff;
    text-decoration: none;
  }
}

.btn-primary {
  background: black;
  color: white;
  border: none;
  padding: 9px 14px;
  border-radius: 6px;
  cursor: pointer;
  text-decoration: none;
  font-size: 0.875rem;
  white-space: nowrap;
}
</style>
