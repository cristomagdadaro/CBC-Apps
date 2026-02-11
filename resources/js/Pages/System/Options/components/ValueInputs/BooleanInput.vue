<template>
  <select
    :value="modelValue === 'true' || modelValue === true || modelValue === '1' ? 'true' : 'false'"
    @change="$emit('update:modelValue', $event.target.value === 'true' ? 'true' : 'false')"
    class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
  >
    <option value="true">True</option>
    <option value="false">False</option>
  </select>
</template>

<script setup>
defineProps({
  modelValue: {
    type: [String, Boolean],
    default: 'false',
  },
})

defineEmits(['update:modelValue'])
</script>
