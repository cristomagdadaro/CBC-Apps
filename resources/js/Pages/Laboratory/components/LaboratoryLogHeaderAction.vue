<script>
import { Link } from "@inertiajs/vue3";

export default {
    name: "LaboratoryLogHeaderAction",
    components: { Link },
};
</script>

<template>
    <ActionHeaderLayout
        title="Equipment Logger"
        subtitle="Track equipment usage and compliance.">
        <div class="flex items-center gap-2">
            <Link
                :href="route('laboratory.equipments.show')"
                class="shadow-xs inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition-all hover:bg-slate-50 active:scale-95 sm:text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                <LuFileText class="h-4 w-4" />
                <span>Form</span>
            </Link>

            <Link
                :href="route('items.index')"
                class="shadow-xs inline-flex items-center gap-1.5 rounded-xl bg-lime-600 px-4 py-2 text-xs font-semibold text-white transition-all hover:bg-lime-700 active:scale-95 sm:text-sm">
                <LuBox class="h-4 w-4" />
                <span>Equipment Inventory</span>
            </Link>
        </div>
    </ActionHeaderLayout>
</template>
