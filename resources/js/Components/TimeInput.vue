<script>
import {
    Clock,
    AlertCircle,
    CheckCircle2,
    HelpCircle,
    X
} from 'lucide-vue-next';

export default {
    name: 'TimeInput',
    components: {
        Clock,
        AlertCircle,
        CheckCircle2,
        HelpCircle,
        X,
    },
    props: {
        modelValue: { type: String, default: '' },
        placeholder: { type: String, default: '' },
        error: { type: String, default: '' },
        label: { type: String, default: '' },
        required: { type: Boolean, default: false },
        chameleon: { type: Boolean, default: false },
        id: { type: String, default: '' },
        disabled: { type: Boolean, default: false },
        clearable: { type: Boolean, default: false },
        hint: { type: String, default: null },
        min: { type: String, default: null },
        max: { type: String, default: null },
        step: { type: [String, Number], default: '60' },
    },
    emits: ['update:modelValue', 'clear'],
    data() {
        return {
            isChameleon: this.chameleon,
            isFocused: false,
        };
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
        inputId() {
            const value = String(this.id || '').trim();
            return value === '' ? `time-${Math.random().toString(36).substr(2, 9)}` : value;
        },
        hasValue() {
            return String(this.modelValue || '').length > 0;
        },
        isValid() {
            return this.hasValue && !this.error && this.isWithinBounds;
        },
        isInvalid() {
            return !!this.error || (this.hasValue && !this.isWithinBounds);
        },
        isWithinBounds() {
            if (!this.hasValue) return true;
            if (!this.min && !this.max) return true;

            const timeValue = this.modelValue;
            if (this.min && timeValue < this.min) return false;
            if (this.max && timeValue > this.max) return false;
            return true;
        },
        displayValue() {
            // Strip seconds for display if present (HH:MM:SS -> HH:MM)
            if (!this.modelValue) return '';
            const parts = this.modelValue.split(':');
            return parts.length >= 2 ? `${parts[0]}:${parts[1]}` : this.modelValue;
        },
    },
    methods: {
        formatTime(value) {
            if (!value) return '';
            const [hours, minutes] = value.split(':');
            // Ensure we always emit HH:MM:SS format
            return `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}:00`;
        },
        onInput(e) {
            const formatted = this.formatTime(e.target.value);
            this.$emit('update:modelValue', formatted);
        },
        onClear() {
            this.$emit('update:modelValue', '');
            this.$emit('clear');
            this.$refs.input?.focus();
        },
        onFocus() {
            this.isFocused = true;
        },
        onBlur() {
            this.isFocused = false;
        },
        focus() {
            this.$refs.input?.focus();
        },
    }
}
</script>

<template>
    <div class="w-full relative" :class="{ 'opacity-60': disabled }">
        <!-- Label Row -->
        <div v-if="label" class="flex items-center justify-between mb-1.5">
            <label
                :for="inputId"
                class="text-xs font-medium text-gray-700 dark:text-gray-200 flex items-center gap-1 cursor-pointer"
            >
                <Clock class="w-3.5 h-3.5 text-gray-400" />
                <span class="flex items-center gap-0.5">
                    {{ label }}
                    <span v-if="required" class="text-red-500" aria-label="required">*</span>
                </span>
                <HelpCircle
                    v-if="hint"
                    :tooltip="hint"
                    class="w-3.5 h-3.5 text-gray-400 hover:text-gray-500 cursor-help ml-1"
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
            :class="{ 'cursor-not-allowed': disabled }"
        >
            <input
                :id="inputId"
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
                    disabled ? 'bg-gray-100 dark:bg-gray-700 cursor-not-allowed' : '',
                    (clearable || isValid || isInvalid) ? 'pr-10' : '',
                ]"
                :value="displayValue"
                :placeholder="placeholder"
                type="time"
                :disabled="disabled"
                :required="required"
                :min="min"
                :max="max"
                :step="step"
                :aria-invalid="isInvalid"
                @input="onInput"
                @focus="onFocus"
                @blur="onBlur"
            />

            <!-- Action Buttons -->
            <div class="absolute right-2 flex items-center gap-1">
                <!-- Clear Button -->
                <button
                    v-if="clearable && hasValue && !disabled"
                    type="button"
                    @click="onClear"
                    class="p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    aria-label="Clear time"
                >
                    <X class="w-4 h-4" />
                </button>

                <!-- Validation Icons -->
                <CheckCircle2
                    v-else-if="isValid"
                    class="w-4 h-4 text-green-500"
                    aria-hidden="true"
                />
                <AlertCircle
                    v-else-if="isInvalid"
                    class="w-4 h-4 text-red-500"
                    aria-hidden="true"
                />
            </div>
        </div>

        <!-- Time Range Hint -->
        <p
            v-if="(min || max) && !error"
            class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1"
        >
            <Clock class="w-3 h-3" />
            <span>
                <template v-if="min && max">Between {{ min }} and {{ max }}</template>
                <template v-else-if="min">From {{ min }} onwards</template>
                <template v-else-if="max">Until {{ max }}</template>
            </span>
        </p>
    </div>
</template>

<style scoped>
/* Custom time input styling */
input[type="time"]::-webkit-calendar-picker-indicator {
    filter: invert(0.4);
    cursor: pointer;
    padding: 0.25rem;
    margin-right: -0.25rem;
    border-radius: 0.25rem;
    transition: all 0.2s ease;
}

input[type="time"]::-webkit-calendar-picker-indicator:hover {
    filter: invert(0.6);
    background-color: rgba(0, 0, 0, 0.05);
}

.dark input[type="time"]::-webkit-calendar-picker-indicator {
    filter: invert(0.6);
}

.dark input[type="time"]::-webkit-calendar-picker-indicator:hover {
    filter: invert(0.8);
    background-color: rgba(255, 255, 255, 0.1);
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
