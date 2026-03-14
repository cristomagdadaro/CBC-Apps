<script setup lang="ts">
import { FlaskConical } from 'lucide-vue-next'

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
    <FlaskConical :size="size" :stroke-width="strokeWidth" v-bind="$attrs" />
</template>
