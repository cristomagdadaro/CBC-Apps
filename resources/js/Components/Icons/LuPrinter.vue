<script setup lang="ts">
import { Printer } from 'lucide-vue-next'

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
    <Printer :size="size" :stroke-width="strokeWidth" v-bind="$attrs" />
</template>
