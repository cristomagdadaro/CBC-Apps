<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-show="message" class="text-[0.7rem] leading-none text-red-600 bg-opacity-50 w-fit px-2 py-0.5 rounded">
        {{ message }}
    </div>
</template>
