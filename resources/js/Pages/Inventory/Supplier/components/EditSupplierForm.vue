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
import SupplierHeaderActions from "@/Pages/Inventory/Supplier/components/SupplierHeaderActions.vue";

export default {
    name: "EditSupplierForm",
    components: {
        SupplierHeaderActions,
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
        this.setFormAction('update');
    },
}
</script>

<template>
    <AppLayout title="Update Personnel Information">
        <template #header>
            <supplier-header-actions />
        </template>

        <form v-if="!!form" @submit.prevent="submitUpdate" class="py-12 max-w-xl mx-auto">
            <div class="flex flex-col gap-2 w-full mx-auto sm:p-2 lg:p-4 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="flex flex-col">
                    <h2 class="font-bold uppercase leading-none py-2 mb-1 border-b">Supplier Update Form</h2>
                    <p>Use this form to update supplier details.</p>
                </div>
                <text-input required label="Company Name" v-model="form.name" :error="form.errors.name" />
                <text-input label="Email" v-model="form.email" :error="form.errors.email" />
                <text-input label="Phone" v-model="form.phone" :error="form.errors.phone" />
                <text-input label="Address" v-model="form.address" :error="form.errors.address" />
                <text-area label="Description" v-model="form.description" :error="form.errors.description" />
                <div class="flex gap-1 justify-between">
                    <reset-btn @click="resetField($page.props.data)">
                        Reset
                    </reset-btn>
                    <submit-btn :disabled="model.api.processing">
                        <span v-if="model.api.processing">Updating</span>
                        <span v-else>Update</span>
                    </submit-btn>
                </div>
                <div class="flex flex-col w-full text-xs text-gray-400 border-t border-gray-500 pt-1">
                        <span>
                            Date Created: {{  $page.props.data.created_at }}
                        </span>
                    <span>
                            Last Updated: {{  $page.props.data.updated_at }}
                        </span>
                </div>
            </div>
        </form>
    </AppLayout>
</template>

<style scoped>

</style>
