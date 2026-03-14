<script>
import {Link} from "@inertiajs/vue3";

export default {
    name: "FormsHeaderActions",
    components: {Link}
}
</script>

<template>
    <ActionHeaderLayout title="Certificate Generator" subtitle="Bulk-generate and email certificates using PPTX template and XLSX data." :route-link="route('certificates.generator')">
        <Link :href="route('forms.create')" title="Create Event">
            <lu-plus-circle class="h-auto w-5 text-white dark:text-gray-800 dark:bg-gray-200 mx-1" />
        </Link>
        <Link :href="route('forms.builder')" title="Form Builder">
            <lu-layout-dashboard />
        </Link>
        <Link :href="route('forms.scan')" title="Scan QR">
            <lu-qr-code />
        </Link>
    </ActionHeaderLayout>
</template>

<style scoped>

</style>
