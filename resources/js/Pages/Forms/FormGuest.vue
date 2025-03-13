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

export default {
    name: "FormGuest",
    components: {SearchBtn, TransitionContainer, PrimaryButton, InputError, InputLabel, TextInput, GuestCard, Head, Link},
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
        }
    },
    methods: {
        async searchEvent() {
            if (!this.form.search || this.form.search.length < 4) return;
            this.eventFormFromApi = null;
            this.eventFormFromApi = await this.model.getIndex(this.form.data());

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
        }
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
    },
    mounted() {
        setTimeout(() => {
            this.delayReady = true;
        }, 200);
    }
}
</script>

<template>
    <Head title="DA-CBC Forms" />

    <div class="absolute top-0 left-0 w-full h-full z-[999] flex justify-center">
        <div class="relative sm:flex flex-col gap-5 sm:justify-center sm:items-center min-h-screen">
            <transition-container :duration="500" type="pop-out">
                <div v-show="delayReady" class="border select-none p-2 md:rounded-md flex flex-col gap-2 bg-gray-100 max-w-xl w-full drop-shadow-lg">
                    <div class="relative flex flex-row bg-AB text-white p-2 px-4 rounded-md gap-2 shadow py-4">
                        <img src="/imgs/logo.png" alt="logo" class="w-auto h-16" />
                        <div class="flex flex-col justify-center">
                            <label class="leading-none font-semibold text-xl">Event Forms</label>
                            <p class="text-sm leading-none">
                                For the event id, kindly check the invitation or ask the organizers.
                            </p>
                        </div>
                    </div>
                    <form class="flex gap-2 items-center"  @submit.prevent="searchEvent">
                        <div class="flex flex-col">
                            <div class="grid grid-cols-4 gap-0.5 items-center">
                                <TextInput
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
                </div>
            </transition-container>
            <template v-if="!model?.processing">
                <div v-if="!form.search && !eventForm && !eventFormFromApi?.data.length">
                    <blockquote v-html="formattedQuote" class="mt-3"></blockquote>
                </div>
                <div v-else-if="form.search && !eventForm && !eventFormFromApi?.data.length">
                    Use the search box to look for a form
                </div>
                <div v-else-if="form.search && !eventFormFromApi?.data.length">
                    Form not found. Try a different event id or ask the organizer.
                </div>
            </template>
            <div v-else class="flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search animate-ping" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                </svg>
                <span class="animate-pulse">Searching</span>
            </div>
            <div class="flex gap-5 md:flex-row flex-col">
                <transition-container :duration="1000" type="slide-bottom">
                    <div v-show="!!eventForm && delayReady" v-if="eventForm">
                        <guest-card :data="eventForm" />
                        <label class="text-[0.6rem] leading-none select-none">Form from URL</label>
                    </div>
                </transition-container>
                <transition-container :duration="1000" type="slide-bottom">
                    <div v-show="!!eventFormFromApi?.data.length" v-if="eventFormFromApi" >
                        <guest-card :data="eventFormFromApi?.data[0]" />
                        <label class="text-[0.6rem] leading-none select-none">Form from Search</label>
                    </div>
                </transition-container>
            </div>
        </div>
    </div>
    <div class="min-h-screen flex items-center justify-center text-white text-3xl font-bold relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-radial animate-gradient"></div>
    </div>
</template>

<style>
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    @keyframes rotateGradient {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    .bg-gradient-radial {
        background: radial-gradient(circle, #42C2FF, #EC8305);
        background-size: 200% 200%;
        position: absolute;
        width: 300%;
        height: 300%;
        min-width: 1700px;
        top: -100%;
        left: -70%;
        animation: rotateGradient 10s linear infinite;
    }

</style>
