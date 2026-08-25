<script>
import FieldMixin from "@/Components/Forms/FieldMixin";

export default {
    name: "LikertScale",
    mixins: [FieldMixin],
    props: {
        helpText: {
            type: String,
            default: "",
        },
    },
    emits: ["clear-error"],
    data() {
        return {
            options: [1, 2, 3, 4, 5],
        };
    },
    methods: {
        onChange(value) {
            this.$emit("update:modelValue", value);
            this.$emit("clear-error");
        },
    },
};
</script>

<template>
    <div class="flex w-full flex-col gap-1 text-sm">
        <div class="flex items-end justify-between">
            <label class="font-medium text-slate-700 dark:text-slate-300">
                {{ label }}
                <span
                    v-if="required"
                    class="text-red-500">
                    *
                </span>
            </label>
            <span
                v-if="helpText || hint"
                class="text-[10px] text-slate-500">
                {{ helpText || hint }}
            </span>
        </div>
        <div class="mt-1 flex items-center justify-between gap-2">
            <button
                v-for="opt in options"
                :key="opt"
                type="button"
                :disabled="disabled"
                class="flex-1 rounded-md border py-1.5 text-center text-xs font-medium transition-colors"
                :class="{
                    'cursor-not-allowed opacity-50': disabled,
                    'border-red-500': error,
                    'border-indigo-600 bg-indigo-600 text-white shadow-sm dark:border-indigo-500 dark:bg-indigo-500': String(modelValue) === String(opt),
                    'border-slate-300 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700': String(modelValue) !== String(opt),
                }"
                @click="onChange(opt)">
                {{ opt }}
            </button>
        </div>
        <p class="mt-0.5 flex justify-between text-[10px] text-slate-500">
            <span>1 - Strongly Disagree</span>
            <span>5 - Strongly Agree</span>
        </p>
        <p
            v-if="error"
            class="mt-0.5 text-xs text-red-500">
            {{ error }}
        </p>
    </div>
</template>

<style scoped></style>
