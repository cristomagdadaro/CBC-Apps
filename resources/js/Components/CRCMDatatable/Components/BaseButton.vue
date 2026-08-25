<template>
    <div
        v-if="topText"
        class="shadow-xs relative flex items-center justify-center rounded-xl text-xs font-semibold uppercase transition-all duration-200 active:scale-95"
        :class="classes">
        <span class="absolute -right-1 -top-1.5 z-10 rounded-full bg-lime-600 px-1.5 py-0.5 text-[0.6rem] font-bold text-white shadow-md">
            {{ topText }}
        </span>
        <button class="flex h-full w-full items-center justify-center"><slot /></button>
    </div>
    <button
        v-else
        :class="classes"
        class="shadow-xs flex items-center justify-center rounded-xl text-xs font-semibold uppercase transition-all duration-200 active:scale-95">
        <slot />
    </button>
</template>
<script setup>
defineProps({
    classes: {
        type: String,
        required: false,
    },
    topText: {
        type: [String, Number],
        default: null,
    },
});
</script>
