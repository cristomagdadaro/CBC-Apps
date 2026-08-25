<template>
    <base-btn
        :disabled="disabled"
        :classes="disabled ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 border border-slate-200 dark:border-slate-800 opacity-60 cursor-not-allowed' : 'bg-lime-600 hover:bg-lime-700 active:scale-95 text-white font-bold shadow-xs'"
        class="text-xs font-semibold rounded-xl px-3 py-1.5 transition-all">
        <template v-slot:icon>
            <slot name="icon" />
        </template>
        <slot />
    </base-btn>
</template>
<script setup>
defineProps({
    disabled: Boolean,
});
</script>
