<template>
    <base-button :disabled="disabled" :classes="disabled?'bg-gray-400 opacity-50':'bg-[#006837] text-white'" class="text-xs bg-AA text-white drop-shadow">
        <template v-slot:icon>
            <slot name="icon" />
        </template>
        <slot />
    </base-button>
</template>
<script setup>
import BaseButton from "@/Components/Buttons/BaseBtn.vue";

defineProps({
    disabled: Boolean,
})
</script>
