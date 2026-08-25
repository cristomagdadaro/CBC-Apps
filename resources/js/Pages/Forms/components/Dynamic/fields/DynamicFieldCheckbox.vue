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
        class="relative flex w-full cursor-pointer items-center gap-2 rounded-md bg-white px-1 py-2 text-center leading-none transition-colors hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700"
        :class="{
            'border-red-500 dark:border-red-600': error,
            'border-gray-600 dark:border-gray-700': !error,
        }"
        @click.prevent="inputValue = !inputValue">
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
        <transition-container type="slide-bottom">
            <InputError
                v-show="!!error"
                class="absolute -top-1 left-3"
                :message="error" />
        </transition-container>
    </div>
</template>
