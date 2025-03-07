<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-show="message" class="text-xs text-red-600 dark:text-red-400 bg-white leading-none w-fit px-2 py-0.5 rounded">
        {{ message }}
    </div>
</template>
