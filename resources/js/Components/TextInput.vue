<script>
export default {
    name: 'TextInput',
    props: {
        modelValue: { type: [String, Number], default: '' },
        autocomplete: { type: String, default: '' },
        name: { type: String, default: '' },
        placeholder: { type: String, default: '' },
        error: { type: String, default: '' },
        type: { type: String, default: 'text' },
        classes: { type: String, default: '' },
        id: { type: String, default: '' },
        label: { type: String, default: '' },
        required: { type: Boolean, default: false },
        typeInput: { type: String, default: '' },
        disabled: { type: Boolean, default: false },
        chameleon: { type: Boolean, default: false },
        guide: { type: String, default: null },
        datalistId: { type: String, default: null },
        datalistOptions: { type: Array, default: null },
    },
    emits: ['update:modelValue'],
    data() {
        return {
            isChameleon: this.chameleon,
        }
    },
    mounted() {
        const input = this.$refs.input;
        if (input && input.hasAttribute && input.hasAttribute('autofocus')) {
            input.focus();
        }
    },
    watch: {
        chameleon(newVal) {
            this.isChameleon = newVal;
        }
    },
    computed: {
        inputAutocomplete() {
            const value = String(this.autocomplete || '').trim();
            return value === '' ? null : value;
        },
        inputId() {
            const value = String(this.id || '').trim();
            return value === '' ? null : value;
        },
        inputName() {
            const value = String(this.name || this.id || '').trim();
            return value === '' ? null : value;
        },
    },
    methods: {
        focus() {
            this.$refs.input?.focus();
        },
        onInput(e) {
            this.$emit('update:modelValue', e.target.value);
        }
    }
}
</script>

<template>
    <div class="w-full relative" :class="classes">
        <div v-if="label" class="text-xs text-gray-700 dark:text-gray-200 flex items-center justify-between">
            <span class="flex gap-0.5 whitespace-nowrap">{{ label }} <b v-if="required" class="text-red-500 ">*</b></span>
            <transition-container type="slide-bottom">
                <InputError v-show="!!error" class="" :message="error" />
            </transition-container>
        </div>
        <div class="flex items-center">
            <input
                :id="inputId"
                :name="inputName"
                ref="input"
                :class="{'border-red-500': error}"
                :autocomplete="inputAutocomplete"
                class="w-full placeholder:text-gray-300 focus:border-AB focus:ring-AB rounded-md shadow-sm px-3 py-2"
                :value="modelValue"
                :placeholder="placeholder"
                :type="typeInput || type"
                :disabled="disabled"
                :list="datalistId"
                @input="$emit('update:modelValue', $event.target.value)"
            >
            <slot />
            <datalist v-if="datalistId && datalistOptions && datalistOptions.length" :id="datalistId">
                <option v-for="opt in datalistOptions" :key="opt" :value="opt" />
            </datalist>
        </div>
        <p v-if="guide" class="mt-1 text-xs text-gray-500">{{ guide }}</p>
    </div>
</template>
<style scoped>
/* Hide the number input spinners */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield; /* Hide in Firefox */
}
</style>
