<script>
import {Link} from "@inertiajs/vue3";
import AddButton from "@/Components/Buttons/AddButton.vue";
import ScanBtn from "@/Components/Buttons/ScanBtn.vue";

export default {
    name: "ItemsHeaderActions",
    components: {ScanBtn, Link, AddButton}
}
</script>

<template>
    <div class="flex justify-between items-center">
        <Link :href="route('items.index')" class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            List of Items
        </Link>
        <div class="flex justify-between items-center">
            <Link :href="route('items.create')">
                <add-button  title="Register a new Consumable" />
            </Link>
        </div>
    </div>
</template>

<style scoped>

</style>
