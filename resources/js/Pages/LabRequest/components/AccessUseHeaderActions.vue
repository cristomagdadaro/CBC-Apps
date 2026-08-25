<script>
import { Link } from "@inertiajs/vue3";

export default {
    name: "AccessUseHeaderActions",
    components: { Link },
};
</script>

<template>
    <ActionHeaderLayout
        title="Facilities, Equipment, and Supplies Request Form"
        subtitle="Manage your requests for facilities, equipment, and supplies here."
        :route-link="route('suppliers.index')">
        <transition-container
            type="pop-in"
            :duration="500">
            <Link
                :href="route('labReq.guest.index')"
                target="_blank"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50 active:scale-95 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                <lu-external-link class="h-4 w-4 text-indigo-500" />
                <span>Open Request Form</span>
            </Link>
        </transition-container>
    </ActionHeaderLayout>
</template>

<style scoped></style>
