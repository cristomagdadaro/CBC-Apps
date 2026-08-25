<template>
    <base-button
        :top-text="topText"
        class="sm:h-8.5 sm:w-8.5 flex h-8 w-8 items-center justify-center gap-0.5 p-0">
        <div class="flex h-full w-full items-center justify-center">
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
