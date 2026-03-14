<script setup lang="ts">
import { TrendingUp } from 'lucide-vue-next'

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
    <TrendingUp :size="size" :stroke-width="strokeWidth" v-bind="$attrs" />
</template>
