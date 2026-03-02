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
        class="w-full relative p-2 flex text-center leading-none items-center gap-2 bg-white rounded-md cursor-pointer hover:bg-gray-50"
        :class="{'border-red-500': error, 'border-gray-600': !error}"
        @click.prevent="inputValue = !inputValue"
    >
        <Checkbox 
            :id="field.field_key" 
            v-model="inputValue" 
            :required="required"
            :checked="inputValue"
        />
        <label :for="field.field_key" class="text-sm cursor-pointer">
            {{ field.label }}<span v-if="required" class="text-red-600">*</span>
        </label>
        <transition-container type="slide-bottom">
            <InputError v-show="!!error" class="absolute -top-1 left-3" :message="error" />
        </transition-container>
    </div>
</template>
