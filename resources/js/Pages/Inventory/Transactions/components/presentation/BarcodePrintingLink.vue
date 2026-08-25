<script>
import { Printer } from "lucide-vue-next";

export default {
    name: "BarcodePrintingLink",
    components: { Printer },
};
</script>

<template>
    <transition-container
        type="pop-in"
        :duration="500">
        <nav-link
            v-show="!route().current('inventory.barcodes.print')"
            :href="route('inventory.barcodes.print')"
            class="shadow-xs inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition-all hover:bg-slate-50 active:scale-95 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
            <Printer class="h-4 w-4 text-blue-500" />
            <span>Print Barcode</span>
        </nav-link>
    </transition-container>
</template>
