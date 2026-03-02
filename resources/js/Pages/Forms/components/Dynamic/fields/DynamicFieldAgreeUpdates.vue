<script>
/**
 * DynamicFieldAgreement.vue - Agreement checkbox (terms & conditions) field component
 */
export default {
    name: "DynamicFieldAgreeUpdates",
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
        agreementText() {
            return this.field.label || this.field.field_config?.agreementText || 'I agree to receive updates and communications from the DA–Crop Biotechnology Center';
        },
    },
};
</script>

<template>
    <div 
        class="w-full relative p-3 flex leading-tight items-start gap-3 bg-white dark:bg-gray-800 rounded-md border cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
        :class="{'border-red-500 dark:border-red-600': error, 'border-gray-600 dark:border-gray-700': !error}"
        @click.prevent="inputValue = !inputValue"
    >
        <Checkbox 
            :id="field.field_key" 
            v-model="inputValue" 
            :checked="inputValue"
            class="mt-0.5"
        />
        <div class="flex flex-col gap-1">
            <label :for="field.field_key" class="text-sm cursor-pointer leading-tight text-gray-700 dark:text-gray-200">
                {{ agreementText }}<span v-if="required" class="text-red-600 dark:text-red-400">*</span>
            </label>
            <div v-if="field.description" class="text-xs text-gray-600 dark:text-gray-400">{{ field.description }}</div>
        </div>
        <transition-container type="slide-bottom">
            <InputError v-show="!!error" class="absolute -bottom-5 left-3" :message="error" />
        </transition-container>
    </div>
</template>
