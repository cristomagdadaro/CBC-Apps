<script>
import ListOfForms from "@/Pages/Forms/components/ListOfForms.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import AddButton from "@/Components/Buttons/AddButton.vue";
import {Link} from "@inertiajs/vue3";
import FormsHeaderActions from "@/Pages/Forms/components/FormsHeaderActions.vue";
import TextInput from "@/Components/TextInput.vue";
import TextArea from "@/Components/TextArea.vue";
import DateInput from "@/Components/DateInput.vue";
import TimeInput from "@/Components/TimeInput.vue";
import Form from "@/Modules/domain/Form";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import SuspendFormBtn from "@/Pages/Forms/components/SuspendFormBtn.vue";

export default {
    name: "FormUpdate",
    components: {
        SuspendFormBtn, TimeInput, DateInput, TextArea, TextInput, FormsHeaderActions, Link, AddButton, AppLayout, ListOfForms
    },
    mixins: [ApiMixin],
    beforeMount() {
        this.model = new Form();
        this.setFormAction('update');
    },
}
</script>

<template>
    <AppLayout title="Update Attendance Form">
        <template #header>
            <forms-header-actions />
        </template>
        <div class="py-12 mx-auto flex flex-col gap-5 max-w-5xl">
            <form v-if="!!form" @submit.prevent="submitUpdate" class="max-w-3xl min-w-xl w-full mx-auto">
                <div class="w-full mx-auto sm:px-6 lg:px-8 flex flex-col gap-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg" :class="{'border border-red-600': form.hasErrors}">
                        <div class="border p-2 rounded-md flex flex-col gap-2 bg-gray-100">
                            <div class="flex flex-row w-full gap-3 bg-gray-200 p-2 rounded-md justify-between shadow py-4">
                                <div class="flex flex-col justify-center gap-1 w-full">
                                    <text-input placeholder="Title" v-model="form.title" :error="form.errors.title"/>
                                    <text-area placeholder="Form Description" v-model="form.description" :error="form.errors.description" class="text-xs" />
                                </div>
                                <div class="flex flex-col items-center justify-center">
                                    <label class="text-3xl leading-none font-[1000]">{{ form.event_id }}</label>
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
                                    <text-input placeholder="Venue" v-model="form.venue" class="text-sm" :error="form.errors.venue"/>
                                </div>
                                <div>
                                    <span class="font-bold uppercase">Other details: </span>
                                    <text-area placeholder="Other details" v-model="form.details" class="w-full text-xs" :error="form.errors.details"/>
                                </div>
                            </div>
                            <div class="px-1">
                                <label class="font-bold uppercase" title="Additional steps for the form">
                                    Evaluation Requirements
                                </label>
                                <div class="flex justify-evenly">
                                    <div @click="form.has_preregistration = !form.has_preregistration" class="flex items-center gap-1" title="Require guests to pre-register">
                                        <input type="checkbox" class="rounded-full" :checked="form.has_preregistration">
                                        <label>Preregistration</label>
                                    </div>
                                    <div @click="form.has_pretest = !form.has_pretest" class="flex items-center gap-1" title="Require guests to take pre-test">
                                        <input type="checkbox" class="rounded-full" :checked="form.has_pretest">
                                        <label>Pretest</label>
                                    </div>
                                    <div @click="form.has_posttest = !form.has_posttest" class="flex items-center gap-1" title="Require guests to take post-test">
                                        <input type="checkbox" class="rounded-full" :checked="form.has_posttest">
                                        <label>Posttest</label>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col p-2">
                                <div class="flex gap-1 justify-between">
                                   <suspend-form-btn :data="form" />
                                    <button class="bg-blue-200 text-blue-900 w-fit px-4 py-2 rounded" title="Temporarily stop accepting responses">
                                        Update
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div v-if="data?.participants.length" class="bg-white dark:bg-gray-800 overflow-hidden w-full shadow-xl sm:rounded-lg">
                <div class="border p-2 rounded-md flex flex-col gap-2 bg-gray-100">
                    <table>
                        <thead>
                        <tr>
                            <th v-for="column in Object.keys(data?.participants[0])">
                                {{ column}}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="participant in data.participants">
                            <td v-for="column in Object.keys(data?.participants[0])">
                                {{ participant[column] }}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
