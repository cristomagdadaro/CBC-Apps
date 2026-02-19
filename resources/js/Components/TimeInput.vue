<script>
export default {
    name: 'TimeInput',
    props: {
        modelValue: { type: String, default: '' },
        placeholder: { type: String, default: '' },
        error: { type: String, default: '' },
        label: { type: String, default: '' },
        required: { type: Boolean, default: false },
        chameleon: { type: Boolean, default: false },
    },
    emits: ['update:modelValue'],
    data() {
        return {
            isChameleon: this.chameleon,
        };
    },
    mounted() {
        const input = this.$refs.input;
        if (input && input.hasAttribute && input.hasAttribute('autofocus')) {
            input.focus();
        }
    },
    watch: {
        chameleon(newVal) {
            this.isChameleon = newVal;
        }
    },
    methods: {
        formatTime(value) {
            if (!value) return '';
            const [hours, minutes] = value.split(':');
            return `${hours}:${minutes}:00`;
        }
    }
}
</script>

<template>
    <div class="w-full relative border-none p-0" :class="{'border-red-500': error}">
        <div v-if="label" class="text-xs text-gray-500 flex items-center justify-between">
            <span class="flex gap-0.5 whitespace-nowrap">{{ label }}<b v-if="required" class="text-red-500 ">*</b></span>
            <transition-container type="slide-bottom">
                <InputError v-show="!!error" :message="error" />
            </transition-container>
        </div>
        <input
            ref="input"
            class="border w-full dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-AB dark:focus:border-AB focus:ring-AB dark:focus:ring-AB rounded-md shadow-sm"
            :value="modelValue"
            :placeholder="placeholder"
            type="time"
            name="trip-start"
            @input="$emit('update:modelValue', formatTime($event.target.value))"
        />
    </div>
</template>
