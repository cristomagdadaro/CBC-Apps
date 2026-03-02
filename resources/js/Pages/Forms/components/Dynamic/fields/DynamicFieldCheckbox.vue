<script>
/**
 * DynamicFieldCheckbox.vue - Single checkbox field component
 */
export default {
    name: "DynamicFieldCheckbox",
    props: {
        modelValue: { type: Boolean, default: false },
        field: { type: Object, required: true },
        error: { type: String, default: null },
        required: { type: Boolean, default: false },
    },
    emits: ['update:modelValue'],
    computed: {
        inputValue: {
            get() { return this.modelValue; },
            set(val) { this.$emit('update:modelValue', val); }
        },
    },
};
</script>

<template>
    <div 
        class="w-full relative p-2 flex text-center leading-none items-center gap-2 bg-white dark:bg-gray-800 rounded-md cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors border"
        :class="{'border-red-500 dark:border-red-600': error, 'border-gray-600 dark:border-gray-700': !error}"
        @click.prevent="inputValue = !inputValue"
    >
        <Checkbox 
            :id="field.field_key" 
            v-model="inputValue" 
            :required="required"
            :checked="inputValue"
        />
        <label :for="field.field_key" class="text-sm cursor-pointer text-gray-700 dark:text-gray-200">
            {{ field.label }}<span v-if="required" class="text-red-600 dark:text-red-400">*</span>
        </label>
        <transition-container type="slide-bottom">
            <InputError v-show="!!error" class="absolute -top-1 left-3" :message="error" />
        </transition-container>
    </div>
</template>
