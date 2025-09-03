<script>
import AddButton from "@/Components/Buttons/AddButton.vue";
import {Link} from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";
import AddIcon from "@/Components/Icons/AddIcon.vue";
import DeleteIcon from "@/Components/Icons/DeleteIcon.vue";

export default {
    name: "TransactionHeaderAction",
    components: {DeleteIcon, AddIcon, NavLink, Link, AddButton}
}
</script>

<template>
    <div class="flex justify-between items-center">
        <Link :href="route('forms.index')" class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Transaction History
        </Link>
        <div class="flex justify-between items-center gap-1">
            <nav-link :href="route('transactions.incoming')" class="flex items-center bg-blue-600 text-white px-2 py-1 rounded hover:shadow-md active:shadow-inner gap-1 text-sm active:scale-95 active:text-gray-300 hover:bg-green-700 hover:text-gray-200">
                <add-icon class="h-5 w-5" />
                <span>Incoming</span>
            </nav-link>
            <nav-link :href="route('transactions.outgoing')" class="flex items-center bg-red-600 text-white px-2 py-1 rounded hover:shadow-md active:shadow-inner gap-1 text-sm active:scale-95 active:text-gray-300 hover:bg-green-700 hover:text-gray-200">
                <add-icon class="h-5 w-5" />
                <span>Outgoing</span>
            </nav-link>
        </div>
    </div>
</template>

<style scoped>

</style>
