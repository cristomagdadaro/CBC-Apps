<template>
    <div class="flex flex-col gap-0.5">
        <div class="text-[0.68rem] sm:text-xs font-semibold text-slate-500 dark:text-slate-400 flex items-center justify-between">
            <span class="flex gap-0.5 whitespace-nowrap">Search by</span>
            <div class="flex items-center gap-0.5">
                <label for="exactValue"></label>
                <input type="checkbox" v-model="is_exact" @click="toggle()" class="rounded-full h-3 w-4 text-lime-600 focus:outline-none focus:ring-0" title="turn on filter by exact value">
            </div>
        </div>
        <custom-dropdown :show-clear="false" :value="value" placeholder="Columns" :options="options" @selectedChange="$emit('searchBy', $event)" :show-valid-indicator="false">
            <template #icon>
                <filter-icon class="h-4 w-4 text-slate-600 dark:text-slate-400" />
            </template>
        </custom-dropdown>
    </div>
</template>
<script>
    import {ref} from "vue";
    import SearchIcon from "@/Components/Icons/SearchIcon.vue";
    import FilterIcon from "@/Components/Icons/FilterIcon.vue";
    import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";

    export default {
        components: {CustomDropdown, FilterIcon, SearchIcon},
        props: {
            options: {
                type: Object,
                required: false,
            },
            isExact: {
                type: Boolean,
                required: false,
            },
            value: {
                type: String,
                required: false,
            },
        },
        data(){
            return {
                selected: null,
                is_exact: this.isExact,
            }
        },
        methods: {
            toggle(){
                this.$emit('isExact', !this.is_exact )
            },
        },
    }
</script>
