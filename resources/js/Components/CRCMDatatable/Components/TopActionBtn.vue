<template>
    <base-button
        :top-text="topText"
        class="flex items-center justify-center gap-0.5 h-8 w-8 sm:h-8.5 sm:w-8.5 p-0">
        <div class="flex items-center justify-center w-full h-full">
            <slot name="icon" />
            <slot />
        </div>
    </base-button>
</template>
<script setup>
import BaseButton from "@/Components/CRCMDatatable/Components/BaseButton.vue";

defineProps({
    topText: {
        type: [String, Number],
        default: null,
    },
});
</script>
