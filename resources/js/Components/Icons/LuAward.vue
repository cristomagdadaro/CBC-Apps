<script setup lang="ts">
import { Award } from 'lucide-vue-next'

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
    <Award :size="size" :stroke-width="strokeWidth" v-bind="$attrs" />
</template>
