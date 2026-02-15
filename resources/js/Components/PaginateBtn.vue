<template>
    <base-btn :disabled="disabled" :classes="disabled?'bg-gray-400 opacity-50':'bg-[#006837] text-white'" class="text-xs bg-AA hover:bg-AB duration-150 text-white drop-shadow">
        <template v-slot:icon>
            <slot name="icon" />
        </template>
        <slot />
    </base-btn>
</template>
<script setup>
defineProps({
    disabled: Boolean,
})
</script>
