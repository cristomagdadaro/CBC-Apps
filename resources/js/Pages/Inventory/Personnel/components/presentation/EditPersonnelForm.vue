<script>
import {Head, router} from "@inertiajs/vue3";
import LoaderIcon from "@/Components/Icons/LoaderIcon.vue";
import TextInput from "@/Components/TextInput.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Personnel from "@/Pages/Inventory/Personnel/components/model/Personnel";
import AppLayout from "@/Layouts/AppLayout.vue";
import FormsHeaderActions from "@/Pages/Forms/components/FormsHeaderActions.vue";
import PersonnelHeaderActions from "@/Pages/Inventory/Personnel/components/presentation/PersonnelHeaderActions.vue";
import SubmitBtn from "@/Components/Buttons/SubmitBtn.vue";
import ResetBtn from "@/Components/Buttons/ResetBtn.vue";

export default {
    name: "EditPersonnelForm",
    components: {
        ResetBtn,
        SubmitBtn, PersonnelHeaderActions, FormsHeaderActions, AppLayout, PrimaryButton, SecondaryButton, TextInput, LoaderIcon, Head
    },
    mixins: [ApiMixin],
    beforeMount() {
        this.model = new Personnel();
        this.setFormAction('update');
    },
}
</script>

<template>

    <AppLayout title="Update Personnel Information">
        <template #header>
            <personnel-header-actions />
        </template>

        <form v-if="!!form" @submit.prevent="submitCreate" class="py-12 max-w-xl mx-auto">
            <div class="flex flex-col gap-2 w-full mx-auto sm:p-2 lg:p-4 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="flex sm:flex-row flex-col gap-1">
                    <text-input placeholder="First Name" v-model="form.fname" :error="form.errors.fname" />
                    <text-input placeholder="Middle Name" v-model="form.mname" :error="form.errors.mname" />
                    <text-input placeholder="Last Name" v-model="form.lname" :error="form.errors.lname" />
                    <text-input placeholder="suffix" v-model="form.suffix" :error="form.errors.suffix" />
                </div>
                <div class="flex flex-col gap-2">
                    <text-input placeholder="Position" v-model="form.position" :error="form.errors.position" />
                    <text-input placeholder="Phone" v-model="form.phone" :error="form.errors.phone" />
                    <text-input placeholder="Email" v-model="form.email" :error="form.errors.email" />
                </div>
                <text-input placeholder="Address" v-model="form.address" :error="form.errors.address" />
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
