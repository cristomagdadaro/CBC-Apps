<template>
    <base-button
        type="reset"
        class="py-2 bg-gray-500 hover:bg-gray-600 active:bg-gray-700 text-white"
    >
        <slot />
    </base-button>
</template>
<script setup>
import BaseButton from "@/Components/CRCMDatatable/Components/BaseButton.vue";
import CloseIcon from "@/Components/Icons/CloseIcon.vue";
</script>
