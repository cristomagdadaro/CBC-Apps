<script>
import ScanBtn from "@/Components/Buttons/ScanBtn.vue";
import {Link} from "@inertiajs/vue3";
import AddButton from "@/Components/Buttons/AddButton.vue";

export default {
    name: "SupplierHeaderActions",
    components: {ScanBtn, Link, AddButton}
}
</script>

<template>
    <div class="flex justify-between items-center">
        <Link :href="route('suppliers.index')" class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Registered Suppliers
        </Link>
        <div class="flex justify-between items-center">
            <Link :href="route('suppliers.create')">
                <add-button  title="Register a new Supplier" />
            </Link>
        </div>
    </div>
</template>

<style scoped>

</style>
