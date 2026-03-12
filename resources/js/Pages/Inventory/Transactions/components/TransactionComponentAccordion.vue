<script>
export default {
    name: 'TransactionComponentAccordion',
    props: {
        components: {
            type: Array,
            default: () => [],
        },
        title: {
            type: String,
            default: 'Attached Components',
        },
    },
    computed: {
        hasComponents() {
            return Array.isArray(this.components) && this.components.length > 0;
        },
    },
};
</script>

<template>
    <div class="border rounded-md overflow-hidden">
        <div class="flex items-center justify-between bg-gray-100 dark:bg-gray-700 px-3 py-2">
            <h3 class="text-sm font-semibold">{{ title }}</h3>
            <span class="text-[10px] text-gray-500">{{ components.length }} linked</span>
        </div>

        <div v-if="!hasComponents" class="p-3 text-xs text-gray-500">
            No components linked to this transaction yet.
        </div>

        <div v-else class="divide-y divide-gray-200 dark:divide-gray-600">
            <div v-for="(component, index) in components" :key="component.id ?? index" class="p-3 text-sm grid grid-cols-2 gap-2">
                <div class="col-span-2 font-medium">
                    {{ component?.item?.name ?? 'Unnamed Component' }}
                    <span v-if="component?.item?.brand" class="text-xs text-gray-500">({{ component.item.brand }})</span>
                </div>
                <div>
                    <span class="text-gray-500">Qty:</span>
                    {{ component.quantity }} {{ component.unit ?? '' }}
                </div>
                <div>
                    <span class="text-gray-500">Unit:</span>
                    {{ component.unit ?? '-' }}
                </div>
                <div>
                    <span class="text-gray-500">PRRI Barcode:</span>
                    {{ component.barcode_prri ?? '-' }}
                </div>
                <div>
                    <span class="text-gray-500">Component No:</span>
                    {{ component.prri_component_no ?? '-' }}
                </div>
                <div>
                    <span class="text-gray-500">Expiry Date:</span>
                    {{ component.expiration ?? '-' }}
                </div>
                <div v-if="component.remarks" class="col-span-2">
                    <span class="text-gray-500">Remarks:</span>
                    {{ component.remarks }}
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
</style>
