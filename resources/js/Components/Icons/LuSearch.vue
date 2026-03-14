<script setup lang="ts">
import { Search } from 'lucide-vue-next'

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
    <Search :size="size" :stroke-width="strokeWidth" v-bind="$attrs" />
</template>
