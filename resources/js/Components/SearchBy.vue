<template>
    <div class="flex flex-col gap-0.5">
        <div class="text-xs text-gray-500 flex items-center justify-between">
            <span class="flex gap-0.5 whitespace-nowrap">Search by</span>
            <div class="flex items-center gap-0.5">
                <label for="exactValue"></label>
                <input type="checkbox" v-model="is_exact" @click="toggle()" class="rounded-full h-3 w-4 focus:outline-none focus:ring-0 focus:border-1" title="turn on filter by exact value">
            </div>
        </div>
        <custom-dropdown :show-clear="false" :value="value" placeholder="Columns" :options="options" @selectedChange="$emit('searchBy', $event)">
            <template #icon>
                <filter-icon class="h-4 w-4" />
            </template>
        </custom-dropdown>
    </div>
</template>
<script>

export default {
    name: "SearchBy",
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
            type: String|Number,
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
