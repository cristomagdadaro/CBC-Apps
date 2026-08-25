<template>
    <template v-if="visible">
        <th class="dtHeaderColumn text-light-color text-normal whitespace-nowrap">
            <div
                class="dtHeaderCell flex items-center justify-center gap-1 rounded-sm p-1 sm:rounded sm:py-2"
                :class="[{ 'hover:bg-opacity-70': sortable }, sortedColumnClass, { 'border-2 border-yellow-400': filteredValues }]">
                <template v-if="props.column">
                    <span class="dtHeaderCellText">
                        {{ props.column }}
                    </span>
                </template>
                <slot v-else />
                <span
                    class="dtHeaderCellSortIco text-xs text-yellow-500"
                    :class="sorted"></span>
            </div>
        </th>
    </template>
</template>
<script setup>
import { computed } from "vue";

const props = defineProps({
    order: {
        type: String,
        default: "asc",
    },
    sortable: {
        type: Boolean,
        default: false,
    },
    column: {
        type: String,
        default: null,
    },
    sortedValue: {
        type: Boolean,
        default: false,
    },
    visible: {
        type: Boolean,
        default: true,
    },
    filteredValues: {
        type: Boolean,
        default: false,
    },
});

const sorted = computed(() => {
    return props.sortable && props.sortedValue ? props.order : "";
});

const sortedColumnClass = computed(() => {
    return props.sortedValue ? "bg-gray-900" : "bg-gray-600";
});
</script>
<style>
.asc::after {
    content: "▲";
}

.desc::after {
    content: "▼";
}
</style>
