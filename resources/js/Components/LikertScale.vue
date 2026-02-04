<script>
export default {
    name: 'LikertScale',
    props: {
        modelValue: {
            type: [Number, String],
            default: null,
        },
        label: {
            type: String,
            required: true,
        },
        name: {
            type: String,
            required: true,
        },
        required: {
            type: Boolean,
            default: false,
        },
        error: {
            type: String,
            default: '',
        },
        helpText: {
            type: String,
            default: '',
        }
    },
    emits: ['update:modelValue', 'clear-error'],
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
    <div class="flex flex-col gap-1 text-sm">
        <div class="flex justify-between items-end">
            <label class="font-medium">
                {{ label }}
                <span v-if="required" class="text-red-600">*</span>
            </label>
            <span v-if="helpText" class="text-[10px] text-gray-500">{{ helpText }}</span>
        </div>
        <div class="flex items-center justify-between gap-2 mt-1">
            <button
                v-for="opt in options"
                :key="opt"
                type="button"
                class="flex-1 py-1.5 text-xs border rounded-md text-center transition-colors "
                :class="{
                    'border-red-600' : error,
                    'bg-blue-600 text-white border-blue-600 shadow-sm': String(modelValue) === String(opt),
                    'bg-white text-gray-700 border-gray-300 hover:bg-gray-50': String(modelValue) !== String(opt)
                }"
                @click="onChange(opt)"
            >
                {{ opt }}
            </button>
        </div>
        <p class="mt-0.5 text-[10px] text-gray-500 flex justify-between">
            <span>1 - Strongly Disagree</span>
            <span>5 - Strongly Agree</span>
        </p>
        <p v-if="error" class="text-[11px] text-red-600 mt-0.5">{{ error }}</p>
    </div>
</template>

<style scoped>
</style>

