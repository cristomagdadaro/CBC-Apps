<script>
/**
 * DynamicFieldDate.vue - Date/Time input field component
 */
export default {
    name: "DynamicFieldDate",
    props: {
        modelValue: { type: String, default: "" },
        field: { type: Object, required: true },
        error: { type: String, default: null },
        required: { type: Boolean, default: false },
    },
    emits: ["update:modelValue"],
    computed: {
        inputValue: {
            get() {
                return this.modelValue;
            },
            set(val) {
                this.$emit("update:modelValue", val);
            },
        },
        placeholder() {
            const p = this.field.placeholder || this.field.label || "";
            return this.required ? `${p}*` : p;
        },
        inputType() {
            if (this.field.field_type === "datetime") return "datetime-local";
            if (this.field.field_type === "time") return "time";
            return "date";
        },
    },
};
</script>

<template>
    <div class="relative">
        <label
            v-if="field.label"
            class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
            {{ field.label }}
            <span
                v-if="required"
                class="text-red-600 dark:text-red-400">
                *
            </span>
        </label>
        <input
            :id="field.field_key"
            v-model="inputValue"
            :type="inputType"
            :required="required"
            :placeholder="placeholder"
            class="w-full rounded-md border border-gray-600 bg-white px-3 py-2 text-gray-900 placeholder-gray-500 transition-colors focus:border-transparent focus:outline-none focus:ring-2 focus:ring-AB dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400 dark:focus:border-gray-600"
            :class="{ 'border-red-500 dark:border-red-600': error }" />
        <div
            v-if="field.description"
            class="mt-1 text-xs text-gray-600 dark:text-gray-400">
            {{ field.description }}
        </div>
        <transition-container type="slide-bottom">
            <InputError
                v-show="!!error"
                class="mt-1"
                :message="error" />
        </transition-container>
    </div>
</template>
