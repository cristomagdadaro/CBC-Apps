<template>
  <div class="space-y-2.5">
    <textarea
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      rows="8"
      placeholder='{&#10;  "key": "value"&#10;}'
      class="block w-full font-mono text-sm leading-relaxed rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-3 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 shadow-sm transition-colors"
    ></textarea>
    
    <div class="flex items-center gap-1.5 px-1">
      <LuCode2 class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" />
      <span class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
        Valid JSON format
      </span>
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