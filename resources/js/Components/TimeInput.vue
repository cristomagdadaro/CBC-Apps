<script>
import FieldMixin from "@/Components/Forms/FieldMixin";

export default {
    name: "TimeInput",
    mixins: [FieldMixin],
    props: {
        chameleon: { type: Boolean, default: false },
        min: { type: String, default: null },
        max: { type: String, default: null },
        step: { type: [String, Number], default: "60" },
    },
    data() {
        return {
            isChameleon: this.chameleon,
        };
    },
    watch: {
        chameleon(newVal) {
            this.isChameleon = newVal;
        },
    },
    computed: {
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
            if (!this.modelValue) return "";
            const parts = this.modelValue.split(":");
            return parts.length >= 2 ? `${parts[0]}:${parts[1]}` : this.modelValue;
        },
        computedError() {
            if (this.error) return this.error;
            if (this.hasValue && !this.isWithinBounds) return "Time is out of range";
            return null;
        },
        rangeHint() {
            if (this.min && this.max) return `Between ${this.min} and ${this.max}`;
            if (this.min) return `From ${this.min} onwards`;
            if (this.max) return `Until ${this.max}`;
            return null;
        },
    },
    methods: {
        formatTime(value) {
            if (!value) return "";
            const [hours, minutes] = value.split(":");
            // Ensure we always emit HH:MM:SS format
            return `${hours.padStart(2, "0")}:${minutes.padStart(2, "0")}:00`;
        },
        onInput(e) {
            const formatted = this.formatTime(e.target.value);
            this.$emit("update:modelValue", formatted);
        },
    },
};
</script>

<template>
    <Field
        :id="id"
        :label="label"
        :required="required"
        :hint="hint"
        :guide="rangeHint"
        :error="computedError"
        :clearable="clearable"
        :has-value="hasValue"
        :disabled="disabled"
        @clear="onClear">
        <template #label-icon>
            <LuClock class="w-3.5 h-3.5 text-indigo-500 dark:text-indigo-400" />
        </template>

        <template #default="{ inputId, isInvalid, isValid, guideId }">
            <input
                :id="inputId"
                ref="input"
                :class="['w-full rounded-xl px-4 py-2.5 text-sm transition-all duration-200 ease-out border', 'placeholder:text-slate-400 dark:placeholder:text-slate-500', isInvalid ? 'border-rose-300 dark:border-rose-700 bg-rose-50/50 dark:bg-rose-900/10 text-rose-900 dark:text-rose-100 focus:border-rose-500 focus:ring-1 focus:ring-rose-500' : isValid ? 'border-emerald-300 dark:border-emerald-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500', disabled ? 'bg-slate-100 dark:bg-slate-800 cursor-not-allowed text-slate-500 dark:text-slate-400' : '', clearable || isValid || isInvalid ? 'pr-10' : '']"
                :value="displayValue"
                :placeholder="placeholder"
                type="time"
                :disabled="disabled"
                :required="required"
                :min="min"
                :max="max"
                :step="step"
                :aria-invalid="isInvalid"
                :aria-describedby="guideId"
                @input="onInput"
                @focus="onFocus"
                @blur="onBlur" />
        </template>
    </Field>
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
