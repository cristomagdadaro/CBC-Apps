<script setup lang="ts">
import { Volume2 } from 'lucide-vue-next'

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
    <Volume2 :size="size" :stroke-width="strokeWidth" v-bind="$attrs" />
</template>
