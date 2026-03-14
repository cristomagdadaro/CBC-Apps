<script setup lang="ts">
import { ClipboardList } from 'lucide-vue-next'

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
    <ClipboardList :size="size" :stroke-width="strokeWidth" v-bind="$attrs" />
</template>
