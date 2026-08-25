<template>
    <div class="flex flex-col gap-0.5">
        <div class="text-[0.68rem] sm:text-xs font-semibold text-slate-500 dark:text-slate-400 flex items-center justify-between">
            <span class="flex gap-0.5 whitespace-nowrap">Per Page</span>
        </div>
        <custom-dropdown
            :show-clear="false"
            :value="value"
            :withAllOption="false"
            :options="perPage"
            @selectedChange="$emit('changePerPage', $event)"
            :show-valid-indicator="false">
            <template #icon>
                <caret-down class="h-4 w-4 text-slate-600 dark:text-slate-400" />
            </template>
        </custom-dropdown>
    </div>
</template>
<script>
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import CaretDown from "@/Components/Icons/CaretDown.vue";

export default {
    name: "PerPage",
    emits: ["changePerPage"],
    components: { CaretDown, CustomDropdown },
    props: {
        value: [String, Number],
    },
    data() {
        return {
            perPage: [
                { label: 25, name: 25, selected: true },
                { label: 50, name: 50, selected: false },
                { label: 100, name: 100, selected: false },
                { label: 1000, name: 1000, selected: false },
            ],
        };
    },
};
</script>
