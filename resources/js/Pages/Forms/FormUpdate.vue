<script>
import ListOfForms from "@/Pages/Forms/components/ListOfForms.vue";
import { Link } from "@inertiajs/vue3";
import FormsHeaderActions from "@/Pages/Forms/components/FormsHeaderActions.vue";
import Form from "@/Modules/domain/Form";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import SuspendFormBtn from "@/Pages/Forms/components/SuspendFormBtn.vue";
import Participant from "@/Modules/domain/Participant";
import LoaderIcon from "@/Components/Icons/LoaderIcon.vue";
import TabNavigation from "@/Components/TabNavigation.vue";
import RequirementsManager from "@/Components/Forms/RequirementsManager.vue";
import FormStyleDesigner from "@/Pages/Forms/components/FormStyleDesigner.vue";
import FormUpdateDashboard from "@/Pages/Forms/components/FormUpdateDashboard.vue";
import EventCertificates from "@/Pages/Forms/components/EventCertificates.vue";
import GuestCard from "@/Pages/Forms/components/GuestCard.vue";

export default {
    name: "FormUpdate",
    computed: {
        Participant() {
            return Participant;
        },
        styleTokensError() {
            if (!this.form?.errors) {
                return null;
            }

            const entry = Object.entries(this.form.errors).find(([key]) => key.startsWith('style_tokens'));
            return entry ? entry[1] : null;
        },
    },
    components: {
        FormStyleDesigner,
        FormUpdateDashboard,
        EventCertificates,
        RequirementsManager,
        LoaderIcon,
        SuspendFormBtn,
        FormsHeaderActions,
        Link,
        ListOfForms,
        GuestCard,
    },
    mixins: [ApiMixin],
    data() {
        return {
            activeTab: "dashboard",
        };
    },
    async beforeMount() {
        this.model = new Form();
        this.setFormAction("update");
        this.setRequirements();
    },
    methods: {
        async submitProxyUpdate() {
            // Log the data being submitted
            const formData = this.form.data();
            console.log('Form data before submit:', formData);
            console.log('Requirements:', formData.requirements);
            await this.submitUpdate();
            this.setRequirements();
        },
        setRequirements() {
            if (!this.form.requirements) {
                this.form.requirements = this.$page.props?.data?.requirements || [];
            }
        },
    },
};
</script>

<template>
    <AppLayout title="Update Attendance Form">
        <template #header>
            <forms-header-actions />
        </template>
        <div class="mx-auto flex flex-col gap-5 sm:px-6 lg:px-8">
            <TabNavigation
                v-model="activeTab"
                :tabs="[
                    { key: 'dashboard', label: 'Summary' },
                    { key: 'update', label: 'Update Form' },
                    { key: 'certificates', label: 'eCertificates' },
                ]"
            >
                <template #default="{ activeKey }">
                    <div v-if="activeKey === 'update'" class="mt-4 flex justify-center gap-5">
                        <div>
                            <span class="font-semibold text-gray-700 text-sm">Event Details</span>
                            <form v-if="!!form" @submit.prevent="submitProxyUpdate" class="max-w-3xl min-w-xl w-full mx-auto">
                                <div class="w-full flex flex-col gap-6">
                                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg" :class="{ 'border border-red-600': form.hasErrors }">
                                        <div class="border p-2 rounded-md flex flex-col gap-2 bg-gray-100">
                                            <div
                                                class="flex flex-row w-full gap-3 bg-gray-200 p-2 rounded-md justify-between shadow py-4"
                                            >
                                                <div class="flex flex-col justify-center gap-1 w-full">
                                                    <text-input
                                                        placeholder="Title"
                                                        v-model="form.title"
                                                        :error="form.errors.title"
                                                    />
                                                    <text-area
                                                        placeholder="Form Description"
                                                        v-model="form.description"
                                                        :error="form.errors.description"
                                                        class="text-xs"
                                                    />
                                                </div>
                                                <div class="flex flex-col items-center justify-center">
                                                    <label class="text-3xl leading-none font-[1000]">
                                                        {{ form.event_id }}
                                                    </label>
                                                    <span class="text-[0.6rem] leading-none ">Event ID</span>
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
                                                    <text-input
                                                        placeholder="Venue"
                                                        v-model="form.venue"
                                                        :error="form.errors.venue"
                                                    />
                                                </div>
                                                <div>
                                                    <span class="font-bold uppercase">Other details: </span>
                                                    <text-area
                                                        placeholder="Other details"
                                                        v-model="form.details"
                                                        class="w-full text-xs"
                                                        :error="form.errors.details"
                                                    />
                                                </div>
                                            </div>
                                            <div class="px-1">
                                                <requirements-manager v-model="form.requirements" :error="form.errors.requirements"/>
                                            </div> 
                                            <div class="flex flex-col p-2">
                                                <div class="flex gap-1 justify-between">
                                                    <suspend-form-btn :data="form" />
                                                    <button
                                                        class="bg-blue-200 text-blue-900 w-fit px-4 py-2 rounded flex items-center gap-1"
                                                        title="Temporarily stop accepting responses"
                                                    >
                                                        <loader-icon v-if="model.api.processing" />
                                                        <span v-if="model.api.processing"> Updating </span>
                                                        <span v-else> Update </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="flex gap-5">
                            <form-style-designer v-model="form.style_tokens" :error="styleTokensError" />
                            <div class="flex flex-col w-fit">
                                <div class="flex justify-between text-sm">
                                    <span class="font-semibold text-gray-700">Preview</span>
                                    <a class="font-semibold text-gray-700 mr-1" title="Visit Form" :href="route('forms.guest.index', form.event_id)" target="_blank">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-auto h-5" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M6.364 13.5a.5.5 0 0 0 .5.5H13.5a1.5 1.5 0 0 0 1.5-1.5v-10A1.5 1.5 0 0 0 13.5 1h-10A1.5 1.5 0 0 0 2 2.5v6.636a.5.5 0 1 0 1 0V2.5a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v10a.5.5 0 0 1-.5.5H6.864a.5.5 0 0 0-.5.5"/>
                                            <path fill-rule="evenodd" d="M11 5.5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793l-8.147 8.146a.5.5 0 0 0 .708.708L10 6.707V10.5a.5.5 0 0 0 1 0z"/>
                                        </svg>
                                    </a>
                                </div>
                                <guest-card :data="form" class="bg-white drop-shadow-lg"/>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else-if="activeKey === 'dashboard'" class="mt-4">
                        <form-update-dashboard
                            :stats="$page.props.eventStats"
                            :responses-by-type="$page.props.eventResponsesByType"
                        />
                    </div>

                    <div v-else-if="activeKey === 'certificates'" class="mt-4">
                        <event-certificates :event-id="data?.event_id" :template="$page.props.certificateTemplate" />
                    </div>
                </template>
            </TabNavigation>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
