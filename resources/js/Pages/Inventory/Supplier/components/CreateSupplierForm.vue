<script>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import LoaderIcon from "@/Components/Icons/LoaderIcon.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {Head} from "@inertiajs/vue3";
import ApiMixin from "@/Modules/mixins/ApiMixin.js";
import Supplier from "@/Modules/domain/Supplier";
import PersonnelHeaderActions from "@/Pages/Inventory/Personnel/components/presentation/PersonnelHeaderActions.vue";
import SubmitBtn from "@/Components/Buttons/SubmitBtn.vue";
import TextInput from "@/Components/TextInput.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import ResetBtn from "@/Components/Buttons/ResetBtn.vue";
import TextArea from "@/Components/TextArea.vue";
import CancelBtn from "@/Components/Buttons/CancelBtn.vue";
import SupplierHeaderActions from "@/Pages/Inventory/Supplier/components/SupplierHeaderActions.vue";

export default {
    name: "CreateSupplierForm",
    components: {
        SupplierHeaderActions,
        CancelBtn,
        TextArea,
        ResetBtn,
        AppLayout,
        TextInput,
        SubmitBtn,
        PersonnelHeaderActions,
        Head, SecondaryButton,
        LoaderIcon, PrimaryButton
    },
    mixins: [ApiMixin],
    beforeMount() {
        this.model = new Supplier();
        this.setFormAction('create');
    },
}
</script>

<template>
    <AppLayout title="Update Personnel Information">
        <template #header>
            <supplier-header-actions />
        </template>

        <form v-if="!!form" @submit.prevent="submitCreate" class="py-12 max-w-xl mx-auto">
            <div class="flex flex-col gap-2 w-full mx-auto sm:p-2 lg:p-4 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="flex flex-col">
                    <h2 class="font-bold uppercase leading-none py-2 mb-1 border-b">Supplier Registration Form</h2>
                    <p>Use this form to register new suppliers of goods and services.</p>
                </div>
                <text-input required label="Company Name" v-model="form.name" :error="form.errors.name" />
                <text-input label="Email" v-model="form.email" :error="form.errors.email" />
                <text-input label="Phone" v-model="form.phone" :error="form.errors.phone" />
                <text-input label="Address" v-model="form.address" :error="form.errors.address" />
                <text-area label="Description" v-model="form.description" :error="form.errors.description" />
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
