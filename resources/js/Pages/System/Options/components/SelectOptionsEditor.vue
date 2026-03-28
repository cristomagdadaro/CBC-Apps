<template>
  <div class="space-y-4">
    <div>
      <label class="block text-sm font-medium text-gray-900">
        Select Options <span class="text-red-500">*</span>
      </label>
      <p class="mt-1 text-xs text-gray-500">Define the available options for this select field</p>
    </div>

    <div class="space-y-3">
      <div
        v-for="(option, index) in localOptions"
        :key="index"
        class="flex gap-2 rounded-lg border border-gray-200 p-3"
      >
        <div class="flex-1 space-y-2">
          <input
            v-model="option.value"
            type="text"
            placeholder="Value (e.g., option_1)"
            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
          <input
            v-model="option.label"
            type="text"
            placeholder="Label (e.g., Option 1)"
            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
        </div>
        <button
          type="button"
          @click="removeOption(index)"
          class="text-red-600 hover:text-red-900 self-center"
        >
          ✕
        </button>
      </div>
    </div>

    <button
      type="button"
      @click="addOption"
      class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
    >
      + Add Option
    </button>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Array, Object],
    default: () => [],
  },
})

const emit = defineEmits(['update:modelValue'])

const localOptions = ref([])

// Initialize from modelValue
watch(
  () => props.modelValue,
  (newVal) => {
    if (typeof newVal === 'string') {
      try {
        localOptions.value = normalizeOptions(JSON.parse(newVal) || [])
      } catch {
        localOptions.value = []
      }
    } else if (Array.isArray(newVal)) {
      localOptions.value = normalizeOptions(newVal)
    } else {
      localOptions.value = []
    }
  },
  { immediate: true }
)

// Watch for changes and emit
watch(localOptions, (newVal) => {
  emit('update:modelValue', JSON.stringify(newVal))
}, { deep: true })

const addOption = () => {
  localOptions.value.push({ value: '', label: '' })
}

const removeOption = (index) => {
  localOptions.value.splice(index, 1)
}

const normalizeOptions = (options) => {
  if (!Array.isArray(options)) {
    return []
  }

  return options.map((option) => ({
    value: String(option?.value ?? option?.name ?? ''),
    label: String(option?.label ?? option?.name ?? option?.value ?? ''),
  }))
}
</script>
