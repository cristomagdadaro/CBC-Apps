<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div
        v-show="message"
        class="w-fit rounded bg-opacity-50 px-2 py-0.5 text-[0.7rem] leading-none text-red-600">
        {{ message }}
    </div>
</template>
