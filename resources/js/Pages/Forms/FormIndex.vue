<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import Welcome from "@/Components/Welcome.vue";
import ListOfForms from "@/Pages/Forms/components/ListOfForms.vue";
import AddButton from "@/Components/Buttons/AddButton.vue";
import Modal from "@/Components/Modal.vue";
import {Link, useForm} from '@inertiajs/vue3';
import FormsHeaderActions from "@/Pages/Forms/components/FormsHeaderActions.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import SearchBtn from "@/Components/Buttons/SearchBtn.vue";
import Form from "@/Modules/domain/Form";
import SearchBy from "@/Components/SearchBy.vue";
import PaginateBtn from "@/Components/PaginateBtn.vue";
import ArrowLeft from "@/Components/Icons/ArrowLeft.vue";
import ArrowRight from "@/Components/Icons/ArrowRight.vue";

export default {
    name: "FormIndex",
    computed: {
        Form() {
            return Form
        }
    },
    props: {
        listOfForms: Object,
    },
    components: {
        ArrowRight,
        ArrowLeft,
        PaginateBtn,
        SearchBy,
        SearchBtn,
        TextInput,
        InputError,
        FormsHeaderActions, Modal, AddButton, ListOfForms, Welcome, AppLayout, Link
    },
    data() {
        return {
            form: useForm({
                search: null,
                filter: null,
                is_exact: false,
                page: 1,
                per_page: 10
            }),
            eventId: {
                cell1: null,
                cell2: null,
                cell3: null,
                cell4: null,
            },
            eventFormFromApi: null,
            model: new Form(),
        }
    },
    methods: {
        async searchEvent() {

            this.eventFormFromApi = null;
            this.eventFormFromApi = await this.model.getIndex(this.form.data());

            this.eventId.cell1 = null;
            this.eventId.cell2 = null;
            this.eventId.cell3 = null;
            this.eventId.cell4 = null;
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
    mounted() {
        this.searchEvent();
    }
}
</script>

<template>
    <AppLayout title="Attendance Forms">
        <template #header>
            <forms-header-actions />
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form class="flex gap-2 items-end"  @submit.prevent="searchEvent">
                    <div class="grid grid-rows-2 w-full">
                        <div class="w-full flex gap-2 items-end">
                            <search-by :value="form.filter" :is-exact="form.is_exact" :options="Form.getFilterColumns()" @isExact="form.is_exact = $event" @searchBy="form.filter = $event" />
                            <text-input v-if="form.filter !== 'event_id'" placeholder="Search..." v-model="form.search" />
                            <div v-else class="flex flex-col">
                                <div class="grid grid-cols-4 gap-0.5 items-center">
                                    <TextInput
                                        ref="cell1"
                                        v-model="eventId.cell1"
                                        type="number"
                                        classes="text-center font-bold"
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
                                        classes="text-center font-bold"
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
                                        classes="text-center font-bold"
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
                                        classes="text-center font-bold"
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
                        </div>
                        <div v-if="eventFormFromApi" class="flex w-full gap-2 items-center">
                            <div id="dtPaginatorContainer" class="flex gap-1 items-center w-full justify-center">
                                <paginate-btn @click="form.page = 1" :disabled="eventFormFromApi?.current_page === 1">First</paginate-btn>
                                <paginate-btn @click="form.page = eventFormFromApi?.current_page-1" :disabled="true"> <arrow-left class="h-auto w-6" />Prev</paginate-btn>
                                <div class="text-xs flex flex-col whitespace-nowrap text-center">
                                    <span class="font-medium mx-1" title="current page and total pages">
                                        <input
                                            ref="input"
                                            type="text"
                                            v-model="form.page"
                                            class="border-x-0 text-right border-t-0 border-b p-0"
                                        /> / <input
                                            ref="input"
                                            type="text"
                                            v-model="form.per_page"
                                            class="border-x-0 text-right border-t-0 border-b p-0"
                                        />
                                    </span>
                                </div>
                                <paginate-btn @click="form.page = eventFormFromApi?.current_page+1" :disabled="true">Next <arrow-right class="h-auto w-6" /></paginate-btn>
                                <paginate-btn @click="form.page = eventFormFromApi.last_page" :disabled="true">Last</paginate-btn>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <list-of-forms v-if="eventFormFromApi" :forms-data="eventFormFromApi.data"/>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
