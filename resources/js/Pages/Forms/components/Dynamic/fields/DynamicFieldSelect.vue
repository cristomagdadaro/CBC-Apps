<script>
/**
 * DynamicFieldSelect.vue - Dropdown select field component
 */
export default {
    name: "DynamicFieldSelect",
    props: {
        modelValue: { type: [String, Number], default: null },
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
            return this.field.placeholder || this.field.field_config?.placeholder || "Choose an option";
        },
        options() {
            return this.field.options || [];
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
        <select
            :id="field.field_key"
            v-model="inputValue"
            :required="required"
            class="w-full rounded-md border border-gray-600 bg-white px-3 py-2 text-gray-900 transition-colors focus:border-transparent focus:outline-none focus:ring-2 focus:ring-AB dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-gray-600"
            :class="{ 'border-red-500 dark:border-red-600': error }">
            <option
                value=""
                disabled
                class="bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-100">
                {{ placeholder }}
            </option>
            <option
                v-for="option in options"
                :key="option.value"
                :value="option.value"
                class="bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-100">
                {{ option.label }}
            </option>
        </select>
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
