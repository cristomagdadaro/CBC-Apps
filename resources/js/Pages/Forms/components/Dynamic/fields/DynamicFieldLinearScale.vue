<script>
/**
 * DynamicFieldLinearScale.vue - Linear scale (customizable range) field component
 */
export default {
    name: "DynamicFieldLinearScale",
    props: {
        modelValue: { type: [Number, String], default: null },
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
                this.$emit("update:modelValue", Number(val));
            },
        },
        min() {
            return this.field.field_config?.min || 1;
        },
        max() {
            return this.field.field_config?.max || 10;
        },
        minLabel() {
            return this.field.field_config?.minLabel || "";
        },
        maxLabel() {
            return this.field.field_config?.maxLabel || "";
        },
        scaleValues() {
            const values = [];
            for (let i = this.min; i <= this.max; i++) {
                values.push(i);
            }
            return values;
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
        <div
            v-if="field.description"
            class="mb-2 text-xs text-gray-600 dark:text-gray-400">
            {{ field.description }}
        </div>

        <div
            class="rounded-md bg-gray-50 p-2 transition-colors dark:bg-gray-800"
            :class="{
                'border border-red-500 dark:border-red-600': error,
                'border border-gray-200 dark:border-gray-700': !error,
            }">
            <div class="mb-1 flex items-center justify-between">
                <span
                    v-if="minLabel"
                    class="text-xs text-gray-600 dark:text-gray-400">
                    {{ minLabel }}
                </span>
                <span
                    v-if="maxLabel"
                    class="ml-auto text-xs text-gray-600 dark:text-gray-400">
                    {{ maxLabel }}
                </span>
            </div>
            <div class="flex items-center justify-evenly gap-1 overflow-x-auto py-1">
                <label
                    v-for="value in scaleValues"
                    :key="value"
                    class="group flex flex-shrink-0 cursor-pointer flex-col items-center">
                    <input
                        type="radio"
                        :name="field.field_key"
                        :value="value"
                        v-model="inputValue"
                        class="sr-only" />
                    <span
                        class="flex h-7 w-7 items-center justify-center rounded-full border-2 text-sm font-semibold transition-all"
                        :class="{
                            'border-AB bg-AB text-white': inputValue === value,
                            'border-gray-300 bg-white text-gray-700 group-hover:border-AB group-hover:bg-blue-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:group-hover:border-blue-400 dark:group-hover:bg-blue-900/20': inputValue !== value,
                        }">
                        {{ value }}
                    </span>
                </label>
            </div>
        </div>

        <transition-container type="slide-bottom">
            <InputError
                v-show="!!error"
                class="mt-1"
                :message="error" />
        </transition-container>
    </div>
</template>
