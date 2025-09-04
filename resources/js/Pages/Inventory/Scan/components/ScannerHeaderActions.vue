<script setup>

import ScanBtn from "@/Components/Buttons/ScanBtn.vue";
import {Link} from "@inertiajs/vue3";
import AddButton from "@/Components/Buttons/AddButton.vue";
</script>

<template>
    <div class="flex justify-between items-center">
        <Link :href="route('scan.index')" class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Inventory
        </Link>
    </div>
</template>

<style scoped>

</style>
