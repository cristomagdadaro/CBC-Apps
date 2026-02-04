<script>
import {Head, router} from "@inertiajs/vue3";
import LoaderIcon from "@/Components/Icons/LoaderIcon.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Personnel from "@/Modules/domain/Personnel";
import FormsHeaderActions from "@/Pages/Forms/components/FormsHeaderActions.vue";
import PersonnelHeaderActions from "@/Pages/Inventory/Personnel/components/PersonnelHeaderActions.vue";

export default {
    name: "CreatePersonnelForm",
    components: {
        PersonnelHeaderActions,
        FormsHeaderActions, LoaderIcon, Head},
    mixins: [ApiMixin],
    beforeMount() {
        this.model = new Personnel();
        this.setFormAction('create');
    }
}
</script>

<template>
    <AppLayout title="Register a New Personnel">
        <template #header>
            <personnel-header-actions />
        </template>

        <form v-if="!!form" @submit.prevent="submitCreate" class="py-12 max-w-3xl mx-auto">
            <div class="flex flex-col gap-2 w-full mx-auto sm:p-2 lg:p-4 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="flex flex-col">
                    <h2 class="font-bold uppercase leading-none py-2 mb-1 border-b">Personnel Registration Form</h2>
                    <p>Use this form to register new personnel.</p>
                </div>
                <div class="flex sm:flex-row flex-col gap-1">
                    <text-input required label="First Name" v-model="form.fname" :error="form.errors.fname" />
                    <text-input label="Middle Name" v-model="form.mname" :error="form.errors.mname" />
                    <text-input required label="Last Name" v-model="form.lname" :error="form.errors.lname" />
                    <text-input label="suffix" v-model="form.suffix" :error="form.errors.suffix" />
                </div>
                <div class="flex flex-col gap-2">
                    <text-input required label="Position" v-model="form.position" :error="form.errors.position" />
                    <text-input label="Phone" v-model="form.phone" :error="form.errors.phone" />
                    <text-input required label="Email" v-model="form.email" :error="form.errors.email" />
                </div>
                <text-input label="Address" v-model="form.address" :error="form.errors.address" />
                <div class="flex gap-1 justify-end">
                    <submit-btn :disabled="model.api.processing">
                        <span v-if="model.api.processing">Saving</span>
                        <span v-else>Save</span>
                    </submit-btn>
                </div>
            </div>
        </form>
    </AppLayout>
</template>

<style scoped>

</style>
