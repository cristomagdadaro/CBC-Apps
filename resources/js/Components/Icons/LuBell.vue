<script setup lang="ts">
import { Bell } from 'lucide-vue-next'

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
    <Bell :size="size" :stroke-width="strokeWidth" v-bind="$attrs" />
</template>
