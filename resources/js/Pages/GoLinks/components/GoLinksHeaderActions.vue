<script>
import { Link } from "@inertiajs/vue3";

export default {
    name: "GoLinksHeaderActions",
    components: { Link },
};
</script>

<template>
    <ActionHeaderLayout
        title="Go Link Manager"
        subtitle="Manage branded short links backed by the WordPress redirects table."
        :route-link="route('golinks.index')">
        <transition-container
            type="pop-in"
            :duration="500">
            <Link
                v-if="!route().current('golinks.create')"
                :href="route('golinks.create')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-sm transition-all active:scale-95 shrink-0">
                <lu-plus class="w-4 h-4 text-emerald-500" />
                <span>New Go Link</span>
            </Link>
        </transition-container>
    </ActionHeaderLayout>
</template>
