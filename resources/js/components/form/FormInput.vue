<script setup>
defineProps({
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
  }
})
</script>

<template>
  <div>
    <label class="font-medium">{{ label }}</label>
    <input :type="type" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)"
      :placeholder="placeholder"
      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
    <span class="text-red-500 text-sm">{{ error }}</span>
  </div>
</template>