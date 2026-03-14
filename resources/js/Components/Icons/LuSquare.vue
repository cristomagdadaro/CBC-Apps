<script setup lang="ts">
import { Square } from 'lucide-vue-next'

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
    <Square :size="size" :stroke-width="strokeWidth" v-bind="$attrs" />
</template>
