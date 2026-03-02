<script>
/**
 * DynamicFieldLinearScale.vue - Linear scale (customizable range) field component
 */
export default {
    name: "DynamicFieldLinearScale",
    props: {
        modelValue: { type: [Number, String], default: null },
        field: { type: Object, required: true },
        error: { type: String, default: null },
        required: { type: Boolean, default: false },
    },
    emits: ['update:modelValue'],
    computed: {
        inputValue: {
            get() { return this.modelValue; },
            set(val) { this.$emit('update:modelValue', Number(val)); }
        },
        min() {
            return this.field.field_config?.min || 1;
        },
        max() {
            return this.field.field_config?.max || 10;
        },
        minLabel() {
            return this.field.field_config?.minLabel || '';
        },
        maxLabel() {
            return this.field.field_config?.maxLabel || '';
        },
        scaleValues() {
            const values = [];
            for (let i = this.min; i <= this.max; i++) {
                values.push(i);
            }
            return values;
        },
    },
};
</script>

<template>
    <div class="relative">
        <label v-if="field.label" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
            {{ field.label }}<span v-if="required" class="text-red-600 dark:text-red-400">*</span>
        </label>
        <div v-if="field.description" class="text-xs text-gray-600 dark:text-gray-400 mb-2">{{ field.description }}</div>
        
        <div class="p-2 bg-gray-50 dark:bg-gray-800 rounded-md transition-colors" :class="{'border border-red-500 dark:border-red-600': error, 'border border-gray-200 dark:border-gray-700': !error}">
            <div class="flex items-center justify-between mb-1">
                <span v-if="minLabel" class="text-xs text-gray-600 dark:text-gray-400">{{ minLabel }}</span>
                <span v-if="maxLabel" class="text-xs text-gray-600 dark:text-gray-400 ml-auto">{{ maxLabel }}</span>
            </div>
            <div class="flex items-center gap-1 justify-evenly overflow-x-auto py-1">
                <label 
                    v-for="value in scaleValues" 
                    :key="value"
                    class="flex flex-col items-center cursor-pointer group flex-shrink-0"
                >
                    <input
                        type="radio"
                        :name="field.field_key"
                        :value="value"
                        v-model="inputValue"
                        class="sr-only"
                    />
                    <span 
                        class="w-7 h-7 flex items-center justify-center rounded-full border-2 transition-all text-sm font-semibold"
                        :class="{
                            'bg-AB text-white border-AB': inputValue === value,
                            'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 group-hover:border-AB dark:group-hover:border-blue-400 group-hover:bg-blue-50 dark:group-hover:bg-blue-900/20': inputValue !== value
                        }"
                    >
                        {{ value }}
                    </span>
                </label>
            </div>
        </div>
        
        <transition-container type="slide-bottom">
            <InputError v-show="!!error" class="mt-1" :message="error" />
        </transition-container>
    </div>
</template>
