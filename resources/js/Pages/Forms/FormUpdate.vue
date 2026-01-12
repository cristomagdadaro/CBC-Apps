<script>
import ListOfForms from "@/Pages/Forms/components/ListOfForms.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import AddButton from "@/Components/Buttons/AddButton.vue";
import { Link } from "@inertiajs/vue3";
import FormsHeaderActions from "@/Pages/Forms/components/FormsHeaderActions.vue";
import TextInput from "@/Components/TextInput.vue";
import TextArea from "@/Components/TextArea.vue";
import DateInput from "@/Components/DateInput.vue";
import TimeInput from "@/Components/TimeInput.vue";
import Form from "@/Modules/domain/Form";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import SuspendFormBtn from "@/Pages/Forms/components/SuspendFormBtn.vue";
import Participant from "@/Modules/domain/Participant";
import ListOfParticipants from "@/Pages/Forms/components/ListOfParticipants.vue";
import LoaderIcon from "@/Components/Icons/LoaderIcon.vue";
import TabNavigation from "@/Components/TabNavigation.vue";
import RequirementsManager from "@/Components/Forms/RequirementsManager.vue";

export default {
    name: "FormUpdate",
    computed: {
        Participant() {
            return Participant;
        },
    },
    components: {
        RequirementsManager,
        TabNavigation,
        LoaderIcon,
        ListOfParticipants,
        SuspendFormBtn,
        TimeInput,
        DateInput,
        TextArea,
        TextInput,
        FormsHeaderActions,
        Link,
        AddButton,
        AppLayout,
        ListOfForms,
    },
    mixins: [ApiMixin],
    data() {
        return {
            activeTab: "update",
        };
    },
    async beforeMount() {
        this.model = new Form();
        this.setFormAction("update");
        this.setRequirements();
    },
    methods: {
        async submitProxyUpdate() {
            this.form.requirements = this.form.requirements || [];
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
                    { key: 'update', label: 'Update Form' },
                    { key: 'participants', label: `Responses ${ $page.props.responsesCount }` },
                ]"
            >
                <template #default="{ activeKey }">
                    <div v-if="activeKey === 'update'" class="mt-4">
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
                                                <span class="text-[0.6rem] leading-none select-none">Event ID</span>
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
                                            <div class="px-1 flex flex-col gap-1">
                                                <div>
                                                    <span class="font-bold uppercase">Max. no. of participants: </span>
                                                    <text-input
                                                        type="number"
                                                        placeholder="optional"
                                                        v-model="form.max_slots"
                                                        :error="form.errors.max_slots"
                                                    />
                                                </div>
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

                    <!-- Participants tab -->
                    <div v-else-if="activeKey === 'participants'" class="mt-4">
                        <list-of-participants :event-id="data?.event_id" />
                    </div>
                </template>
            </TabNavigation>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
