<script>
export default {
    name: 'TextArea',
    props: {
        modelValue: { type: [String, Number], default: '' },
        autocomplete: { type: String, default: '' },
        placeholder: { type: String, default: '' },
        error: { type: String, default: '' },
        type: { type: String, default: '' },
        classes: { type: String, default: '' },
        required: { type: Boolean, default: false },
        id: { type: String, default: '' },
        label: { type: String, default: '' },
        rows: { type: Number, default: 4 },
        guide: { type: String, default: null },
        datalistId: { type: String, default: null },
        datalistOptions: { type: Array, default: null },
    },
    emits: ['update:modelValue'],
    data() {
        return {
            // input ref is handled via $refs.input directly
        }
    },
    mounted() {
        if (this.$refs.input && this.$refs.input.hasAttribute('autofocus')) {
            this.$refs.input.focus();
        }
        this.$nextTick(() => this.adjustHeight());
    },
    watch: {
        modelValue() {
            this.$nextTick(() => this.adjustHeight());
        }
    },
    methods: {
        adjustHeight() {
            const textarea = this.$refs.input;
            if (!textarea) return;
            const lineHeight = parseFloat(getComputedStyle(textarea).lineHeight) || 20;
            const minHeight = this.rows * lineHeight;
            textarea.style.height = 'auto';
            const newHeight = textarea.scrollHeight;
            textarea.style.height = `${Math.max(newHeight, minHeight)}px`;
        },
        onInput(e) {
            this.$emit('update:modelValue', e.target.value);
            this.adjustHeight();
        },
        focus() {
            this.$refs.input?.focus();
        }
    }
}
</script>

<template>
    <div class="w-full relative " :class="{'border-red-500': error}">
        <div v-if="label" class="text-xs text-gray-500 flex items-center justify-between">
            <span class="flex gap-0.5 whitespace-nowrap">{{ label }}<b v-if="required" class="text-red-500 ">*</b></span>
            <transition-container type="slide-bottom">
                <InputError v-show="!!error" :message="error" />
            </transition-container>
        </div>
        <textarea
            ref="input"
            :rows="rows"
            class="leading-tight w-full focus:border-AB focus:ring-AB rounded-md shadow-sm resize-none overflow-hidden"
            :value="modelValue"
            :placeholder="placeholder"
            @input="onInput"
            :style="{ minHeight: rows * 20 + 'px' }"
            :list="datalistId"
        />
        <datalist v-if="datalistId && datalistOptions && datalistOptions.length" :id="datalistId">
            <option v-for="opt in datalistOptions" :key="opt" :value="opt" />
        </datalist>
        <p v-if="guide" class="mt-1 text-xs text-gray-500">{{ guide }}</p>
    </div>
</template>
