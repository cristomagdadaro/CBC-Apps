<script>
import {
    AlertCircle,
    CheckCircle2,
    AlignLeft,
    HelpCircle,
    X,
    Maximize2,
    Minimize2
} from 'lucide-vue-next';

export default {
    name: 'TextArea',
    components: {
        AlertCircle,
        CheckCircle2,
        AlignLeft,
        HelpCircle,
        X,
        Maximize2,
        Minimize2,
    },
    props: {
        modelValue: { type: [String, Number], default: '' },
        autocomplete: { type: String, default: '' },
        placeholder: { type: String, default: '' },
        error: { type: String, default: '' },
        type: { type: String, default: '' },
        classes: { type: String, default: '' },
        required: { type: Boolean, default: false },
        id: { type: String, default: '' },
        label: { type: String, default: '' },
        rows: { type: Number, default: 4 },
        guide: { type: String, default: null },
        datalistId: { type: String, default: null },
        datalistOptions: { type: Array, default: null },
        maxLength: { type: Number, default: null },
        clearable: { type: Boolean, default: false },
        expandable: { type: Boolean, default: false },
        hint: { type: String, default: null },
        disabled: { type: Boolean, default: false },
    },
    emits: ['update:modelValue', 'clear'],
    data() {
        return {
            isExpanded: false,
            isFocused: false,
        }
    },
    mounted() {
        if (this.$refs.input && this.$refs.input.hasAttribute('autofocus')) {
            this.$refs.input.focus();
        }
        this.$nextTick(() => this.adjustHeight());
    },
    watch: {
        modelValue() {
            this.$nextTick(() => this.adjustHeight());
        }
    },
    computed: {
        inputId() {
            const value = String(this.id || '').trim();
            return value === '' ? `textarea-${Math.random().toString(36).substr(2, 9)}` : value;
        },
        hasValue() {
            return String(this.modelValue || '').length > 0;
        },
        charCount() {
            return String(this.modelValue || '').length;
        },
        isValid() {
            return this.hasValue && !this.error && (!this.maxLength || this.charCount <= this.maxLength);
        },
        isInvalid() {
            return !!this.error || (this.maxLength && this.charCount > this.maxLength);
        },
        isNearLimit() {
            return this.maxLength && this.charCount > this.maxLength * 0.9 && this.charCount <= this.maxLength;
        },
    },
    methods: {
        adjustHeight() {
            const textarea = this.$refs.input;
            if (!textarea) return;
            const lineHeight = parseFloat(getComputedStyle(textarea).lineHeight) || 24;
            const minHeight = this.rows * lineHeight;
            textarea.style.height = 'auto';
            const newHeight = textarea.scrollHeight;
            textarea.style.height = `${Math.max(newHeight, minHeight)}px`;
        },
        onInput(e) {
            this.$emit('update:modelValue', e.target.value);
            this.adjustHeight();
        },
        focus() {
            this.$refs.input?.focus();
        },
        onClear() {
            this.$emit('update:modelValue', '');
            this.$emit('clear');
            this.focus();
        },
        toggleExpand() {
            this.isExpanded = !this.isExpanded;
            this.$nextTick(() => this.adjustHeight());
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
    <div
        class="w-full relative"
        :class="[
            classes,
            isExpanded ? 'fixed inset-4 z-50 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-2xl flex flex-col' : ''
        ]"
    >
        <!-- Expanded Overlay Backdrop -->
        <div v-if="isExpanded" class="fixed inset-0 bg-black/50 -z-10" @click="toggleExpand"></div>

        <!-- Label Row -->
        <div v-if="label" class="flex items-center justify-between mb-1.5" :class="{ 'mb-3': isExpanded }">
            <label
                :for="inputId"
                class="text-xs font-medium text-gray-700 dark:text-gray-200 flex items-center gap-1 cursor-pointer"
            >
                <AlignLeft class="w-3.5 h-3.5 text-gray-400" />
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

            <div class="flex items-center gap-2">
                <!-- Character Count -->
                <span
                    v-if="maxLength"
                    class="text-xs"
                    :class="{
                        'text-gray-400': !isNearLimit && !isInvalid,
                        'text-amber-500': isNearLimit,
                        'text-red-500': isInvalid,
                    }"
                >
                    {{ charCount }}/{{ maxLength }}
                </span>

                <!-- Expand Toggle -->
                <button
                    v-if="expandable"
                    type="button"
                    @click="toggleExpand"
                    class="p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    :aria-label="isExpanded ? 'Collapse' : 'Expand'"
                >
                    <Minimize2 v-if="isExpanded" class="w-4 h-4" />
                    <Maximize2 v-else class="w-4 h-4" />
                </button>

                <transition name="fade">
                    <div v-if="error" class="flex items-center gap-1 text-xs text-red-600 dark:text-red-400">
                        <AlertCircle class="w-3.5 h-3.5" />
                        <span class="max-w-[200px] truncate">{{ error }}</span>
                    </div>
                </transition>
            </div>
        </div>

        <!-- Textarea Wrapper -->
        <div
            class="relative flex items-start group"
            :class="{
                'opacity-60 cursor-not-allowed': disabled,
                'flex-1': isExpanded
            }"
        >
            <textarea
                :id="inputId"
                ref="input"
                :rows="rows"
                :class="[
                    'w-full bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100',
                    'placeholder:text-gray-400 dark:placeholder:text-gray-500',
                    'rounded-lg shadow-sm px-3 py-2.5 text-sm leading-relaxed',
                    'transition-all duration-200 ease-in-out',
                    'border resize-none overflow-hidden',
                    isInvalid
                        ? 'border-red-300 dark:border-red-700 focus:border-red-500 focus:ring-2 focus:ring-red-200 dark:focus:ring-red-900'
                        : isValid
                            ? 'border-green-300 dark:border-green-700 focus:border-green-500 focus:ring-2 focus:ring-green-200 dark:focus:ring-green-900'
                            : 'border-gray-300 dark:border-gray-600 focus:border-AA focus:ring-2 focus:ring-AA/20',
                    disabled ? 'bg-gray-100 dark:bg-gray-700 cursor-not-allowed' : '',
                    isExpanded ? 'flex-1 h-full' : '',
                    (clearable || isValid || isInvalid) && !isExpanded ? 'pr-10' : '',
                ]"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :list="datalistId"
                :maxlength="maxLength"
                :required="required"
                :aria-invalid="isInvalid"
                :aria-describedby="guide ? `${inputId}-guide` : undefined"
                @input="onInput"
                @focus="onFocus"
                @blur="onBlur"
            />

            <!-- Action Buttons (Inline, only when not expanded) -->
            <div v-if="!isExpanded" class="absolute right-2 top-2 flex items-center gap-1">
                <!-- Clear Button -->
                <button
                    v-if="clearable && hasValue && !disabled"
                    type="button"
                    @click="onClear"
                    class="p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    aria-label="Clear textarea"
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

            <!-- Expanded Actions -->
            <div v-if="isExpanded" class="flex justify-end gap-2 mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                <button
                    type="button"
                    @click="toggleExpand"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                >
                    Done
                </button>
            </div>

            <!-- Datalist -->
            <datalist v-if="datalistId && datalistOptions?.length" :id="datalistId">
                <option v-for="opt in datalistOptions" :key="opt" :value="opt" />
            </datalist>
        </div>

        <!-- Guide Text -->
        <p
            v-if="guide && !isExpanded"
            :id="`${inputId}-guide`"
            class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 flex items-start gap-1"
        >
            <HelpCircle class="w-3 h-3 mt-0.5 flex-shrink-0" />
            <span>{{ guide }}</span>
        </p>
    </div>
</template>

<style scoped>
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
textarea:focus {
    outline: none;
}

/* Custom autofill styling */
textarea:-webkit-autofill,
textarea:-webkit-autofill:hover,
textarea:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0px 1000px white inset;
    -webkit-text-fill-color: inherit;
}

.dark textarea:-webkit-autofill,
.dark textarea:-webkit-autofill:hover,
.dark textarea:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0px 1000px rgb(31, 41, 55) inset;
    -webkit-text-fill-color: rgb(243, 244, 246);
}

/* Smooth height transition */
textarea {
    transition: height 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}
</style>
