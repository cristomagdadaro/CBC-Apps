<script>
/**
 * DynamicFieldLikertScale.vue - Likert scale (1-5 rating) field component
 */
export default {
    name: "DynamicFieldLikertScale",
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
            return this.field.field_config?.max || 5;
        },
        labels() {
            return this.field.field_config?.labels || { 1: 'Poor', 5: 'Excellent' };
        },
        scaleValues() {
            const values = [];
            for (let i = this.min; i <= this.max; i++) {
                values.push(i);
            }
            return values;
        },
    },
    methods: {
        getLabel(value) {
            return this.labels[value] || '';
        },
    },
};
</script>

<template>
    <div class="relative">
        <label v-if="field.label" class="block text-sm font-medium text-gray-700 mb-2">
            {{ field.label }}<span v-if="required" class="text-red-600">*</span>
        </label>
        <div v-if="field.description" class="text-xs text-gray-500 mb-2">{{ field.description }}</div>
        
        <div class="flex items-center justify-between gap-1 p-2 bg-gray-50 rounded-md" :class="{'border border-red-500': error}">
            <span class="text-xs text-gray-600 w-16 text-left">{{ getLabel(min) }}</span>
            <div class="flex items-center gap-1 flex-1 justify-center">
                <label 
                    v-for="value in scaleValues" 
                    :key="value"
                    class="flex flex-col items-center cursor-pointer group"
                >
                    <input
                        type="radio"
                        :name="field.field_key"
                        :value="value"
                        v-model="inputValue"
                        class="sr-only"
                    />
                    <span 
                        class="w-8 h-8 flex items-center justify-center rounded-full border-2 transition-all"
                        :class="{
                            'bg-AB text-white border-AB': inputValue === value,
                            'bg-white border-gray-300 group-hover:border-AB': inputValue !== value
                        }"
                    >
                        {{ value }}
                    </span>
                </label>
            </div>
            <span class="text-xs text-gray-600 w-16 text-right">{{ getLabel(max) }}</span>
        </div>
        
        <transition-container type="slide-bottom">
            <InputError v-show="!!error" class="mt-1" :message="error" />
        </transition-container>
    </div>
</template>
