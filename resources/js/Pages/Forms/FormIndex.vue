<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import Welcome from "@/Components/Welcome.vue";
import ListOfForms from "@/Pages/Forms/components/ListOfForms.vue";
import AddButton from "@/Components/Buttons/AddButton.vue";
import Modal from "@/Components/Modal.vue";
import { Link } from '@inertiajs/vue3';
import FormsHeaderActions from "@/Pages/Forms/components/FormsHeaderActions.vue";

export default {
    name: "FormIndex",
    props: {
        listOfForms: Object,
    },
    components: {FormsHeaderActions, Modal, AddButton, ListOfForms, Welcome, AppLayout, Link},
    data() {
        return {

        }
    }
}
</script>

<template>
    <AppLayout title="Attendance Forms">
        <template #header>
            <forms-header-actions />
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <list-of-forms :forms-data="listOfForms"/>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
