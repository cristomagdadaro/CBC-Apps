<script>
import AddButton from "@/Components/Buttons/AddButton.vue";
import {Link} from "@inertiajs/vue3";

export default {
    name: "FormsHeaderActions",
    components: {Link, AddButton}
}
</script>

<template>
    <div class="flex justify-between items-center">
        <Link :href="route('forms.index')" class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Attendance Forms
        </Link>
        <div class="flex justify-between items-center">
            <Link :href="route('forms.create')">
                <add-button  title="Create new form" />
            </Link>
        </div>
    </div>
</template>

<style scoped>

</style>
