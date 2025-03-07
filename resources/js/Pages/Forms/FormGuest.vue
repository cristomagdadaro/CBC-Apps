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
            model: null,
        }
    },
    methods: {
        async searchEvent() {
            this.model = new Form();
            this.eventFormFromApi = await this.model.getIndex(this.form.data());
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
        }
    },
    watch: {
        eventId: {
            handler(newVal, oldVal) {
                this.form.search = Object.values(newVal).join('');
            },
            deep: true,
        }
    }
}
</script>

<template>
    <Head title="DA-CBC Forms" />

    <div class="relative sm:flex flex-col gap-5 sm:justify-center sm:items-center min-h-screen">
        <div class="border p-2 rounded-md flex flex-col gap-2 bg-gray-100 max-w-xl w-full drop-shadow-lg">
            <div class="flex flex-row bg-gray-200 p-2 rounded-md justify-between shadow py-4">
                <div class="flex flex-col justify-center">
                    <label class="leading-none font-semibold text-xl">Event ID</label>
                    <p class="text-xs leading-none">
                        For the event id, kindly check the invitation.
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
                            class="w-full text-center"
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
                            class="w-full text-center"
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
                            type="text"
                            class="w-full text-center"
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
                            class="w-full text-center"
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
        <transition-container>
            <guest-card v-show="!!eventForm" :data="eventForm" />
        </transition-container>
        <transition-container>
            <guest-card v-show="!!eventFormFromApi?.data.length" :data="eventFormFromApi?.data[0]" />
        </transition-container>
        <div v-if="!eventForm && !eventFormFromApi?.data.length">
            Event not found. Try a different event id or ask the organizer.
        </div>
    </div>

</template>

<style>
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>
