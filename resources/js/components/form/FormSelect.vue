<script setup>
defineProps({
  modelValue: String,
  type: {
    type: String,
    default: 'text'
  },
  label: String,
  placeholder: String,
  options: {
    type: Array,
    default: () => []
  },
  error: {
    type: String,
    default: ''
  }
})
</script>

<template>
  <div>
    <label class="font-medium">{{ label }}</label>
    <select :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" name="" id=""
      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
      <option v-for="option in options" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>
    <span class="text-red-500 text-sm">{{ error }}</span>
  </div>
</template>