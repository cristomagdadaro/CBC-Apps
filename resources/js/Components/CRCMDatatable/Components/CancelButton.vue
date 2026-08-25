<template>
    <base-button
        type="reset"
        class="bg-gray-500 py-2 text-white hover:bg-gray-600 active:bg-gray-700">
        <slot />
    </base-button>
</template>
<script setup>
import BaseButton from "@/Components/CRCMDatatable/Components/BaseButton.vue";
import CloseIcon from "@/Components/Icons/CloseIcon.vue";
</script>
