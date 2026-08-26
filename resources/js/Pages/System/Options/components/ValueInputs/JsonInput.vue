<template>
    <div class="space-y-2.5">
        <textarea
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            rows="8"
            placeholder='{&#10;  "key": "value"&#10;}'
            class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-mono text-sm leading-relaxed text-slate-900 shadow-sm transition-colors placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"></textarea>

        <div class="flex items-center gap-1.5 px-1">
            <LuCode2 class="h-3.5 w-3.5 text-slate-400 dark:text-slate-500" />
            <span class="text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">Valid JSON format</span>
        </div>
    </div>
</template>

<script setup>
defineProps({
    modelValue: {
        type: [String, Object],
        default: "",
    },
});

defineEmits(["update:modelValue"]);
</script>
