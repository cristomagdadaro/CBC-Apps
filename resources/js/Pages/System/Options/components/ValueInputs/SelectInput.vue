<template>
  <select
    :value="normalizedValue"
    @change="$emit('update:modelValue', $event.target.value)"
    :disabled="!normalizedOptions.length"
    class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
    :class="!normalizedOptions.length ? 'cursor-not-allowed bg-gray-100 text-gray-500' : ''"
  >
    <option value="">{{ normalizedOptions.length ? 'Select a value' : 'Add choices below first' }}</option>
    <option v-if="showCurrentValue" :value="normalizedValue">
      Current: {{ normalizedValue }}
    </option>
    <option
      v-for="option in normalizedOptions"
      :key="`${option.value}-${option.label}`"
      :value="option.value"
    >
      {{ option.label }}
    </option>
  </select>
  <p class="mt-2 text-xs text-gray-500">
    {{
      normalizedOptions.length
        ? 'Pick the stored value that this option should use by default.'
        : 'Define at least one select choice below before choosing a default value.'
    }}
  </p>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  modelValue: {
    type: [String, Number, null],
    default: "",
  },
  options: {
    type: Array,
    default: () => [],
  },
});

defineEmits(["update:modelValue"]);

const normalizedValue = computed(() =>
  props.modelValue === null || props.modelValue === undefined
    ? ""
    : String(props.modelValue)
);

const normalizedOptions = computed(() =>
  (props.options || [])
    .map((option) => ({
      value: String(option?.value ?? option?.name ?? ""),
      label: String(option?.label ?? option?.name ?? option?.value ?? ""),
    }))
    .filter((option) => option.value !== "" || option.label !== "")
);

const showCurrentValue = computed(() => {
  return (
    normalizedValue.value !== "" &&
    !normalizedOptions.value.some((option) => option.value === normalizedValue.value)
  );
});
</script>