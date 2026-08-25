<script>
import { ArrowUpRight } from "lucide-vue-next";

export default {
    name: "OutgoingTransactionLink",
    components: { ArrowUpRight },
};
</script>

<template>
    <transition-container
        type="pop-in"
        :duration="500">
        <nav-link
            v-show="!route().current('transactions.outgoing')"
            :href="route('transactions.outgoing')"
            class="shadow-xs inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition-all hover:bg-slate-50 active:scale-95 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
            <ArrowUpRight class="h-4 w-4 text-rose-500" />
            <span>Outgoing</span>
        </nav-link>
    </transition-container>
</template>
