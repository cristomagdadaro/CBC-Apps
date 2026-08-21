<script setup>
import { computed } from 'vue';
import * as Icons from '@/Components/Icons';

const props = defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    icon: { type: [Object, Function, String], required: true },
    maxWidth: { type: String, default: 'max-w-4xl' },
    minHeight: { type: String, default: '' }
});

const iconComponent = computed(() => {
    if (typeof props.icon === 'string') {
        return Icons[props.icon] || props.icon;
    }
    return props.icon;
});
</script>

<template>
    <div :class="['w-full mx-auto space-y-6 text-slate-900 dark:text-slate-100', maxWidth]">
        <!-- Main Container -->
        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-800 overflow-hidden">
            
            <!-- Header -->
            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/50 dark:bg-slate-800/20">
                <div class="flex items-center gap-3.5">
                    <div class="p-2.5 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 rounded-xl shadow-sm shrink-0">
                        <component :is="iconComponent" class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white tracking-tight">{{ title }}</h2>
                        <p v-if="description" class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">
                            {{ description }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Optional Header Tabs Slot -->
            <slot name="header-tabs" />

            <!-- Content Area -->
            <div :class="['p-6 space-y-8', minHeight]">
                <slot />
            </div>
        </div>
    </div>
</template>
