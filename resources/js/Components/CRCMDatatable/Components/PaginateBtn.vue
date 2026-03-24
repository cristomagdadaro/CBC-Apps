<template>
  <base-button :disabled="disabled" :classes="disabled?'bg-gray-400 opacity-50':'bg-[#006837] text-white'" class="text-xs">
    <slot />
  </base-button>
</template>
<script setup>
import BaseButton from "@/Components/CRCMDatatable/Components/BaseButton.vue";

defineProps({
  disabled: Boolean,
})
</script>
