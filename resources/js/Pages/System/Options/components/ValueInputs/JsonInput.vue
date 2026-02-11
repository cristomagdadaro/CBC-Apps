<template>
  <div class="space-y-2">
    <textarea
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      rows="8"
      placeholder='{"key": "value"}'
      class="block w-full font-mono text-sm rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
    ></textarea>
    <div class="text-xs text-gray-500">
      Valid JSON format
    </div>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: [String, Object],
    default: '',
  },
})

defineEmits(['update:modelValue'])
</script>
