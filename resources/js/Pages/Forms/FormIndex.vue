<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import Welcome from "@/Components/Welcome.vue";
import ListOfForms from "@/Pages/Forms/components/ListOfForms.vue";

export default {
    name: "FormIndex",
    props: {
        listOfForms: Object,
    },
    components: {ListOfForms, Welcome, AppLayout}
}
</script>

<template>
    <AppLayout title="Attendance Forms">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Attendance Forms
            </h2>
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
