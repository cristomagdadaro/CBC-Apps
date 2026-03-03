<template>
    <div class="flex flex-col gap-0.5">
        <div class="text-xs text-gray-600 flex items-center justify-between">
            <span class="flex gap-0.5 whitespace-nowrap">Filter by Column</span>
            <div class="flex items-center gap-0.5">
                <label for="exactValue"></label>
                <input type="checkbox" v-model="is_exact" @click="toggle()" class="rounded-full h-3 w-3 focus:outline-none focus:ring-0 focus:border-1" title="turn on filter by exact value">
            </div>
        </div>
        <custom-dropdown :value="value" placeholder="Filters" :options="options" @selectedChange="$emit('searchBy', $event)">
            <template #icon>
                <filter-icon class="h-4 w-4" />
            </template>
        </custom-dropdown>
    </div>
</template>
<script>
export default {
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
    data(){
        return {
            selected: null,
            is_exact: this.isExact,
        }
    },
    methods: {
        toggle(){
            this.$emit('isExact', !this.is_exact? true : null)
        },
    },
}
</script>
