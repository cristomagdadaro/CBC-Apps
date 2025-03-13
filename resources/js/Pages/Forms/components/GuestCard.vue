<script>
import PreregistrationCard from "@/Pages/Forms/components/PreregistrationCard.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";

export default {
    name: "GuestCard",
    components: {InputError, InputLabel, TextInput, PreregistrationCard},
    props: {
        data: { type: Object },
    },
    data() {
        return {
            intervalId: null,
            countdownData: { days: 0, hours: 0, minutes: 0, seconds: 0 }
        };
    },
    methods: {
        parseDate(dateString) {
            // Ensure correct parsing of "YYYY-MM-DD" format
            const [year, month, day] = dateString.split('-').map(Number);
            return new Date(year, month - 1, day); // Month is zero-based
        },

        updateCountdown() {
            if (!this.data || !this.data.date_to || !this.data.time_to) {
                this.countdownData = { days: 0, hours: 0, minutes: 0, seconds: 0 };
                return;
            }

            const now = new Date();
            const targetDate = this.parseDate(this.data.date_to);

            if (!targetDate) {
                console.error("Failed to parse target date.");
                return;
            }

            // Extract time from "HH:MM:SS" and set it on the target date
            const [hours, minutes, seconds] = this.data.time_to.split(':').map(Number);
            targetDate.setHours(hours, minutes, seconds);

            const timeDifference = targetDate - now;

            if (timeDifference <= 0) {
                this.countdownData = { days: 0, hours: 0, minutes: 0, seconds: 0 };
                clearInterval(this.intervalId);
                return;
            }

            this.countdownData = {
                days: Math.floor(timeDifference / (1000 * 60 * 60 * 24)),
                hours: Math.floor((timeDifference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
                minutes: Math.floor((timeDifference % (1000 * 60 * 60)) / (1000 * 60)),
                seconds: Math.floor((timeDifference % (1000 * 60)) / 1000),
            };
        },
        startCountdown() {
            this.updateCountdown();
            this.intervalId = setInterval(this.updateCountdown, 1000);
        },
        formatDate(dateString) {
            if (!dateString) return "";

            const [year, month, day] = dateString.split("-").map(Number);
            const date = new Date(year, month - 1, day); // Month is zero-based in JS

            return new Intl.DateTimeFormat("en-US", {
                year: "numeric",
                month: "long",
                day: "numeric"
            }).format(date);
        },
        formatTime(timeString) {
            if (!timeString) return "";

            const [hours, minutes, seconds] = timeString.split(":").map(Number);

            // Convert to 12-hour format
            const ampm = hours >= 12 ? "PM" : "AM";
            const formattedHours = hours % 12 === 0 ? 12 : hours % 12;

            return `${formattedHours}:${minutes.toString().padStart(2, "0")} ${ampm}`;
        }
    },
    computed: {
        countdownDisplay() {
            return `${this.countdownData.days}d ${this.countdownData.hours}h ${this.countdownData.minutes}m ${this.countdownData.seconds}s`;
        }
    },
    mounted() {
        this.startCountdown();
    },
    beforeDestroy() {
        clearInterval(this.intervalId);
    }

}
</script>

<template>

    <div v-if="!!data" class="border p-2 md:rounded-md flex flex-col gap-2 bg-gray-100 max-w-xl drop-shadow-lg">
        <div class="flex flex-row bg-AB gap-2 text-white p-2 rounded-md justify-between shadow py-4">
            <div class="flex flex-col justify-center drop-shadow">
                <label class="leading-none font-semibold text-2xl">{{ data.title }}</label>
                <p class="text-xs leading-none">
                    {{ data.description }}
                </p>
            </div>
            <div class="flex flex-col items-center justify-center border-l pl-2">
                <label class="text-xl leading-none font-[1000]">{{ data.event_id }}</label>
                <span class="text-[0.6rem] leading-none select-none">Form ID</span>
            </div>
        </div>

        <div class="flex flex-col items-center justify-center p-2 rounded-md select-none drop-shadow">
            <span class="text-sm uppercase leading-none">Event Starts in </span>
            <label class="leading-none font-bold text-4xl">{{ countdownDisplay }}</label>
        </div>

        <div class="grid grid-cols-2">
            <div class="bg-AA text-center py-3 text-white rounded-l-md flex flex-col leading-none">
                <label class="font-bold">{{ formatTime(data.time_from) }} {{ formatDate(data.date_from) }}</label>
                <span class="text-xs">Start</span>
            </div>
            <div class="bg-AD text-center py-3 text-white rounded-r-md flex flex-col leading-none">
                <label class="font-bold">{{formatTime(data.time_from) }} {{ formatDate(data.date_to) }}</label>
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
        <div v-if="data.has_preregistration && data.has_pretest && data.has_posttest" class="px-1 py-2 select-none bg-gray-300">
            <div class="grid grid-cols-3 justify-items-center">
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
        <preregistration-card :event-id="data.event_id" />
    </div>
</template>

<style scoped>

</style>
