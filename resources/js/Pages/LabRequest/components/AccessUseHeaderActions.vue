<script>
import { Link } from "@inertiajs/vue3";

export default {
    name: "AccessUseHeaderActions",
    components: { Link }
}
</script>

<template>
    <ActionHeaderLayout 
        title="Facilities, Equipment, and Supplies Request Form" 
        subtitle="Manage your requests for facilities, equipment, and supplies here." 
        :route-link="route('suppliers.index')"
    >
        <transition-container type="pop-in" :duration="500">
            <Link 
                :href="route('labReq.guest.index')" 
                target="_blank"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-sm transition-all active:scale-95 shrink-0"
            >
                <lu-external-link class="w-4 h-4 text-indigo-500" />
                <span>Open Request Form</span>
            </Link>
        </transition-container>
    </ActionHeaderLayout>
</template>

<style scoped>
</style>