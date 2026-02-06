<script>
import ListOfForms from "@/Pages/Forms/components/ListOfForms.vue";
import AddButton from "@/Components/Buttons/AddButton.vue";
import {Link} from "@inertiajs/vue3";
import FormsHeaderActions from "@/Pages/Forms/components/FormsHeaderActions.vue";
import TextInput from "@/Components/TextInput.vue";
import TextArea from "@/Components/TextArea.vue";
import DateInput from "@/Components/DateInput.vue";
import TimeInput from "@/Components/TimeInput.vue";
import Form from "@/Modules/domain/Form";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import GuestCard from "@/Pages/Forms/components/GuestCard.vue";
import RequirementsManager from "@/Components/Forms/RequirementsManager.vue";
import FormStyleDesigner from "@/Pages/Forms/components/FormStyleDesigner.vue";

export default {
    name: "FormCreate",
    components: {
        FormStyleDesigner,
        RequirementsManager,
        GuestCard,
        TimeInput, DateInput, TextArea, TextInput, FormsHeaderActions, Link, AddButton, ListOfForms},
    mixins: [ApiMixin],
    beforeMount() {
        this.model = new Form();
        this.setFormAction('create');
        if (!this.form.requirements) {
            this.form.requirements = [];
        }
    },
    computed: {
        styleTokensError() {
            if (!this.form?.errors) {
                return null;
            }
            const entry = Object.entries(this.form.errors).find(([key]) => key.startsWith('style_tokens'));
            return entry ? entry[1] : null;
        }
    },
    methods: {
        async submitProxyCreate() {
            this.form.requirements = this.form.requirements || [];
            await this.submitCreate();
        },
    },
};
</script>

<template>
    <AppLayout title="Build New Attendance Form">
        <template #header>
            <forms-header-actions />
        </template>
        <div class="flex flex-col md:flexrow justify-center gap-5 py-12">
            <div class="flex flex-col w-1/4">
                <span class="font-semibold text-gray-700">Event Details</span>
                <form v-if="!!form" @submit.prevent="submitProxyCreate">
                    <div class="w-full mx-auto">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden sm:rounded-lg">
                            <div class="border p-2 rounded-md flex flex-col gap-2">
                                <div class="flex flex-row w-full gap-2 bg-gray-200 p-2 rounded-md justify-between shadow py-4">
                                    <div class="flex flex-col justify-center gap-1 w-full">
                                        <text-input placeholder="Title" v-model="form.title" :error="form.errors.title"/>
                                        <text-area placeholder="Form Description" v-model="form.description" :error="form.errors.description" class="text-xs" />
                                    </div>
                                    <div class="flex flex-col items-center justify-center">
                                        <label class="text-2xl leading-none font-[1000]">####</label>
                                        <span class="text-[0.6rem] leading-none ">Form ID</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 grid-rows-2 px-1 gap-2">
                                    <div>
                                        <span class="font-bold uppercase">Start Date: </span>
                                        <date-input v-model="form.date_from" :error="form.errors.date_from" />
                                    </div>
                                    <div>
                                        <span class="font-bold uppercase">End Date: </span>
                                        <date-input v-model="form.date_to" :error="form.errors.date_to" />
                                    </div>
                                    <div>
                                        <span class="font-bold uppercase">Start Time: </span>
                                        <time-input v-model="form.time_from" :error="form.errors.time_from" />
                                    </div>
                                    <div>
                                        <span class="font-bold uppercase">End Time: </span>
                                        <time-input v-model="form.time_to" :error="form.errors.time_to" />
                                    </div>
                                </div>
                                <div class="px-1 flex flex-col gap-1">
                                    <div>
                                        <span class="font-bold uppercase">Venue: </span>
                                        <text-input placeholder="Venue" v-model="form.venue" class="text-sm" :error="form.errors.venue"/>
                                    </div>
                                    <div>
                                        <span class="font-bold uppercase">Other details: </span>
                                        <text-area placeholder="Other details" v-model="form.details" class="w-full text-xs" :error="form.errors.details"/>
                                    </div>
                                </div>
                                <div class="px-1 flex flex-col gap-1">
                                    <requirements-manager v-model="form.requirements" :error="form.errors.requirements"/>
                                </div>
                                <div class="flex flex-col p-2">
                                    <div class="flex gap-1 justify-end">
                                        <button class="bg-blue-200 text-blue-900 w-fit px-4 py-2 rounded" title="Temporarily stop accepting responses">
                                            Save
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="px-1 flex flex-col gap-1">
                <form-style-designer v-model="form.style_tokens" :error="styleTokensError" />
            </div>
            <div class="flex flex-col w-fit">
                <span class="font-semibold text-gray-700">Preview</span>
                <guest-card :data="form" class="bg-white drop-shadow-lg"/>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
