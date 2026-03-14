<script setup lang="ts">
import { Grid3X3 } from 'lucide-vue-next'

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
    <Grid3X3 :size="size" :stroke-width="strokeWidth" v-bind="$attrs" />
</template>
