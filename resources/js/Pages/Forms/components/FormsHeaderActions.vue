<script>
import AddButton from "@/Components/Buttons/AddButton.vue";
import {Link} from "@inertiajs/vue3";
import ScanBtn from "@/Components/Buttons/ScanBtn.vue";

export default {
    name: "FormsHeaderActions",
    components: {ScanBtn, Link, AddButton}
}
</script>

<template>
    <div class="flex justify-between items-center">
        <Link :href="route('forms.index')" class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Event Forms
        </Link>
        <div class="flex justify-between items-center">
            <Link :href="route('forms.create')">
                <add-button  title="Create new form" />
            </Link>
            <Link :href="route('forms.scan')">
                <scan-btn title="Scan QR code" />
            </Link>
        </div>
    </div>
</template>

<style scoped>

</style>
