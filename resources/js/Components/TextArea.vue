<script setup>
import { onMounted, ref, watch, nextTick } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    autocomplete: String,
    placeholder: String,
    error: String,
    type: String,
    classes: String,
    id: String,
    label: String,
    rows: {
        type: Number,
        default: 4,
    },
});

const emit = defineEmits(['update:modelValue']);

const input = ref(null);

function adjustHeight() {
    if (!input.value) return;
    // reset height so scrollHeight shrinks when content is removed
    input.value.style.height = 'auto';
    const newHeight = input.value.scrollHeight;
    input.value.style.height = `${newHeight}px`;
}

function onInput(e) {
    emit('update:modelValue', e.target.value);
    adjustHeight();
}

onMounted(() => {
    if (input.value && input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
    // ensure height adjusts after initial render/value
    nextTick(adjustHeight);
});

watch(() => props.modelValue, () => {
    // adjust when parent updates the value
    nextTick(adjustHeight);
});

defineExpose({ focus: () => input.value?.focus(), adjustHeight });
</script>

<template>
    <div class="w-full relative " :class="{'border-red-500': error}">
        <div v-if="label" class="text-xs text-gray-500 flex items-center justify-between">
            <span class="flex gap-0.5 whitespace-nowrap">{{ label }}</span>
        </div>
        <textarea
            ref="input"
            :rows="rows"
            class="leading-tight w-full focus:border-AB focus:ring-AB rounded-md shadow-sm resize-none overflow-hidden"
            :value="modelValue"
            :placeholder="placeholder"
            @input="onInput"
        />
    </div>
</template>
