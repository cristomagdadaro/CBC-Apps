<script>
import FieldMixin from '@/Components/Forms/FieldMixin';

export default {
    name: 'DateInput',
    mixins: [FieldMixin],
    props: {
        chameleon: { type: Boolean, default: false },
    },
    data() {
        return {
            isChameleon: this.chameleon,
        }
    },
    watch: {
        chameleon(newVal) {
            this.isChameleon = newVal;
        }
    },
    methods: {
        onInput(e) {
            this.$emit('update:modelValue', e.target.value);
        }
    }
}
</script>

<template>
    <Field
        :id="id"
        :label="label"
        :error="error"
        :required="required"
        :hint="hint"
        :guide="guide"
        :clearable="clearable"
        :has-value="hasValue"
        :disabled="disabled"
        @clear="onClear"
    >
        <template #label-icon>
            <LuCalendar class="w-3.5 h-3.5 text-indigo-500 dark:text-indigo-400" />
        </template>
        
        <template #default="{ inputId, isInvalid, isValid, guideId }">
            <input
                :id="inputId"
                ref="input"
                type="date"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :aria-invalid="isInvalid"
                :aria-describedby="guideId"
                @input="onInput"
                @focus="onFocus"
                @blur="onBlur"
                :class="[
                    'w-full rounded-xl px-4 py-2.5 text-sm transition-all duration-200 ease-out border [color-scheme:light] dark:[color-scheme:dark]',
                    'placeholder:text-slate-400 dark:placeholder:text-slate-500',
                    isInvalid
                        ? 'border-rose-300 dark:border-rose-700 bg-rose-50/50 dark:bg-rose-900/10 text-rose-900 dark:text-rose-100 focus:border-rose-500 focus:ring-1 focus:ring-rose-500'
                        : isValid
                            ? 'border-emerald-300 dark:border-emerald-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500'
                            : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500',
                    disabled ? 'bg-slate-100 dark:bg-slate-800 cursor-not-allowed text-slate-500 dark:text-slate-400' : '',
                    (clearable && hasValue && !disabled) || isValid || isInvalid ? 'pr-10' : ''
                ]"
            />
        </template>
    </Field>
</template>

<style scoped>
/* Custom date input styling */
input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(0.4);
    cursor: pointer;
    padding: 0.25rem;
    margin-right: -0.25rem;
    border-radius: 0.25rem;
    transition: all 0.2s ease;
}

input[type="date"]::-webkit-calendar-picker-indicator:hover {
    filter: invert(0.6);
    background-color: rgba(0, 0, 0, 0.05);
}

.dark input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(0.6);
}

.dark input[type="date"]::-webkit-calendar-picker-indicator:hover {
    filter: invert(0.8);
    background-color: rgba(255, 255, 255, 0.1);
}

/* Focus ring animation */
input:focus {
    outline: none;
}
</style>
