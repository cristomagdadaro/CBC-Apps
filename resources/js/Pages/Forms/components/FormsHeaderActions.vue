<script>
import { Link } from "@inertiajs/vue3";
// Ensure TransitionContainer and NavLink are imported or globally registered in your app

export default {
    name: "FormsHeaderActions",
    components: { Link },
};
</script>

<template>
    <ActionHeaderLayout
        title="Certificate Generator"
        subtitle="Bulk-generate and email certificates using PPTX template and XLSX data.">
        <!-- Create Event Button -->
        <transition-container
            type="pop-in"
            :duration="500">
            <Link
                v-show="!route().current('forms.create')"
                :href="route('forms.create')"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50 active:scale-95 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                <lu-plus-circle class="h-4 w-4 text-indigo-500" />
                <span>Create Event</span>
            </Link>
        </transition-container>

        <!-- Form Builder Button -->
        <transition-container
            type="pop-in"
            :duration="500">
            <Link
                v-show="!route().current('forms.builder')"
                :href="route('forms.builder')"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50 active:scale-95 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                <lu-layout-dashboard class="h-4 w-4 text-blue-500" />
                <span>Form Builder</span>
            </Link>
        </transition-container>

        <!-- Scan QR Button -->
        <transition-container
            type="pop-in"
            :duration="500">
            <Link
                v-show="!route().current('forms.scan')"
                :href="route('forms.scan')"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50 active:scale-95 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                <lu-qr-code class="h-4 w-4 text-emerald-500" />
                <span>Scan QR</span>
            </Link>
        </transition-container>
    </ActionHeaderLayout>
</template>

<style scoped>
/* Scoped styles can be added here if needed, but Tailwind handles the design */
</style>
