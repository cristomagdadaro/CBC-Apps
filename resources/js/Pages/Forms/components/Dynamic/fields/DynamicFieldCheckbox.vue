<script>
/**
 * DynamicFieldCheckbox.vue - Single checkbox field component
 */
export default {
    name: "DynamicFieldCheckbox",
    props: {
        modelValue: { type: Boolean, default: false },
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
    },
};
</script>

<template>
    <div
        class="relative flex w-full flex-col gap-1 rounded-md bg-white px-3 py-2 leading-none transition-colors hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700"
        :class="{
            'border-red-500 dark:border-red-600': error,
            'border-gray-600 dark:border-gray-700': !error,
        }"
        @click.prevent="inputValue = !inputValue">
        <div class="flex items-center gap-2">
            <Checkbox
                :id="field.field_key"
                v-model="inputValue"
                :required="required"
                :checked="inputValue" />
            <label
                :for="field.field_key"
                class="cursor-pointer text-sm text-gray-700 dark:text-gray-200">
                {{ field.label }}
                <span
                    v-if="required"
                    class="text-red-600 dark:text-red-400">
                    *
                </span>
            </label>
        </div>
        <div
            v-if="field.description"
            class="ml-6 mt-1 text-xs text-gray-500 dark:text-gray-400">
            {{ field.description }}
        </div>
        <transition-container type="slide-bottom">
            <InputError
                v-show="!!error"
                class="absolute -top-1 left-3"
                :message="error" />
        </transition-container>
    </div>
</template>
