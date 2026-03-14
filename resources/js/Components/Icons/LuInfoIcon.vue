<script setup lang="ts">
import { InfoIcon } from 'lucide-vue-next'

withDefaults(
    defineProps<{
        size?: number
        strokeWidth?: number
    }>(),
    {
        size: 20,
        strokeWidth: 2,
    },
)
</script>

<template>
    <InfoIcon :size="size" :stroke-width="strokeWidth" v-bind="$attrs" />
</template>
