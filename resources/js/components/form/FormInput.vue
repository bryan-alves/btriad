<script setup>
import { vMaska } from 'maska/vue'

const props = defineProps({
  modelValue: String,
  type: {
    type: String,
    default: 'text'
  },
  label: String,
  placeholder: String,
  error: {
    type: String,
    default: ''
  },
  mask: String
})
</script>

<template>
  <div>
    <label class="font-medium">{{ label }}</label>
    <input v-maska="mask" :type="type" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)"
      :placeholder="placeholder"
      :class="[
        'w-full border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500',
        props.type === 'color' ? 'color-input' : 'px-3 py-2',
      ]" />
    <span class="text-red-500 text-sm">{{ error }}</span>
  </div>
</template>

<style scoped>
.color-input {
  height: 44px;
  min-width: 100%;
  padding: 4px;
  cursor: pointer;
}

.color-input::-webkit-color-swatch-wrapper {
  padding: 0;
}

.color-input::-webkit-color-swatch {
  border: none;
  border-radius: 6px;
}

.color-input::-moz-color-swatch {
  border: none;
  border-radius: 6px;
}
</style>