<script>
/**
 * DynamicFieldCheckboxGroup.vue - Multiple checkbox selection field component
 */
export default {
    name: "DynamicFieldCheckboxGroup",
    props: {
        modelValue: { type: Array, default: () => [] },
        field: { type: Object, required: true },
        error: { type: String, default: null },
        required: { type: Boolean, default: false },
    },
    emits: ["update:modelValue"],
    computed: {
        inputValue: {
            get() {
                return this.modelValue || [];
            },
            set(val) {
                this.$emit("update:modelValue", val);
            },
        },
        options() {
            return this.field.options || [];
        },
        layout() {
            return this.field.field_config?.layout || "vertical";
        },
        layoutClass() {
            return this.layout === "horizontal" ? "flex flex-row flex-wrap gap-4" : "flex flex-col gap-2";
        },
    },
    methods: {
        toggleOption(value) {
            const current = [...this.inputValue];
            const index = current.indexOf(value);
            if (index === -1) {
                current.push(value);
            } else {
                current.splice(index, 1);
            }
            this.inputValue = current;
        },
        isChecked(value) {
            return this.inputValue.includes(value);
        },
    },
};
</script>

<template>
    <div class="relative">
        <label
            v-if="field.label"
            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
            {{ field.label }}
            <span
                v-if="required"
                class="text-red-600 dark:text-red-400">
                *
            </span>
        </label>
        <div :class="layoutClass">
            <label
                v-for="option in options"
                :key="option.value"
                class="flex cursor-pointer items-center gap-2 rounded p-1 transition-colors hover:bg-gray-100 dark:hover:bg-gray-700">
                <input
                    type="checkbox"
                    :value="option.value"
                    :required="required"
                    :checked="isChecked(option.value)"
                    @change="toggleOption(option.value)"
                    class="h-4 w-4 rounded border-gray-800 text-AB focus:ring-AB dark:border-gray-600 dark:bg-gray-100" />
                <span class="text-sm text-gray-700 dark:text-gray-200">{{ option.label }}</span>
            </label>
        </div>
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
