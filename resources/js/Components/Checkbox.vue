<script>
import FieldMixin from "@/Components/Forms/FieldMixin";

export default {
    name: "Checkbox",
    mixins: [FieldMixin],
    inheritAttrs: false,
    props: {
        checked: {
            type: [Boolean, String, Number],
            default: false,
        },
        value: {
            type: [String, Number, Boolean, Object],
            default: null,
        },
        name: {
            type: String,
            default: "",
        },
    },
    emits: ["update:checked", "update:modelValue"],
    methods: {
        normalizeValue(val) {
            if (val === null || val === undefined || val === "") {
                return false;
            }
            if (typeof val === "boolean") {
                return val;
            }
            if (typeof val === "string") {
                const lower = val.toLowerCase().trim();
                return lower === "true" || lower === "1" || lower === "yes";
            }
            if (typeof val === "number") {
                return val !== 0;
            }
            return Boolean(val);
        },
    },
    computed: {
        filteredAttrs() {
            const { class: _, ...rest } = this.$attrs;
            return rest;
        },
        proxyChecked: {
            get() {
                // Support both v-model:checked and v-model
                const hasModelValue = this.modelValue !== undefined && this.modelValue !== null;
                return this.normalizeValue(hasModelValue ? this.modelValue : this.checked);
            },
            set(val) {
                this.$emit("update:checked", val);
                this.$emit("update:modelValue", val);
            },
        },
    },
};
</script>

<template>
    <div
        :class="[
            $attrs.class,
            'w-fit',
            {
                'flex flex-col gap-1': label || error || hint,
                'inline-flex': !label && !error && !hint,
            },
        ]">
        <div class="flex items-center gap-2">
            <input
                :id="id"
                :name="name || id"
                v-model="proxyChecked"
                type="checkbox"
                :value="value"
                :disabled="disabled"
                :class="['cursor-pointer rounded border-slate-300 leading-none text-indigo-600 focus:ring-indigo-600 dark:border-slate-700 dark:bg-slate-900', error ? 'border-red-500 focus:ring-red-500 dark:border-red-500' : '']"
                v-bind="filteredAttrs" />
            <label
                v-if="label"
                :for="id"
                class="cursor-pointer select-none text-sm font-medium text-slate-700 dark:text-slate-300">
                {{ label }}
            </label>
        </div>
        <div
            v-if="error"
            class="text-xs text-red-500">
            {{ error }}
        </div>
        <div
            v-if="hint"
            class="text-xs text-slate-500">
            {{ hint }}
        </div>
    </div>
</template>
