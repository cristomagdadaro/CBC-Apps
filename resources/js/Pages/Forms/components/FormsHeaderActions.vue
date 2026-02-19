<script>
import {Link} from "@inertiajs/vue3";

export default {
    name: "FormsHeaderActions",
    components: {Link}
}
</script>

<template>
    <ActionHeaderLayout title="Event Forms" subtitle="Manage event forms" :route-link="route('forms.index')">
        <Link :href="route('forms.create')">
            <add-icon class="h-auto w-5 text-white dark:text-gray-800 dark:bg-gray-200" />
        </Link>
        <Link :href="route('forms.scan')">
            <scan-btn class="h-auto w-5 text-white dark:text-gray-800 dark:bg-gray-200" />
        </Link>
    </ActionHeaderLayout>
</template>

<style scoped>

</style>
