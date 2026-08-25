<script setup>
import { computed } from "vue";
import * as Icons from "@/Components/Icons";

const props = defineProps({
    title: { type: String, required: true },
    description: { type: String, default: "" },
    icon: { type: [Object, Function, String], required: true },
    maxWidth: { type: String, default: "max-w-4xl" },
    minHeight: { type: String, default: "" },
});

const iconComponent = computed(() => {
    if (typeof props.icon === "string") {
        return Icons[props.icon] || props.icon;
    }
    return props.icon;
});
</script>

<template>
    <div :class="['mx-auto w-full space-y-6 text-slate-900 dark:text-slate-100', maxWidth]">
        <!-- Main Container -->
        <div class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white/80 shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80">
            <!-- Header -->
            <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-5 dark:border-slate-800/60 dark:bg-slate-800/20">
                <div class="flex items-center gap-3.5">
                    <div class="shrink-0 rounded-xl border border-indigo-100 bg-indigo-50 p-2.5 shadow-sm dark:border-indigo-500/20 dark:bg-indigo-500/10">
                        <component
                            :is="iconComponent"
                            class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">
                            {{ title }}
                        </h2>
                        <p
                            v-if="description"
                            class="mt-0.5 text-xs font-medium leading-relaxed text-slate-500 dark:text-slate-400">
                            {{ description }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Optional Header Tabs Slot -->
            <slot name="header-tabs" />

            <!-- Content Area -->
            <div :class="['space-y-8 p-6', minHeight]">
                <slot />
            </div>
        </div>
    </div>
</template>
