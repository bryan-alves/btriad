<script setup>
import { onMounted, ref, watch } from 'vue';

const emit = defineEmits(['tab']);

const activeTab = ref('');

const props = defineProps({
  tabs: {
    type: Array,
  },
  selectedTab: {
    type: String,
  }
})

function handleClick(id) {
  activeTab.value = id;
  emit('tab', id)
}

watch(() => props.selectedTab, (newVal, oldVal) => {
  activeTab.value = newVal;
})

onMounted(() => {
  activeTab.value = props.selectedTab;
})
</script>

<template>
  <div class="tabs">
    <button v-for="tab in tabs" :class="['tab', { active: activeTab === tab.id }]" @click="handleClick(tab.id)">
      {{ tab.name }}
    </button>
  </div>
</template>

<style lang="scss">
.tabs {
  display: inline-flex;
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden; // 🔥 deixa tudo colado
}

.tab {
  padding: 10px 16px;
  background: #f9f9f9;
  border: none;
  cursor: pointer;
  font-size: 14px;

  // remove espaçamento entre botões
  &:not(:last-child) {
    border-right: 1px solid #ddd;
  }

  &:hover {
    background: #f1f1f1;
  }
}


.tab.active {
  background: #111;
  color: white;
}
</style>