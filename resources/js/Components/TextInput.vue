<script>
import FieldMixin from "@/Components/Forms/FieldMixin";

export default {
    name: "TextInput",
    mixins: [FieldMixin],
    props: {
        autocomplete: { type: String, default: "" },
        name: { type: String, default: "" },
        type: { type: String, default: "text" },
        typeInput: { type: String, default: "" },
        chameleon: { type: Boolean, default: false },
    },
    data() {
        return {
            isChameleon: this.chameleon,
            showPassword: false,
        };
    },
    watch: {
        chameleon(newVal) {
            this.isChameleon = newVal;
        },
    },
    computed: {
        inputAutocomplete() {
            const value = String(this.autocomplete || "").trim();
            return value === "" ? null : value;
        },
        inputName() {
            const value = String(this.name || this.id || "").trim();
            return value === "" ? null : value;
        },
        isPassword() {
            return (this.typeInput || this.type) === "password";
        },
        inputType() {
            if (this.isPassword) {
                return this.showPassword ? "text" : "password";
            }
            return this.typeInput || this.type;
        },
    },
    methods: {
        togglePassword() {
            this.showPassword = !this.showPassword;
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
        :guide="guide"
        :error="error"
        :clearable="clearable"
        :has-value="hasValue"
        :disabled="disabled"
        :datalist-id="datalistId"
        :datalist-options="datalistOptions"
        :classes="classes"
        :show-valid-indicator="!isPassword"
        @clear="onClear">
        <template #default="{ inputId, isInvalid, isValid, guideId }">
            <input
                :id="inputId"
                :name="inputName"
                ref="input"
                :class="['w-full rounded-xl px-4 py-2.5 text-sm transition-all duration-200 ease-out border', 'placeholder:text-slate-400 dark:placeholder:text-slate-500', isInvalid ? 'border-rose-300 dark:border-rose-700 bg-rose-50/50 dark:bg-rose-900/10 text-rose-900 dark:text-rose-100 focus:border-rose-500 focus:ring-1 focus:ring-rose-500' : isValid ? 'border-emerald-300 dark:border-emerald-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500', disabled ? 'bg-slate-100 dark:bg-slate-800 cursor-not-allowed text-slate-500 dark:text-slate-400' : '', isPassword && ((clearable && hasValue && !disabled) || isInvalid) ? 'pr-20' : isPassword || (clearable && hasValue && !disabled) || (!isPassword && isValid) || isInvalid ? 'pr-10' : '']"
                :autocomplete="inputAutocomplete"
                :value="modelValue"
                :placeholder="placeholder"
                :type="inputType"
                :disabled="disabled"
                :list="datalistId"
                :required="required"
                :aria-invalid="isInvalid"
                :aria-describedby="guideId"
                @input="onInput"
                @focus="onFocus"
                @blur="onBlur" />
        </template>

        <template #input-actions>
            <button
                v-if="isPassword && !disabled"
                type="button"
                @click="togglePassword"
                class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 bg-white/80 dark:bg-slate-800/80 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors backdrop-blur-sm"
                :aria-label="showPassword ? 'Hide password' : 'Show password'">
                <LuEyeOff
                    v-if="showPassword"
                    class="w-4 h-4" />
                <LuEye
                    v-else
                    class="w-4 h-4" />
            </button>
        </template>
    </Field>
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

/* Focus ring animation */
input:focus {
    outline: none;
}

/* Custom autofill styling tailored for slate theme */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0px 1000px #f8fafc inset;
    -webkit-text-fill-color: #0f172a;
}

.dark input:-webkit-autofill,
.dark input:-webkit-autofill:hover,
.dark input:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0px 1000px #0f172a inset;
    -webkit-text-fill-color: #f1f5f9;
}
</style>
