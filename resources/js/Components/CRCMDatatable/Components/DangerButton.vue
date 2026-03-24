<template>
    <base-button
        type="button"
        class="p-2 bg-red-300 hover:bg-red-600 active:bg-red-700 text-white"
    >
        <slot />
    </base-button>
</template>
<script setup>
import BaseButton from "@/Components/CRCMDatatable/Components/BaseButton.vue";
</script>
