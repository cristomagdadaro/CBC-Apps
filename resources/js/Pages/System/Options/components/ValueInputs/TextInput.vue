<template>
  <input
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
    type="text"
    placeholder="Enter text value"
    class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
  />
</template>

<script setup>
defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
})

defineEmits(['update:modelValue'])
</script>
