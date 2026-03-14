<script setup lang="ts">
import { QrCode } from 'lucide-vue-next'

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
    <QrCode :size="size" :stroke-width="strokeWidth" v-bind="$attrs" />
</template>
