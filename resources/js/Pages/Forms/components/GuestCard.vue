<script>
import PreregistrationCard from "@/Pages/Forms/components/PreregistrationCard.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import RegistrationCard from "@/Pages/Forms/components/RegistrationCard.vue";
import FeedbackCard from "@/Pages/Forms/components/FeedbackCard.vue";
import TabNavigation from "@/Components/TabNavigation.vue";

export default {
    name: "GuestCard",
    components: {TabNavigation, FeedbackCard, RegistrationCard, InputError, InputLabel, TextInput, PreregistrationCard},
    mixins: [DataFormatterMixin],
    data() {
        return {
            // v-model on <TabNavigation> expects a reactive property.
            // Define it so template render won't warn about undefined property.
            activeTab: null,
            // referenced in beforeDestroy when clearing the interval
            intervalId: null,
        };
    },
    mounted() {
        this.startCountdown();
        // Set default active tab to first available open form to avoid null in TabNavigation
        if (this.activeTab === null) {
            if (this.isFormOpen(this.whatForm('pre_registration'))) {
                this.activeTab = 'preregistration';
            } else if (this.isFormOpen(this.whatForm('registration'))) {
                this.activeTab = 'registration';
            } else if (this.isFormOpen(this.whatForm('feedback'))) {
                this.activeTab = 'feedback';
            }
        }
    },
    beforeDestroy() {
        clearInterval(this.intervalId);
    },
    methods: {
        whatForm(formType) {
            if (this.data.requirements.length <= 0) return null;
            return this.data.requirements.find((requirement) => requirement.form_type === formType) || null;
        },
        isFormOpen(form) {
            if (!form || !form.config) {
                return false;
            }
            const { open_from, open_to } = form.config;
            if (!open_from || !open_to) {
                return false;
            }
            const now = new Date();
            const openFrom = new Date(open_from);
            const openTo = new Date(open_to);
            if (isNaN(openFrom.getTime()) || isNaN(openTo.getTime())) {
                return false;
            }
            return now >= openFrom && now <= openTo;
        },
    }
}
</script>

<template>
    <div v-if="!!data" class="border p-2 rounded-md flex flex-col gap-2 bg-gray-100 max-w-xl drop-shadow-lg mx-4 lg:mx-0 my-4 md:mt-0">
        <div class="flex flex-row bg-AB gap-2 text-white p-2 rounded-md justify-between shadow py-4">
            <div class="flex flex-col justify-center drop-shadow">
                <label class="leading-tight font-semibold text-2xl">{{ data.title }}</label>
                <p class="text-xs leading-snug break-all">
                    {{ data.description }}
                </p>
            </div>
            <div class="flex flex-col items-center justify-center border-l pl-2">
                <label class="text-xl leading-none font-[1000]">{{ data.event_id }}</label>
                <span class="text-[0.6rem] leading-none select-none">Form ID</span>
            </div>
        </div>

        <div class="flex flex-col items-center justify-center p-2 rounded-md select-none drop-shadow">
            <span v-if="isExpired" class="text-sm uppercase leading-none text-red-600">Event has ended</span>
            <span v-else class="text-sm uppercase leading-none">Event starts in </span>
            <label class="leading-none font-bold text-4xl" :class="{'text-red-600' : isExpired}">{{ countdownDisplay }}</label>
        </div>

        <div class="flex relative items-center drop-shadow">
            <div class="bg-AA text-center py-3 text-white rounded-md flex flex-col leading-none w-full">
                <label class="font-bold">{{ formatTime(data.time_from) }} {{ formatDate(data.date_from) }}</label>
                <span class="text-xs">Start</span>
            </div>
            <div class="flex w-full bg-AB max-w-[2rem]">
                <label class="m-auto text-white font-bold">TO</label>
            </div>
            <div class="bg-AA text-center py-3 text-white rounded-md flex flex-col leading-none w-full">
                <label class="font-bold">{{ formatTime(data.time_to) }} {{ formatDate(data.date_to) }}</label>
                <span class="text-xs">End</span>
            </div>
        </div>

        <div  class="px-1">
            <div v-if="data.venue">
                <span class="font-bold uppercase">Venue: </span>
                <label class="break-all overflow-ellipsis">{{ data.venue }}</label>
            </div>
            <p v-if="data.venue" class="text-justify break-all">{{ data.details }}</p>
        </div>
        <div v-if="data.max_slots" class="px-1 flex gap-2 justify-between">
            <div>
                <span class="font-bold uppercase">Max Slots: </span>
                <label>{{ data.max_slots }}</label>
            </div>
            <div>
                <span class="font-bold uppercase">Slots Available: </span>
                <span v-if="data.participants_count >= data.max_slots" class="text-red-600">FULL</span> <label v-else >{{ data.max_slots-data.participants_count}}</label>
            </div>
        </div>
        <div v-show="data.is_suspended" v-if="data.is_suspended" class="flex flex-col border-t p-2 bg-yellow-300 w-full min-w-full rounded-md min-h-[3rem]">
            <span class="font-bold uppercase leading-none text-center">This Form is suspended</span>
            <span class="leading-none text-xs text-center">unable to accept registration</span>
        </div>
        <div v-show="isExpired" v-else-if="isExpired" class="flex flex-col border-t p-2 bg-yellow-300 w-full min-w-full rounded-md min-h-[3rem]">
            <span class="font-bold uppercase leading-none text-center">This Form has expired</span>
            <span class="leading-none text-xs text-center">unable to accept registration</span>
        </div>
        <div v-else-if="data.participants_count >= data.max_slots" class="text-center bg-orange-500 text-white py-5 rounded-b">
            Maximum Number of Registrations Reached!
        </div>
        <div v-else class="flex flex-col gap-1">
            <TabNavigation
                v-model="activeTab"
                :tabs="[
                    isFormOpen(whatForm('pre_registration')) ? { key: 'preregistration', label: 'Pre-Registration' } : null,
                    isFormOpen(whatForm('registration')) ? { key: 'registration', label: 'Registration' } : null,
                    isFormOpen(whatForm('feedback')) ? { key: 'feedback', label: 'Feedback' } : null,
                ].filter(Boolean)"
            >
                <template #default="{ activeKey }">
                    <div v-if="activeKey === 'preregistration'">
                        <!-- Pre-registration -->
                        <preregistration-card
                            v-if="isFormOpen(whatForm('pre_registration'))"
                            :event-id="data.event_id"
                            :config="whatForm('pre_registration')"
                            @createdModel="$emit('createdModel', $event)"
                        />
                        <h3 v-else-if="whatForm('pre_registration') && whatForm('pre_registration').config && isFormOpen(whatForm('pre_registration'))" class="bg-AB text-white p-3 rounded-md shadow leading-none">
                            Pre-registration will open on
                            <b>{{ formatDateTime(whatForm('pre_registration').config.open_from) }}</b>
                        </h3>
                    </div>

                    <div v-if="activeKey === 'registration'">
                        <!-- Registration -->
                        <registration-card
                            v-if="isFormOpen(whatForm('registration'))"
                            :event-id="data.event_id"
                            :config="whatForm('registration')"
                            @createdModel="$emit('createdModel', $event)"
                        />
                        <h3 v-else-if="whatForm('registration') && whatForm('registration').config && isFormOpen(whatForm('registration'))" class="bg-AB text-white p-3 rounded-md shadow leading-none">
                            Registration will open on
                            <b>{{ formatDateTime(whatForm('registration').config.open_from) }}</b>
                        </h3>
                    </div>

                    <div v-if="activeKey === 'feedback'">
                        <!-- Feedback / Evaluation -->
                        <feedback-card
                            v-if="isFormOpen(whatForm('feedback'))"
                            :event-id="data.event_id"
                            :config="whatForm('feedback')"
                            @createdModel="$emit('createdModel', $event)"
                        />
                        <h3 v-else-if="whatForm('feedback') && whatForm('feedback').config && isFormOpen(whatForm('feedback'))" class="bg-AB text-white p-3 rounded-md shadow leading-none">
                            Evaluation Form will be open on
                            <b>{{ formatDateTime(whatForm('feedback').config.open_from) }}</b>
                        </h3>
                    </div>
                </template>
            </TabNavigation>

        </div>

    </div>
</template>

<style scoped>

</style>
