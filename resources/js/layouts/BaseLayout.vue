<script setup>
import { RouterLink } from 'vue-router';
import Header from '../components/layout/Header.vue';
import SideBar from '../components/layout/SideBar.vue';

const props = defineProps({
  title: {
    type: String,
    default: "Dashboard"
  },
  action: {
    type: String,
    default: ''
  }
})
</script>

<template>
  <div style="display: flex;">
    <SideBar />
    <div style="display: flex; flex-direction: column; width: 100%;">
      <Header />
      <div class="px-10 py-8">
        <div
          style="display: flex; justify-content: space-between; width: 100%;align-items: center; border-bottom: 1px solid #DDD;padding-bottom: .5rem;margin-bottom: .5rem">
          <h1 style="font-size: 36px;font-weight: 600;">{{ title }}</h1>

          <router-link to="/admin/students/create" class="btn-primary" v-if="action">
            + {{ action }}
          </router-link>
        </div>
        <slot />
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.header {
  width: calc(100% - 250px);
  height: 60px;
  background-color: #1b1b18;
  display: flex;
  justify-content: space-between;
}

.sidebar {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 250px;
  min-height: 100vh;
  background-color: #1b1b18;
  padding: 20px 20px;
}

ul {
  list-style: none;
  padding: 0;
  width: 100%;
}

li {
  margin: 10px 0;
  background-color: #333;
  width: 100%;
  padding: 10px;

  a {
    width: 100%;
    display: block;
  }
}

.btn-primary {
  background: black;
  color: white;
  border: none;
  padding: 9px 14px;
  border-radius: 6px;
  cursor: pointer;
}
</style>
