<script>
import { IdCard } from "lucide-vue-next";

export default {
    name: "PrintApprovedIdsLink",
    components: { IdCard },
};
</script>

<template>
    <transition-container
        type="pop-in"
        :duration="500">
        <nav-link
            v-show="!route().current('personnels.id-cards.print')"
            :href="route('personnels.id-cards.print')"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-xs transition-all active:scale-95 shrink-0">
            <IdCard class="w-4 h-4 text-lime-500 dark:text-lime-400" />
            <span>Print Approved IDs</span>
        </nav-link>
    </transition-container>
</template>
