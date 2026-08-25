<template>
    <tr
        class="dtBodyRow h-10 rounded border border-gray-300 active:bg-gray-400 sm:h-14"
        :class="isSelected ? 'bg-blue-400 duration-300 hover:brightness-75' : 'hover:bg-gray-300'">
        <slot />
    </tr>
</template>
<script setup>
defineProps({
    isSelected: {
        type: Boolean,
        default: false,
    },
});
</script>
