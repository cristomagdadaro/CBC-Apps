<script>
import PreregistrationCard from "@/Pages/Forms/components/PreregistrationCard.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";

export default {
    name: "LabReqGuestCard",
    components: {InputError, InputLabel, TextInput, PreregistrationCard},
    props: {
        data: { type: Object },
    },
    mixins: [DataFormatterMixin],
}
</script>

<template>
    <div v-if="!!data" class="border p-2 md:rounded-md flex flex-col gap-2 bg-gray-100 max-w-xl drop-shadow-lg">
        <div class="flex flex-row bg-AB gap-2 text-white p-2 rounded-md justify-between shadow py-4">
            <div class="flex flex-col justify-center drop-shadow">
                <label class="leading-tight font-semibold text-2xl">{{ data.request_form.project_title }}</label>
                <p class="text-xs leading-none">
                    {{ data.request_form.id }}
                </p>
            </div>
            <div class="flex flex-col items-center justify-center border-l pl-2">
                <label class="text-xl leading-none font-[1000]">2546</label>
                <span class="text-[0.6rem] leading-none select-none">Form ID</span>
            </div>
        </div>

        <div class="flex flex-col items-center justify-center p-2 rounded-md select-none drop-shadow">
            <span v-if="isExpired" class="text-sm uppercase leading-none text-red-600">Form Expired</span>
            <span v-else class="text-sm uppercase leading-none">Form will close in </span>
            <label class="leading-none font-bold text-4xl" :class="{'text-red-600' : isExpired}">{{ countdownDisplay }}</label>
        </div>

        <div class="flex relative items-center drop-shadow">
            <div class="bg-AA text-center py-3 text-white rounded-md flex flex-col leading-none w-full">
                <label class="font-bold">{{ formatTime(data.time_from) }} {{ formatDate(data.date_from) }}</label>
                <span class="text-xs">Start</span>
            </div>
            <div class="flex w-full h-full bg-AB max-w-[2rem]">
                <label class="m-auto text-white font-bold">TO</label>
            </div>
            <div class="bg-AA text-center py-3 text-white rounded-md flex flex-col leading-none w-full">
                <label class="font-bold">{{ formatTime(data.time_to) }} {{ formatDate(data.date_to) }}</label>
                <span class="text-xs">End</span>
            </div>
        </div>

        <div class="px-1">
            <div>
                <span class="font-bold uppercase">Venue: </span>
                <label>{{ data.venue }}</label>
            </div>
            <p class="text-sm leading-none text-justify">{{ data.details }}</p>
        </div>
        <div v-if="data.max_slots" class="px-1 flex gap-2 justify-between">
            <div>
                <span class="font-bold uppercase">Max Slots: </span>
                <label :class="{'text-red-600': data.participants_count >= data.max_slots}">{{ data.max_slots }}</label> <span v-if="data.participants_count >= data.max_slots" class="text-red-600">FULL</span>
            </div>
            <div>
                <span class="font-bold uppercase">Slots Available: </span>
                <label :class="{'text-red-600': data.participants_count >= data.max_slots}">{{ data.max_slots-data.participants_count}}</label> <span v-if="data.participants_count >= data.max_slots" class="text-red-600">FULL</span>
            </div>
        </div>
        <div class="px-1 py-2 select-none bg-gray-300">
            <div class="flex justify-evenly items-center">
                <div v-if="data.has_preregistration" class="flex items-center gap-1" title="Require guests to pre-register">
                    <div v-if="data.has_preregistration" class="rounded-full shadow bg-AC text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                            <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
                        </svg>
                    </div>
                    <label>Preregistration</label>
                </div>
                <div v-if="data.has_pretest" class="flex items-center gap-1" title="Require guests to take pre-test">
                    <div v-if="data.has_pretest" class="rounded-full shadow bg-AC text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                            <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
                        </svg>
                    </div>
                    <label>Pretest</label>
                </div>
                <div v-if="data.has_posttest" class="flex items-center gap-1" title="Require guests to take post-test">
                    <div v-if="data.has_posttest" class="rounded-full shadow bg-AC text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                            <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
                        </svg>
                    </div>
                    <label>Posttest</label>
                </div>
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
        <preregistration-card v-else :event-id="data.event_id" @createdModel="$emit('createdModel', $event)" />
    </div>
</template>

<style scoped>

</style>
