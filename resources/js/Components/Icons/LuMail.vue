<script setup lang="ts">
import { Mail } from 'lucide-vue-next'

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
    <Mail :size="size" :stroke-width="strokeWidth" v-bind="$attrs" />
</template>
