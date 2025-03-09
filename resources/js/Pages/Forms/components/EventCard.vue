<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Form from "@/Modules/domain/Form.js";

export default {
    name: "EventCard",
    computed: {
        Form() {
            return Form
        }
    },
    mixins: [ApiMixin],
    beforeMount() {
        this.model = new Form();
    },
}
</script>

<template>
    <div class="border p-2 rounded-md flex flex-col gap-2 bg-gray-100 lg:max-w-2xl max-w-full min-w-[30rem] w-full overflow-x-auto">
        <div class="flex flex-row bg-gray-200 p-2 rounded-md justify-between shadow py-4">
            <div class="flex flex-col justify-center">
                <label class="leading-none font-semibold">{{ data.title }}</label>
                <p class="text-xs leading-none">
                    {{ data.description }}
                </p>
            </div>
            <div class="flex flex-col items-center justify-center">
                <label class="text-xl leading-none font-[1000]">{{ data.event_id }}</label>
                <span class="text-[0.6rem] leading-none select-none">Event ID</span>
            </div>
        </div>
        <div class="grid grid-cols-2 grid-rows-2 px-1">
            <div>
                <span class="font-bold uppercase">Start Date: </span>
                <label>{{ data.date_from }}</label>
            </div>
            <div>
                <span class="font-bold uppercase">End Date: </span>
                <label>{{ data.date_to }}</label>
            </div>
            <div>
                <span class="font-bold uppercase">Start Time: </span>
                <label>{{ data.time_from }}</label>
            </div>
            <div>
                <span class="font-bold uppercase">End Time: </span>
                <label>{{ data.time_to }}</label>
            </div>
        </div>
        <div class="px-1">
            <div>
                <span class="font-bold uppercase">Venue: </span>
                <label>{{ data.venue }}</label>
            </div>
            <p class="text-sm leading-none">{{ data.details }}</p>
        </div>
        <div class="px-1">
            <label class="font-bold uppercase" title="Additional steps for the form">
                Evaluation Requirements
            </label>
            <div class="flex justify-evenly">
                <div class="flex items-center gap-1" title="Require guests to pre-register">
                    <input type="checkbox" class="rounded-full" :checked="data.has_preregistration">
                    <label>Preregistration</label>
                </div>
                <div class="flex items-center gap-1" title="Require guests to take pre-test">
                    <input type="checkbox" class="rounded-full" :checked="data.has_pretest">
                    <label>Pretest</label>
                </div>
                <div class="flex items-center gap-1" title="Require guests to take post-test">
                    <input type="checkbox" class="rounded-full" :checked="data.has_posttest">
                    <label>Posttest</label>
                </div>
            </div>
        </div>
        <div class="flex flex-col border-t p-2 bg-gray-200 rounded-md">
            <span class="font-bold uppercase text-center">Statistics</span>
            <div class="flex gap-1 justify-center">
                <div class="flex flex-col items-center border-r-2 border-gray-900 text-green-900 w-fit px-2 py-1">
                    <label class="text-xl leading-none font-[1000]">{{ data.participants_count ?? 0 }}</label>
                    <span class="text-[0.6rem] leading-none select-none">Registered Participants</span>
                </div>

                <div class="flex flex-col items-center border-r-2 border-gray-900 text-green-900 w-fit px-2 py-1">
                    <label class="text-xl leading-none font-[1000]">{{ data.pretests_count ?? 0 }}</label>
                    <span class="text-[0.6rem] leading-none select-none">Pretest Responses</span>
                </div>

                <div class="flex flex-col items-center text-green-900 w-fit px-2 py-1">
                    <label class="text-xl leading-none font-[1000]">{{ data.posttests_count ?? 0 }}</label>
                    <span class="text-[0.6rem] leading-none select-none">Posttest Responses</span>
                </div>
            </div>
        </div>
        <div class="flex flex-col p-2">
            <div class="flex gap-1 justify-center">
                <a :href="route('forms.guest.index')+'/'+data.event_id" target="_blank" class="bg-green-200 text-green-900 w-fit px-2 py-1 rounded" title="Temporarily stop accepting responses">
                    Visit
                </a>

                <Link :href="route('forms.update')+'/'+data.event_id" class="bg-blue-200 text-blue-900 w-fit px-2 py-1 rounded" title="Temporarily stop accepting responses">
                    Update
                </Link>

                <button class="bg-blue-600 text-blue-100 w-fit px-2 py-1 rounded" title="Temporarily stop accepting responses">
                    Registration
                </button>

                <button class="bg-cyan-200 text-cyan-900 w-fit px-2 py-1 rounded" title="Temporarily stop accepting responses">
                    Export At
                </button>

                <button class="bg-yellow-200 text-yellow-900 w-fit px-2 py-1 rounded" title="Temporarily stop accepting responses">
                    Suspend
                </button>

                <button @click="submitDelete"  class="bg-red-200 text-red-900 w-fit px-2 py-1 rounded" title="Permanently remove this form">
                    Delete
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
