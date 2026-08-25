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
                class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50 active:scale-95 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                <lu-plus class="h-4 w-4 text-emerald-500" />
                <span>New Go Link</span>
            </Link>
        </transition-container>
    </ActionHeaderLayout>
</template>
