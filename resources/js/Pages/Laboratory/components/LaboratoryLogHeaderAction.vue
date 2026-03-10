<script>
import { Link } from "@inertiajs/vue3";

export default {
    name: "LaboratoryLogHeaderAction",
    components: { Link },
};
</script>

<template>
    <ActionHeaderLayout title="Equipment Logger" subtitle="Track equipment usage and compliance.">
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
    </ActionHeaderLayout>
</template>
