<script>
import {Head, Link, useForm} from "@inertiajs/vue3";
import GuestCard from "@/Pages/Forms/components/GuestCard.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Form from "@/Modules/domain/Form";
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";
import SearchBtn from "@/Components/Buttons/SearchBtn.vue";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin";
import QrcodeVue from 'qrcode.vue';
import DeleteBtn from "@/Components/Buttons/DeleteBtn.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import CancelBtn from "@/Components/Buttons/CancelBtn.vue";
import GuestFormPage from "@/Pages/Shared/GuestFormPage.vue";

export default {
    name: "FormGuest",
    mixins: [FormLocalMixin],
    components: {
        GuestFormPage,
        CancelBtn,
        ConfirmationModal,
        DeleteBtn,
        SearchBtn, TransitionContainer, PrimaryButton, InputError, InputLabel, TextInput, GuestCard, Head, Link, QrcodeVue},
    props: {
        eventForm: { type: Object },
        quote: String,
    },
    data() {
        return {
            form: useForm({
                search: null,
                filter: 'event_id',
            }),
            eventId: {
                cell1: null,
                cell2: null,
                cell3: null,
                cell4: null,
            },
            eventFormFromApi: null,
            model: new Form(),
            delayReady: false,
            showConfirmationModel: false,
            showFullQr: false,
            fullQrValue: '',
        }
    },
    methods: {
        async searchEvent() {
            if (!this.form.search || this.form.search.length < 4) return;
            this.eventFormFromApi = null;
            this.eventFormFromApi = await this.model.api.getIndex(this.form.data(), this.model);

            this.eventId.cell1 = null;
            this.eventId.cell2 = null;
            this.eventId.cell3 = null;
            this.eventId.cell4 = null;
            this.$refs.cell1.focus();
        },
        getLastDigit(value) {
            return /^[0-9]$/.test(value) ? value : '';
        },
        handleInput(field, event) {
            const value = event.target.value.slice(-1);
            this.eventId[field] = this.getLastDigit(value);

            const index = parseInt(field.replace('cell', ''), 10);

            if (this.eventId[field] && this.$refs[`cell${index + 1}`]) {
                this.$refs[`cell${index + 1}`].focus();
            }

            this.form.search = Object.values(this.eventId).join('');
        },
        handleBackspace(field, event) {
            const index = parseInt(field.replace('cell', ''), 10);

            if (!this.eventId[field] && event.key === 'Backspace' && this.$refs[`cell${index - 1}`]) {
                this.$refs[`cell${index - 1}`].focus();
            }

            this.eventFormFromApi = null;
        },
        confirmLocalSaveDelete(){
            this.showConfirmationModel = true;
        },
        openFullQr(value) {
            this.fullQrValue = value;
            this.showFullQr = true;
        },
        closeFullQr() {
            this.showFullQr = false;
            this.fullQrValue = '';
        },
    },
    watch: {
        eventId: {
            handler(newVal, oldVal) {
                this.form.search = Object.values(newVal).join('');
            },
            deep: true,
        }
    },
    computed: {
        formattedQuote() {
            return this.quote
                .replace('<options=bold>', '<strong class="text-AB drop-shadow flex flex-col text-center">')
                .replace('</options>', '</strong>')
                .replace('<fg=gray>', '<span class="text-AB font-light">')
                .replace('</fg>', '</span>');
        },
        innerSize() {
            const isLandscape = window.innerWidth > window.innerHeight;
            return isLandscape ? window.innerHeight : window.innerWidth;
        }
    },
    mounted() {
        setTimeout(() => {
            this.delayReady = true;
        }, 200);
    }
}
</script>

<template>
    <Head title="Event Form" />

    <guest-form-page
        :title="'Event Forms'"
        :subtitle="'For the event id, kindly check the invitation or ask the organizers.'"
        :delay-ready="delayReady"
    >
        <template #search>
            <form class="flex gap-2 items-center"  @submit.prevent="searchEvent">
                <div class="flex flex-col">
                    <div class="grid grid-cols-4 gap-0.5 items-center">
                        <TextInput
                            id="cell1"
                            ref="cell1"
                            v-model="eventId.cell1"
                            type="number"
                            classes="text-center font-bold text-3xl"
                            required
                            autofocus
                            @input="handleInput('cell1', $event)"
                            @keydown.backspace="handleBackspace('cell1', $event)"
                            maxlength="1"
                            pattern="[0-9]"
                            autocomplete="event"
                        />
                        <TextInput
                            id="cell2"
                            ref="cell2"
                            v-model="eventId.cell2"
                            type="number"
                            classes="text-center font-bold text-3xl"
                            required
                            @input="handleInput('cell2', $event)"
                            @keydown.backspace="handleBackspace('cell2', $event)"
                            maxlength="1"
                            pattern="[0-9]"
                            autocomplete="event"
                        />
                        <TextInput
                            id="cell3"
                            ref="cell3"
                            v-model="eventId.cell3"
                            type="number"
                            classes="text-center font-bold text-3xl"
                            required
                            @input="handleInput('cell3', $event)"
                            @keydown.backspace="handleBackspace('cell3', $event)"
                            maxlength="1"
                            pattern="[0-9]"
                            autocomplete="event"
                        />
                        <TextInput
                            id="cell4"
                            ref="cell4"
                            v-model="eventId.cell4"
                            type="number"
                            classes="text-center font-bold text-3xl"
                            required
                            @input="handleInput('cell4', $event)"
                            @keydown.backspace="handleBackspace('cell4', $event)"
                            maxlength="1"
                            pattern="[0-9]"
                            autocomplete="event"
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.event" />
                </div>
                <search-btn type="submit" :disabled="model?.processing" class="w-[10rem] text-center">
                    <span v-if="!model?.processing">Search</span>
                    <span v-else>Searching</span>
                </search-btn>
            </form>
        </template>

        <!-- Middle content: recent QR codes, full QR modal, confirmation modal -->
        <transition-container type="slide-bottom" :duration="1000">
            <div v-show="delayReady"  v-if="recentQrCodes.length" class="md:absolute md:top-0 md:left-full md:mx-5 p-3  bg-gray-100 md:rounded-md drop-shadow">
                <!-- ...existing recent QR code content... -->
                <h3 class="text-normal whitespace-nowrap text-center drop-shadow md:flex md:flex-col leading-none md:mb-2 mb-1"><span>Recent</span> <span class="md:text-xs">(max 6)</span></h3>
                <div class="flex md:flex-col flex-row gap-2  bg-gray-100 justify-between">
                    <div class="flex md:flex-col flex-row gap-2  bg-gray-100">
                        <button
                            v-for="item in recentQrCodes"
                            :key="item.participant_hash"
                            @click="openFullQr(item.participant_hash)"
                            class="text-center leading-none"
                        >
                            <qrcode-vue
                                v-if="item.participant_hash"
                                :value="item.participant_hash"
                                :size="50"
                                level="H"
                                render-as="canvas"
                                class="mx-auto my-1 active:scale-90"
                                ref="qrcodeCanvas"
                            />
                            <span>{{ item.event_id }}</span>
                        </button>
                    </div>
                    <delete-btn @click="showConfirmationModel = true" title="clear locally stored data">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                            <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                        </svg>
                    </delete-btn>
                </div>
            </div>
        </transition-container>

        <transition-container type="slide-top" :duration="500">
            <div v-if="showFullQr" @click="closeFullQr" class="fixed inset-0 backdrop-blur-sm bg-black/30 flex items-center justify-center z-50 flex flex-col">
                <div @click="closeFullQr" class="absolute inset-0 cursor-pointer"></div>
                <span class="text-white text-sm py-2">Show this to the organizers for scanning. Thank you!</span>
                <div @click="closeFullQr" class="z-50 p-4 bg-white rounded shadow-xl max-w-full max-h-full flex flex-col items-center justify-center">
                    <qrcode-vue
                        :value="fullQrValue"
                        :size="innerSize * 0.9"
                        level="H"
                        render-as="canvas"
                    />
                </div>
            </div>
        </transition-container>

        <confirmation-modal :show="showConfirmationModel" @close="showConfirmationModel = false">
            <template v-slot:title>Clear Recent Registration QR Codes</template>
            <template v-slot:content>
                This will remove the locally saved registration data in this device.
            </template>
            <template v-slot:footer>
                <div class="flex justify-between w-full">
                    <delete-btn @click="clearLocalHashedIds(null); showConfirmationModel = false;">
                        Confirm
                    </delete-btn>
                    <cancel-btn @click="showConfirmationModel = false">
                        Cancel
                    </cancel-btn>
                </div>
            </template>
        </confirmation-modal>

        <!-- Bottom descriptive / feedback text -->
        <template v-if="!model?.processing">
            <div v-if="!form.search && !eventForm && !eventFormFromApi?.data?.length">
                <blockquote v-html="formattedQuote" class="mt-3"></blockquote>
            </div>
            <div v-else-if="form.search && !eventForm && !eventFormFromApi?.data?.length">
                Use the search box to look for a form
            </div>
            <div v-else-if="form.search && !eventFormFromApi?.data?.length">
                Form not found. Try a different event id or ask the organizer.
            </div>
        </template>
        <div v-else class="flex items-center justify-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search animate-ping" viewBox="0 0 16 16">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
            </svg>
            <span class="animate-pulse">Searching</span>
        </div>

        <!-- Cards row -->
        <div class="flex gap-5 md:flex-row flex-col">
            <transition-container :duration="1000" type="slide-bottom">
                <div v-show="!!eventFormFromApi?.data?.length" v-if="eventFormFromApi" >
                    <guest-card :data="eventFormFromApi?.data[0]" @createdModel="lastCreatedForm = $event" />
                    <label class="text-[0.6rem] leading-none select-none">Form from Search</label>
                </div>
            </transition-container>
            <transition-container :duration="1000" type="slide-bottom">
                <div v-show="!!eventForm && delayReady" v-if="eventForm">
                    <guest-card :data="eventForm" @createdModel="lastCreatedForm = $event" />
                    <label class="text-[0.6rem] leading-none select-none">Form from URL</label>
                </div>
            </transition-container>
        </div>
    </guest-form-page>
</template>
