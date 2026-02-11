<script>
import { Link } from "@inertiajs/vue3";

export default {
    name: "LaboratoryLogHeaderAction",
    components: { Link },
};
</script>

<template>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <Link
                :href="route('laboratory.dashboard')"
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Laboratory Logs
            </Link>
            <p class="text-xs text-gray-500">Track equipment usage and compliance.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <Link
                :href="route('laboratory.equipments.show')"
                class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-blue-700"
            >
                Form
            </Link>
            <Link
                :href="route('items.index')"
                class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-blue-700"
            >
                Equipment Inventory
            </Link>
        </div>
    </div>
</template>
