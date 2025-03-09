<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
</script>

<template>
    <Link :href="'/'">
        <img src="/imgs/logo.png" alt="logo" class="w-auto h-20" />
    </Link>
</template>
