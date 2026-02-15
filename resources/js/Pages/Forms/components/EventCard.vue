<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Form from "@/Modules/domain/Form.js";
import SuspendFormBtn from "@/Pages/Forms/components/SuspendFormBtn.vue";
import DtoResponse from "@/Modules/dto/DtoResponse";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import Participant from "@/Modules/domain/Participant";
import RequirementsManager from "@/Components/Forms/RequirementsManager.vue";

export default {
    name: "EventCard",

    components: {
        RequirementsManager,
        SuspendFormBtn,
    },

    mixins: [ApiMixin, DataFormatterMixin],

    data() {
        return {
            confirmDelete: false,
            updatedData: null,
            errors: null,
        };
    },

    computed: {
        Form() {
            return Form;
        },
        formsData() {
            if (this.updatedData instanceof DtoResponse) {
                return this.updatedData.data;
            }
            return this.data ?? null;
        },
        formTypeLabels() {
            return {
                pre_registration: "Pre-registration",
                pre_registration_biotech: "Pre-registration + Quiz Bee",
                pre_registration_quizbee: "Pre-registration Quiz Bee",
                preregistration_quizbee: "Pre-registration Quiz Bee",
                registration: "Registration",
                pre_test: "Pre-test",
                post_test: "Post-test",
                feedback: "Feedback",
            };
        },
        requirementByType() {
            if (!Array.isArray(this.formsData?.requirements)) {
                return {};
            }

            return this.formsData.requirements.reduce((acc, req) => {
                acc[req.form_type] = req;
                return acc;
            }, {});
        },
        responseCountByType() {
            const result = {
                registration: 0,
                feedback: 0,
                preregistration: 0,
                preregistration_biotech: 0,
                preregistration_quizbee: 0,
                pretest: 0,
                posttest: 0,
            };

            if (!Array.isArray(this.formsData?.requirements)) {
                return result;
            }

            this.formsData.requirements.forEach(req => {
                if (!req?.form_type) return;

                switch (req.form_type) {
                    case "registration":
                        result.registration = req.responses_count ?? 0;
                        break;
                    case "feedback":
                        result.feedback = req.responses_count ?? 0;
                        break;
                    case "preregistration":
                        result.preregistration = req.responses_count ?? 0;
                        break;
                    case "preregistration_biotech":
                        result.preregistration_biotech = req.responses_count ?? 0;
                        break;
                    case "preregistration_quizbee":
                        result.preregistration_quizbee = req.responses_count ?? 0;
                        break;
                    case "pre_test":
                        result.pretest = req.responses_count ?? 0;
                        break;
                    case "post_test":
                        result.posttest = req.responses_count ?? 0;
                        break;
                }
            });

            return result;
        },
        responsesByType() {
            if (!this.formsData?.responses_by_type) {
                return [];
            }

            return Object.entries(this.formsData.responses_by_type).map(
                ([key, count]) => ({
                    form_type: key,
                    label: this.formTypeLabels[key] || key.replace(/_/g, " "),
                    count: count ?? 0,
                })
            );
        },
        visibleResponseTypes() {
            return [
                { key: 'registration', label: 'Registrations' },
                { key: 'feedback', label: 'Feedback' },
                { key: 'preregistration', label: 'Pre-registration' },
                { key: 'preregistration_biotech', label: 'Pre-registration + Quiz Bee' },
                { key: 'preregistration_quizbee', label: 'Pre-registration Quiz Bee' },
                { key: 'pretest', label: 'Pre-test' },
                { key: 'posttest', label: 'Post-test' },
            ].filter(item => this.responseCountByType[item.key] > 0);
        },
        hasRightBorder() {
            return (index) => index < this.visibleResponseTypes.length - 1;
        },
    },
    beforeMount() {
        this.model = new Form();
        this.startCountdown();
    },
    methods: {
        safeFormatDate(value) {
            return value ? this.formatDate(value) : "-";
        },
        safeFormatTime(value) {
            return value ? this.formatTime(value) : "-";
        },
        confirmAction() {
            this.confirmDelete = true;
        },
        async handleDelete() {
            this.toDelete = { event_id: this.formsData?.event_id };
            const response = await this.submitDelete();

            if (response instanceof DtoResponse) {
                this.confirmDelete = false;
                this.$emit("deletedModel", response.data);
            }
        },
        async handleExport(eventId, filename) {
            if (!eventId) return;

            this.model = new Participant();
            this.setFormAction("get");

            this.form.filter = "event_id";
            this.form.search = eventId;
            this.form.is_exact = true;

            const response = await this.fetchData();
            await this.exportCSV(response.data, filename);

            this.model = new Form();
        },
    },
};
</script>


<template>
    <div class="flex flex-col gap-2 justify-between mx-auto bg-gray-100 border rounded-md min-w-xl max-w-xl">
        <div v-if="formsData" class="p-2 flex flex-col gap-2 lg:max-w-2xl w-full ">
            <div class="flex flex-row bg-gray-200 p-2 rounded-md justify-between shadow py-4 gap-2">
                <div class="flex flex-col min-h-[3rem] gap-1">
                    <label class="leading-none font-bold">{{ formsData.title }}</label>
                    <p class="leading-snug line-clamp-2 text-sm">
                        {{ formsData.description }}
                    </p>
                </div>
                <div class="flex flex-col items-start md:items-center justify-center">
                    <label class="text-xl md:text-4xl leading-none font-[1000]">{{ formsData.event_id }}</label>
                    <span class="text-[0.65rem] leading-none ">Event ID</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 px-1 text-sm">
                <div>
                    <span class="font-semibold uppercase">Start Date: </span>
                    <label>{{ formatDate(formsData.date_from) }}</label>
                </div>
                <div>
                    <span class="font-semibold uppercase">End Date: </span>
                    <label>{{ formatDate(formsData.date_to) }}</label>
                </div>
                <div>
                    <span class="font-semibold uppercase">Start Time: </span>
                    <label>{{ formatTime(formsData.time_from) }}</label>
                </div>
                <div>
                    <span class="font-semibold uppercase">End Time: </span>
                    <label>{{ formatTime(formsData.time_to) }}</label>
                </div>
            </div>
            <div class="px-1 min-h-[4rem] hidden">
                <div class="line-clamp-1">
                    <span class="font-bold uppercase">Venue: </span>
                    <label class="leading-snug break-all">{{ formsData.venue }}</label>
                </div>
                <p class="text-sm leading-none line-clamp-3 break-all">{{ formsData.details }}</p>
            </div>
            <div class="flex flex-col border-t bg-gray-200 rounded-md">
                <div v-if="isExpired" class="px-1 flex w-full">
                    <div v-show="isExpired" class="relative w-full min-w-full">
                        <div class="flex flex-col border-t p-2 bg-gray-600 w-full text-white" >
                            <span class="font-bold uppercase leading-none text-center">This Form is expired</span>
                            <span class="leading-none text-xs text-center">adjust date or time to reopen</span>
                        </div>
                    </div>
                </div>
                <div v-else class="flex w-full">
                    <transition-container type="slide-right" :duration="1000">
                        <div v-show="formsData.requirements.length" v-if="formsData.requirements.length" class="hidden relative w-full min-w-full gap-2 flex flex-wrap">
                            <span class="font-bold uppercase">Requirements: </span>
                            <div v-for="data in formsData.requirements" v-bind:key="data.id" class="w-fit gap-2 flex items-center uppercase font-bold">
                                <input type="checkbox" class="rounded-full" :checked="true" disabled>
                                <label>{{ data.form_type }}</label>
                            </div>
                        </div>
                    </transition-container>
                    <transition-container type="slide-right" :duration="1000">
                        <div v-show="formsData.is_suspended" v-if="formsData.is_suspended" class="relative w-full min-w-full bg-yellow-300 rounded-md">
                            <div class="flex flex-col border-t p-2" >
                                <span class="font-bold uppercase leading-none text-center">This Form is suspended</span>
                                <span class="leading-none text-xs text-center">unable to accept request</span>
                            </div>
                        </div>
                        <span v-else-if="visibleResponseTypes.length" class="font-bold uppercase text-center pt-2 text-sm mx-auto">Statistics</span>
                        <span v-else class="font-bold uppercase text-center text-sm p-2 mx-auto">No Responses</span>
                    </transition-container>
                </div>
                <div class="flex gap-1 justify-center flex-wrap w-full overflow-x-auto">
                    <template v-for="(item, index) in visibleResponseTypes" :key="item.key">
                        <div
                            v-if="item.key === 'registration'"
                            :class="[
                                'flex flex-col items-center text-green-900 px-2 py-1 flex-shrink-0',
                                requirementByType.registration?.max_slots > 0 && responseCountByType.registration >= requirementByType.registration.max_slots ? 'text-red-600' : ''
                            ]"
                        >
                            <label class="text-lg md:text-xl font-[1000]">{{ responseCountByType.registration }}</label>
                            <span class="text-[0.55rem] md:text-[0.6rem] text-center">Registrations</span>
                        </div>
                        <div
                            v-else-if="item.key === 'feedback'"
                            class="flex flex-col items-center text-green-900 px-2 py-1 flex-shrink-0"
                        >
                            <label class="text-lg md:text-xl font-[1000]">{{ responseCountByType.feedback }}</label>
                            <span class="text-[0.55rem] md:text-[0.6rem] text-center">Feedback</span>
                        </div>
                        <div
                            v-else-if="item.key === 'preregistration'"
                            class="flex flex-col items-center text-green-900 px-2 py-1 flex-shrink-0"
                        >
                            <label class="text-lg md:text-xl font-[1000]">{{ responseCountByType.preregistration }}</label>
                            <span class="text-[0.55rem] md:text-[0.6rem] text-center">Pre-registration</span>
                        </div>
                        <div
                            v-else-if="item.key === 'preregistration_biotech'"
                            class="flex flex-col items-center text-green-900 px-2 py-1 flex-shrink-0"
                        >
                            <label class="text-lg md:text-xl font-[1000]">{{ responseCountByType.preregistration_biotech }}</label>
                            <span class="text-[0.5rem] md:text-[0.6rem] text-center line-clamp-2">Pre-reg + Quiz Bee</span>
                        </div>
                        <div
                            v-else-if="item.key === 'preregistration_quizbee'"
                            class="flex flex-col items-center text-green-900 px-2 py-1 flex-shrink-0"
                        >
                            <label class="text-lg md:text-xl font-[1000]">{{ responseCountByType.preregistration_quizbee }}</label>
                            <span class="text-[0.5rem] md:text-[0.6rem] text-center line-clamp-2">Pre-reg Quiz Bee</span>
                        </div>
                        <div
                            v-else-if="item.key === 'pretest'"
                            class="flex flex-col items-center text-green-900 px-2 py-1 flex-shrink-0"
                        >
                            <label class="text-lg md:text-xl font-[1000]">{{ responseCountByType.pretest }}</label>
                            <span class="text-[0.55rem] md:text-[0.6rem] text-center">Pre-test</span>
                        </div>
                        <div
                            v-else-if="item.key === 'posttest'"
                            class="flex flex-col items-center text-green-900 px-2 py-1 flex-shrink-0"
                        >
                            <label class="text-lg md:text-xl font-[1000]">{{ responseCountByType.posttest }}</label>
                            <span class="text-[0.55rem] md:text-[0.6rem] text-center">Post-test</span>
                        </div>
                    </template>
                </div>
                <div v-if="responsesByType?.length" class="mt-3 pt-3 border-t">
                    <div class="text-xs font-bold uppercase text-center mb-2">
                        Responses by Form Type
                    </div>

                    <div class="flex flex-wrap gap-2 justify-center w-full">
                        <div
                            v-for="item in responsesByType"
                            :key="item.form_type"
                            class="flex flex-col items-center bg-white rounded px-2 py-1 border border-gray-300 flex-shrink-0 min-w-max"
                        >
                            <label class="text-xs md:text-sm font-bold">{{ item.count }}</label>
                            <span class="text-[0.5rem] md:text-[0.6rem] text-center line-clamp-2">
                                {{ item.label }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex flex-col p-2">
                <div class="flex gap-1 justify-center flex-wrap">
                    <a :href="route('forms.guest.index', formsData.event_id)" target="_blank" class="bg-green-200 text-green-900 w-fit px-2 py-1 rounded flex items-center" title="Preview form">
                        <view-icon class="w-4 h-4" />
                    </a>

                    <Link :href="route('forms.scan', formsData.event_id)" class="bg-AA text-white w-fit px-2 py-1 rounded flex items-center" title="Modify details in the form">
                        <scan-icon class="w-4 h-4" />
                    </Link>

                    <Link :href="route('forms.update', formsData.event_id)" class="bg-blue-200 text-blue-900 w-fit px-2 py-1 rounded flex items-center" title="Modify details in the form">
                        <setting-icon class="w-4 h-4" />
                    </Link>

                    <button class="hidden bg-blue-600 text-blue-100 w-fit px-2 py-1 rounded" title="Manually register poarticipants">
                        Registration
                    </button>

                    <button @click.prevent="handleExport(formsData.event_id, `${formsData.title} (${formsData.event_id})`)" class="bg-cyan-200 text-cyan-900 w-fit px-2 py-1 rounded" title="Download form data in csv format">
                        <ExportIcon class="w-5 h-5" />
                    </button>

                    <suspend-form-btn v-if="!isExpired" :data="formsData" @updated="updatedData = $event" @failedUpdate="errors = $event"/>

                    <button @click="confirmAction"  class="bg-red-200 text-red-900 w-fit px-2 py-1 rounded" title="Permanently remove this form">
                        <DeleteIcon class="w-4 h-4" />
                    </button>
                </div>
                <label class="text-xs text-center text-red-600">{{errors?.toObject()?.message}}</label>
            </div>
            <delete-confirmation-modal
                :show="confirmDelete"
                :is-processing="model.api.processing"
                title="Confirm Delete Form"
                :message="`This will permanently delete the form. This action cannot be undone.`"
                :item-name="formsData.title"
                @confirm="handleDelete"
                @close="confirmDelete = false"
            />
    </div>
</template>

<style scoped>

</style>
