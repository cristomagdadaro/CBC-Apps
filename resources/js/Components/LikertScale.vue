<script>
import FieldMixin from '@/Components/Forms/FieldMixin';

export default {
    name: 'LikertScale',
    mixins: [FieldMixin],
    props: {
        helpText: {
            type: String,
            default: '',
        }
    },
    emits: ['clear-error'],
    data() {
        return {
            options: [1, 2, 3, 4, 5],
        };
    },
    methods: {
        onChange(value) {
            this.$emit('update:modelValue', value);
            this.$emit('clear-error');
        }
    }
}
</script>

<template>
    <div class="flex flex-col gap-1 text-sm w-full">
        <div class="flex justify-between items-end">
            <label class="font-medium text-slate-700 dark:text-slate-300">
                {{ label }}
                <span v-if="required" class="text-red-500">*</span>
            </label>
            <span v-if="helpText || hint" class="text-[10px] text-slate-500">{{ helpText || hint }}</span>
        </div>
        <div class="flex items-center justify-between gap-2 mt-1">
            <button
                v-for="opt in options"
                :key="opt"
                type="button"
                :disabled="disabled"
                class="flex-1 py-1.5 text-xs border rounded-md text-center transition-colors font-medium"
                :class="{
                    'opacity-50 cursor-not-allowed': disabled,
                    'border-red-500': error,
                    'bg-indigo-600 text-white border-indigo-600 shadow-sm dark:bg-indigo-500 dark:border-indigo-500': String(modelValue) === String(opt),
                    'bg-white text-slate-700 border-slate-300 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700': String(modelValue) !== String(opt)
                }"
                @click="onChange(opt)"
            >
                {{ opt }}
            </button>
        </div>
        <p class="mt-0.5 text-[10px] text-slate-500 flex justify-between">
            <span>1 - Strongly Disagree</span>
            <span>5 - Strongly Agree</span>
        </p>
        <p v-if="error" class="text-xs text-red-500 mt-0.5">{{ error }}</p>
    </div>
</template>

<style scoped>
</style>

