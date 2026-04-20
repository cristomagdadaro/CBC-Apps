<script>
import {
    AlertCircle,
    CheckCircle2,
    XCircle,
    Eye,
    EyeOff,
    HelpCircle,
    X
} from 'lucide-vue-next';

export default {
    name: 'TextInput',
    components: {
        AlertCircle,
        CheckCircle2,
        XCircle,
        Eye,
        EyeOff,
        HelpCircle,
        X,
    },
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
        clearable: { type: Boolean, default: false },
        hint: { type: String, default: null },
    },
    emits: ['update:modelValue', 'clear'],
    data() {
        return {
            isChameleon: this.chameleon,
            showPassword: false,
            isFocused: false,
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
            return value === '' ? `input-${Math.random().toString(36).substr(2, 9)}` : value;
        },
        inputName() {
            const value = String(this.name || this.id || '').trim();
            return value === '' ? null : value;
        },
        isPassword() {
            return (this.typeInput || this.type) === 'password';
        },
        inputType() {
            if (this.isPassword) {
                return this.showPassword ? 'text' : 'password';
            }
            return this.typeInput || this.type;
        },
        hasValue() {
            return String(this.modelValue || '').length > 0;
        },
        isValid() {
            return this.hasValue && !this.error;
        },
        isInvalid() {
            return !!this.error;
        },
    },
    methods: {
        focus() {
            this.$refs.input?.focus();
        },
        onInput(e) {
            this.$emit('update:modelValue', e.target.value);
        },
        onClear() {
            this.$emit('update:modelValue', '');
            this.$emit('clear');
            this.focus();
        },
        togglePassword() {
            this.showPassword = !this.showPassword;
        },
        onFocus() {
            this.isFocused = true;
        },
        onBlur() {
            this.isFocused = false;
        },
    }
}
</script>

<template>
    <div class="w-full relative" :class="classes">
        <!-- Label Row -->
        <div v-if="label" class="flex items-center justify-between mb-1.5">
            <label
                :for="inputId"
                class="text-xs font-medium text-gray-700 dark:text-gray-200 flex items-center gap-1 cursor-pointer"
            >
                <span class="flex items-center gap-0.5">
                    {{ label }}
                    <span v-if="required" class="text-red-500" aria-label="required">*</span>
                </span>
                <HelpCircle
                    v-if="hint"
                    :tooltip="hint"
                    class="w-3.5 h-3.5 text-gray-400 hover:text-gray-500 cursor-help"
                />
            </label>

            <transition name="fade">
                <div v-if="error" class="flex items-center gap-1 text-xs text-red-600 dark:text-red-400">
                    <AlertCircle class="w-3.5 h-3.5" />
                    <span>{{ error }}</span>
                </div>
            </transition>
        </div>

        <!-- Input Wrapper -->
        <div
            class="relative flex items-center group"
            :class="{ 'opacity-60 cursor-not-allowed': disabled }"
        >
            <input
                :id="inputId"
                :name="inputName"
                ref="input"
                :class="[
                    'w-full bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100',
                    'placeholder:text-gray-400 dark:placeholder:text-gray-500',
                    'rounded-lg shadow-sm px-3 py-2.5 text-sm',
                    'transition-all duration-200 ease-in-out',
                    'border',
                    isInvalid
                        ? 'border-red-300 dark:border-red-700 focus:border-red-500 focus:ring-2 focus:ring-red-200 dark:focus:ring-red-900'
                        : isValid
                            ? 'border-green-300 dark:border-green-700 focus:border-green-500 focus:ring-2 focus:ring-green-200 dark:focus:ring-green-900'
                            : 'border-gray-300 dark:border-gray-600 focus:border-AA focus:ring-2 focus:ring-AA/20',
                    (clearable || isPassword) && hasValue ? 'pr-20' : isPassword || clearable ? 'pr-10' : '',
                    disabled ? 'bg-gray-100 dark:bg-gray-700 cursor-not-allowed' : '',
                ]"
                :autocomplete="inputAutocomplete"
                :value="modelValue"
                :placeholder="placeholder"
                :type="inputType"
                :disabled="disabled"
                :list="datalistId"
                :required="required"
                :aria-invalid="isInvalid"
                :aria-describedby="guide ? `${inputId}-guide` : undefined"
                @input="onInput"
                @focus="onFocus"
                @blur="onBlur"
            >

            <!-- Action Buttons -->
            <div class="absolute right-2 flex items-center gap-1">
                <!-- Clear Button -->
                <button
                    v-if="clearable && hasValue && !disabled"
                    type="button"
                    @click="onClear"
                    class="p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    aria-label="Clear input"
                >
                    <X class="w-4 h-4" />
                </button>

                <!-- Password Toggle -->
                <button
                    v-if="isPassword && !disabled"
                    type="button"
                    @click="togglePassword"
                    class="p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                >
                    <EyeOff v-if="showPassword" class="w-4 h-4" />
                    <Eye v-else class="w-4 h-4" />
                </button>

                <!-- Validation Icons -->
                <CheckCircle2
                    v-if="isValid && !isPassword && !clearable"
                    class="w-4 h-4 text-green-500"
                    aria-hidden="true"
                />
                <XCircle
                    v-if="isInvalid"
                    class="w-4 h-4 text-red-500"
                    aria-hidden="true"
                />
            </div>

            <!-- Datalist -->
            <datalist v-if="datalistId && datalistOptions?.length" :id="datalistId">
                <option v-for="opt in datalistOptions" :key="opt" :value="opt" />
            </datalist>
        </div>

        <!-- Guide Text -->
        <p
            v-if="guide"
            :id="`${inputId}-guide`"
            class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 flex items-start gap-1"
        >
            <HelpCircle class="w-3 h-3 mt-0.5 flex-shrink-0" />
            <span>{{ guide }}</span>
        </p>
    </div>
</template>

<style scoped>
/* Hide number spinners */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Focus ring animation */
input:focus {
    outline: none;
}

/* Custom autofill styling */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0px 1000px white inset;
    -webkit-text-fill-color: inherit;
}

.dark input:-webkit-autofill,
.dark input:-webkit-autofill:hover,
.dark input:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0px 1000px rgb(31, 41, 55) inset;
    -webkit-text-fill-color: rgb(243, 244, 246);
}
</style>
