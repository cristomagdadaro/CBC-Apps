<script>
import {useForm} from "@inertiajs/vue3";
import Form from "@/Modules/domain/Form";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin";
import QrcodeVue from 'qrcode.vue';
import GuestCard from "@/Pages/Forms/components/GuestCard.vue";

export default {
    name: "FormGuest",
    mixins: [FormLocalMixin],
    components: {
        QrcodeVue,
        GuestCard,
    },
    props: {
        eventForm: { type: Object },
        quote: String,
        todayEvents: {
            type: Array,
            default: () => [],
        },
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
            showTodayPanel: false,
            showMobileTodayPanel: false,
            isSearching: false,
        }
    },
    methods: {
        async searchEvent(resetInputs = true) {
            if (!this.form.search || this.form.search.length < 4) return;

            this.isSearching = true;
            this.eventFormFromApi = null;

            try {
                this.eventFormFromApi = await this.model.api.getIndex(this.form.data(), this.model);
            } catch (error) {
                console.error('Failed to fetch event form:', error);
            } finally {
                if (resetInputs) {
                    this.eventId.cell1 = null;
                    this.eventId.cell2 = null;
                    this.eventId.cell3 = null;
                    this.eventId.cell4 = null;
                    this.$refs.cell1?.focus();
                }

                this.isSearching = false;
            }
        },
        async handleCreatedModel(payload) {
            this.lastCreatedForm = payload;

            const eventId = this.eventForm?.event_id ?? this.form?.search;
            if (!eventId) {
                return;
            }

            this.form.search = eventId;
            await this.searchEvent(false);
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
        async applyTodayEvent(eventId) {
            if (!eventId) return;
            this.populateEventId(eventId);
            this.eventFormFromApi = null;
            await this.$nextTick();
            await this.searchEvent(false);
            if (typeof window !== 'undefined' && window.innerWidth < 768) {
                this.closeMobileTodayPanel();
            }
        },
        populateEventId(value) {
            const digitsOnly = (value ?? '')
                .toString()
                .replace(/\D/g, '')
                .slice(0, 4);

            const cells = ['cell1', 'cell2', 'cell3', 'cell4'];
            cells.forEach((cell, index) => {
                this.eventId[cell] = digitsOnly[index] ?? '';
            });

            this.form.search = digitsOnly || null;
        },
        syncTodayPanelVisibility() {
            if (typeof window === 'undefined') return;
            const isDesktop = window.innerWidth >= 768;
            this.showTodayPanel = isDesktop;
            if (isDesktop) {
                this.showMobileTodayPanel = false;
            }
        },
        toggleMobileTodayPanel() {
            this.showMobileTodayPanel = !this.showMobileTodayPanel;
        },
        closeMobileTodayPanel() {
            this.showMobileTodayPanel = false;
        },
        formatEventDates(event) {
            if (!event?.date_from || !event?.date_to) {
                return 'Date TBD';
            }

            const from = new Date(event.date_from);
            const to = new Date(event.date_to);
            const formatter = new Intl.DateTimeFormat('en-US', {
                month: 'short',
                day: 'numeric',
            });

            const sameDay = from.toDateString() === to.toDateString();
            return sameDay
                ? formatter.format(from)
                : `${formatter.format(from)} – ${formatter.format(to)}`;
        },
        formatEventTimes(event) {
            if (!event?.time_from || !event?.time_to) {
                return 'Time TBD';
            }

            try {
                const formatter = new Intl.DateTimeFormat('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                });
                const base = '1970-01-01T';
                const from = new Date(`${base}${event.time_from}`);
                const to = new Date(`${base}${event.time_to}`);
                return `${formatter.format(from)} – ${formatter.format(to)}`;
            } catch (e) {
                return `${event.time_from} – ${event.time_to}`;
            }
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
        },
        resolvedEventForm() {
            const fromApi = this.eventFormFromApi?.data?.[0] || null;
            if (fromApi && this.eventForm?.event_id && fromApi.event_id === this.eventForm.event_id) {
                return fromApi;
            }

            return this.eventForm;
        }
    },
    mounted() {
        setTimeout(() => {
            this.delayReady = true;
        }, 200);
        this.populateEventId(this.form?.search);
        if (typeof window !== 'undefined') {
            this.syncTodayPanelVisibility();
            window.addEventListener('resize', this.syncTodayPanelVisibility);
        }
    },
    beforeUnmount() {
        if (typeof window !== 'undefined') {
            window.removeEventListener('resize', this.syncTodayPanelVisibility);
        }
    }
}
</script>

<template>
    <Head title="Event Form" />

    <!-- Mobile Events Modal - rendered at root level -->
    <teleport to="body">
        <transition name="fade">
            <div
                v-if="showMobileTodayPanel"
                class="md:hidden fixed inset-0 z-50 bg-black/60 flex"
                @click="closeMobileTodayPanel"
            >
                <div 
                    class="bg-white text-gray-800 rounded-3xl shadow-2xl w-11/12 max-w-md m-auto p-5 flex flex-col max-h-[85vh]" 
                    @click.stop
                >
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.4em] text-gray-400">Events</p>
                            <p class="text-lg font-semibold text-AB">{{ todayEvents.length }} scheduled</p>
                        </div>
                        <button type="button" class="text-gray-500 hover:text-gray-700" @click="closeMobileTodayPanel">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                                <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="overflow-y-auto divide-y divide-gray-100 -mx-5 px-5 flex-1">
                        <button
                            v-for="event in todayEvents"
                            :key="`mobile-${event.event_id}`"
                            type="button"
                            class="w-full text-left py-3 hover:bg-AB/5"
                            @click="applyTodayEvent(event.event_id)"
                        >
                            <p class="font-semibold text-base text-AB leading-snug">{{ event.title || 'Untitled Event' }}</p>
                            <p class="text-xs text-gray-500 tracking-[0.3em] uppercase">{{ event.event_id }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ formatEventDates(event) }}</p>
                            <p class="text-xs text-gray-500">
                                {{ formatEventTimes(event) }}
                                <span v-if="event.venue"> • {{ event.venue }}</span>
                            </p>
                        </button>
                    </div>
                    <button type="button" class="mt-4 w-full py-2 rounded-xl border border-gray-200 text-sm" @click="closeMobileTodayPanel">
                        Close
                    </button>
                </div>
            </div>
        </transition>
    </teleport>

    <div
        v-if="showTodayPanel && todayEvents && todayEvents.length"
        class="hidden md:block fixed left-4 top-1/2 -translate-y-1/2 z-[1000] pointer-events-auto"
    >
        <div class="bg-white/95 text-gray-800 shadow-2xl rounded-2xl border border-AB/30 w-64 max-h-[80vh] flex flex-col backdrop-blur">
            <div class="px-4 py-3 border-b border-gray-100">
                <p class="text-lg font-bold uppercase text-gray-500">Events</p>
                <p class="text-xs text-gray-400">{{ todayEvents.length }} ongoing</p>
            </div>
            <div class="overflow-y-auto scroll-m-0 divide-y divide-gray-100">
                <button
                    v-for="event in todayEvents"
                    :key="event.event_id"
                    type="button"
                    @click="applyTodayEvent(event.event_id)"
                    class="w-full text-left px-4 py-3 hover:bg-AB/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-AB/40"
                >
                    <p class="font-semibold text-sm text-AB leading-tight">{{ event.title || 'Untitled Event' }}</p>
                    <p class="text-[0.65rem] text-gray-500 tracking-[0.2em] uppercase">{{ event.event_id }}</p>
                    <p class="text-xs text-gray-600 mt-1">{{ formatEventDates(event) }}</p>
                    <p class="text-[0.65rem] text-gray-500">
                        {{ formatEventTimes(event) }}
                    </p>
                </button>
            </div>
        </div>
    </div>
{{ $appEnv }}
    <button
        v-if="todayEvents && todayEvents.length"
        type="button"
        class="md:hidden fixed left-4 bottom-4 z-50 bg-AB text-white px-4 py-3 rounded-full shadow-lg shadow-AB/40 flex items-center gap-2 text-sm"
        @click="toggleMobileTodayPanel"
    >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2-event" viewBox="0 0 16 16">
            <path d="M11 7.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/>
            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1.5A1.5 1.5 0 0 1 16 2.5V14a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2.5A1.5 1.5 0 0 1 1.5 1H3V.5a.5.5 0 0 1 .5-.5M15 4H1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z"/>
        </svg>
        Events
    </button>

    <guest-form-page
        :title="'Event Forms'"
        :subtitle="'For the event id, kindly check the invitation or ask the organizers.'"
        :delay-ready="delayReady"
        :max-width="'max-w-2xl'">
        <template #search>
            <form v-if="!eventForm" class="flex gap-2 items-center pr-2 bg-white md:rounded-md"  @submit.prevent="searchEvent">
                <div class="flex flex-col w-full items-center gap-3">
                    <div class="flex flex-row w-full items-center justify-between py-2 pl-2">
                        <input
                            id="cell1"
                            ref="cell1"
                            v-model="eventId.cell1"
                            type="number"
                            class="text-center font-bold text-3xl md:text-5xl py-2 md:py-3 border-none rounded focus:ring-0 w-14 md:w-20 bg-gray-100 drop-shadow"
                            required
                            autofocus
                            @input="handleInput('cell1', $event)"
                            @keydown.backspace="handleBackspace('cell1', $event)"
                            maxlength="1"
                            pattern="[0-9]"
                            autocomplete="off"
                        />
                        <div class="border border-AB h-3 w-3 bg-AB flex rounded-full mx-1">&nbsp;</div>
                        <input
                            id="cell2"
                            ref="cell2"
                            v-model="eventId.cell2"
                            type="number"
                            class="text-center font-bold text-3xl md:text-5xl py-2 md:py-3 border-none rounded focus:ring-0 w-14 md:w-20 bg-gray-100 drop-shadow"
                            required
                            @input="handleInput('cell2', $event)"
                            @keydown.backspace="handleBackspace('cell2', $event)"
                            maxlength="1"
                            pattern="[0-9]"
                            autocomplete="off"
                        />
                        <div class="border border-AB h-3 w-3 bg-AB flex rounded-full mx-1">&nbsp;</div>
                        <input
                            id="cell3"
                            ref="cell3"
                            v-model="eventId.cell3"
                            type="number"
                            class="text-center font-bold text-3xl md:text-5xl py-2 md:py-3 border-none rounded focus:ring-0 w-14 md:w-20 bg-gray-100 drop-shadow"
                            required
                            @input="handleInput('cell3', $event)"
                            @keydown.backspace="handleBackspace('cell3', $event)"
                            maxlength="1"
                            pattern="[0-9]"
                            autocomplete="off"
                        />
                        <div class="border border-AB h-3 w-3 bg-AB flex rounded-full mx-1">&nbsp;</div>
                        <input
                            id="cell4"
                            ref="cell4"
                            v-model="eventId.cell4"
                            type="number"
                            class="text-center font-bold text-3xl md:text-5xl py-2 md:py-3 border-none rounded focus:ring-0 w-14 md:w-20 bg-gray-100 drop-shadow"
                            required
                            @input="handleInput('cell4', $event)"
                            @keydown.backspace="handleBackspace('cell4', $event)"
                            maxlength="1"
                            pattern="[0-9]"
                            autocomplete="off"
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.event" />
                </div>
                <search-btn type="submit" :disabled="isSearching" class="text-center">
                    <span v-if="!isSearching" class="md:block hidden">Search</span>
                    <span v-else class="md:block hidden">Searching</span>
                </search-btn>
            </form>
        </template>

        <!-- Middle content: recent QR codes, full QR modal, confirmation modal -->
        <transition-container type="slide-bottom" :duration="1000">
            <div v-show="delayReady"  v-if="recentQrCodes.length" class="md:absolute md:top-5 mt-3 md:left-full mx-4 lg:mx-5 p-3 bg-gray-100 md:rounded-md drop-shadow rounded-md">
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
            <div v-if="showFullQr" @click="closeFullQr" class="fixed inset-0 backdrop-blur-sm bg-black/30 flex items-center justify-center z-50 flex-col">
                <div @click="closeFullQr" class="absolute inset-0 cursor-pointer"></div>
                <span class="text-white text-sm py-2">Show this to the organizers for scanning. Thank you!</span>
                <span class="text-white text-sm py-2">{{ fullQrValue }}</span>
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
        <template v-if="!isSearching">
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
        
        <transition name="fade">
        <div
            v-if="isSearching"
            class="fixed inset-0 z-40 flex items-center justify-center pointer-events-none"
        >
            <div class="bg-white/90 text-AB px-4 py-3 rounded-2xl shadow-xl flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="animate-spin" viewBox="0 0 16 16">
                    <path d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 1 1 .908-.417 6 6 0 1 1-5.454-3.485.5.5 0 0 1 0 1Z" />
                </svg>
                <div class="text-sm font-semibold tracking-wide">Fetching event details…</div>
            </div>
        </div>
    </transition>

        <!-- Cards row -->
        <div class="flex gap-5 md:flex-row flex-col justify-center">
            <transition-container :duration="300" type="pop-in">
                <div v-if="eventFormFromApi?.data?.length">
                    <guest-card
                        :data="eventFormFromApi.data[0]"
                        @createdModel="handleCreatedModel"
                    />
                    <label class="text-[0.6rem] leading-none ">
                        Form from Search
                    </label>
                </div>
            </transition-container>

            <transition-container :duration="300" type="pop-in">
                <div v-if="resolvedEventForm && delayReady">
                    <guest-card
                        :data="resolvedEventForm"
                        @createdModel="handleCreatedModel"
                    />
                    <label class="text-[0.6rem] leading-none ">
                        Form from URL
                    </label>
                </div>
            </transition-container>
        </div>

    </guest-form-page>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
