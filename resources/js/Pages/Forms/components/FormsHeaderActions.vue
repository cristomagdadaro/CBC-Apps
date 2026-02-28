<script>
import {Link} from "@inertiajs/vue3";

export default {
    name: "FormsHeaderActions",
    components: {Link}
}
</script>

<template>
    <ActionHeaderLayout title="Event Forms" subtitle="Manage event forms" :route-link="route('forms.index')">
        <Link :href="route('forms.create')" title="Create Event">
            <add-icon class="h-auto w-5 text-white dark:text-gray-800 dark:bg-gray-200 mx-1" />
        </Link>
        <Link :href="route('forms.builder')" title="Form Builder">
            <svg class="h-auto w-5 text-white dark:text-gray-800 dark:bg-gray-200 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
            </svg>
        </Link>
        <Link :href="route('forms.scan')" title="Scan QR">
            <scan-icon class="w-5 h-5 text-white dark:text-gray-800 dark:bg-gray-200 mx-1" />
        </Link>
    </ActionHeaderLayout>
</template>

<style scoped>

</style>
