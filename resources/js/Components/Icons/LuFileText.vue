<script setup lang="ts">
import { FileText } from 'lucide-vue-next'

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
    <FileText :size="size" :stroke-width="strokeWidth" v-bind="$attrs" />
</template>
