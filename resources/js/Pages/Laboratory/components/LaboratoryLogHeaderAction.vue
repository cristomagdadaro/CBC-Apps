<script>
import { Link } from "@inertiajs/vue3";

export default {
    name: "LaboratoryLogHeaderAction",
    components: { Link },
};
</script>

<template>
    <ActionHeaderLayout title="Equipment Logger" subtitle="Track equipment usage and compliance.">
        <div class="flex items-center gap-2">
            <Link
                :href="route('laboratory.equipments.show')"
                class="inline-flex items-center gap-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold px-4 py-2 text-xs sm:text-sm shadow-xs transition-all active:scale-95"
            >
                <LuFileText class="w-4 h-4" />
                <span>Form</span>
            </Link>
            
            <Link
                :href="route('items.index')"
                class="inline-flex items-center gap-1.5 rounded-xl bg-lime-600 hover:bg-lime-700 text-white font-semibold px-4 py-2 text-xs sm:text-sm shadow-xs transition-all active:scale-95"
            >
                <LuBox class="w-4 h-4" />
                <span>Equipment Inventory</span>
            </Link>
        </div>
    </ActionHeaderLayout>
</template>
