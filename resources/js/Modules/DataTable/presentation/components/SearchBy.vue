<template>
    <div class="space-y-1.5">
        <div class="flex items-center justify-between">
            <label class="text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                Filter By
            </label>
            <label class="inline-flex items-center gap-1.5 cursor-pointer group">
                <input 
                    type="checkbox" 
                    v-model="is_exact" 
                    @change="toggle()" 
                    class="w-3.5 h-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                >
                <span class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                    Exact match
                </span>
            </label>
        </div>
        
        <custom-dropdown 
            :value="value" 
            placeholder="Select column..." 
            :options="options" 
            @selectedChange="$emit('searchBy', $event)"
            class="w-full"
        >
            <template #icon>
                <LuFilter class="w-4 h-4 text-gray-400" />
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
            is_exact: this.isExact,
        }
    },
    methods: {
        toggle(){
            this.$emit('isExact', this.is_exact);
        },
    },
}
</script>