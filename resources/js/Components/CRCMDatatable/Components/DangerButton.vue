<template>
    <base-button
        type="button"
        class="bg-red-300 p-2 text-white hover:bg-red-600 active:bg-red-700">
        <slot />
    </base-button>
</template>
<script setup>
import BaseButton from "@/Components/CRCMDatatable/Components/BaseButton.vue";
</script>
