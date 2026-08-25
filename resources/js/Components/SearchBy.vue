<template>
    <custom-dropdown
        label="Search by"
        :show-clear="false"
        :value="value"
        placeholder="Columns"
        :options="options"
        @selectedChange="$emit('searchBy', $event)"
        :show-valid-indicator="false">
        <template #header-actions>
            <div
                class="flex items-center gap-1.5"
                title="Turn on exact match filter">
                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Exact</span>
                <input
                    type="checkbox"
                    v-model="is_exact"
                    @change="toggle"
                    class="h-3.5 w-3.5 cursor-pointer rounded-sm border-slate-300 text-indigo-600 transition-colors focus:ring-indigo-600 dark:border-slate-700 dark:bg-slate-900" />
            </div>
        </template>
        <template #label-icon>
            <LuFilter class="h-3.5 w-3.5 text-indigo-500 dark:text-indigo-400" />
        </template>
    </custom-dropdown>
</template>

<script>
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";

export default {
    name: "SearchBy",
    components: {
        CustomDropdown,
    },
    props: {
        options: {
            type: Array,
            required: false,
            default: () => [],
        },
        isExact: {
            type: Boolean,
            required: false,
            default: false,
        },
        value: {
            type: [String, Number],
            required: false,
            default: null,
        },
    },
    data() {
        return {
            selected: null,
            is_exact: this.isExact,
        };
    },
    methods: {
        toggle() {
            this.$emit("isExact", this.is_exact);
        },
    },
};
</script>
