<script setup lang="ts">
import { Settings2 } from 'lucide-vue-next'

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
    <Settings2 :size="size" :stroke-width="strokeWidth" v-bind="$attrs" />
</template>
