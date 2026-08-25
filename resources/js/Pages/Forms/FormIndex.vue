<script>
import ListOfForms from "@/Pages/Forms/components/ListOfForms.vue";
import FormsHeaderActions from "@/Pages/Forms/components/FormsHeaderActions.vue";
import Form from "@/Modules/domain/Form";
import ApiMixin from "@/Modules/mixins/ApiMixin";

export default {
    name: "FormIndex",
    components: { FormsHeaderActions, ListOfForms },
    computed: {
        Form() {
            return Form;
        },
    },
    mixins: [ApiMixin],
    data() {
        return {
            eventId: {
                cell1: null,
                cell2: null,
                cell3: null,
                cell4: null,
            },
            eventFormFromApi: null,
        };
    },
    beforeMount() {
        this.model = new Form();
        this.setFormAction("get");
    },
    async mounted() {
        this.eventFormFromApi = await this.fetchData();
    },
    methods: {
        async searchEvent() {
            this.eventFormFromApi = await this.fetchData();

            this.eventId.cell1 = null;
            this.eventId.cell2 = null;
            this.eventId.cell3 = null;
            this.eventId.cell4 = null;
        },
        getLastDigit(value) {
            return /^[0-9]$/.test(value) ? value : "";
        },
        handleInput(field, event) {
            const value = event.target.value.slice(-1);
            this.eventId[field] = this.getLastDigit(value);

            const index = parseInt(field.replace("cell", ""), 10);

            if (this.eventId[field] && this.$refs[`cell${index + 1}`]) {
                this.$refs[`cell${index + 1}`].focus();
            }

            this.form.search = Object.values(this.eventId).join("");
        },
        handleBackspace(field, event) {
            const index = parseInt(field.replace("cell", ""), 10);

            if (!this.eventId[field] && event.key === "Backspace" && this.$refs[`cell${index - 1}`]) {
                this.$refs[`cell${index - 1}`].focus();
            }
        },
    },
    watch: {
        eventId: {
            handler(newVal, oldVal) {
                this.form.search = Object.values(newVal).join("");
            },
            deep: true,
        },
    },
};
</script>

<template>
    <AppLayout title="Event Forms">
        <template #header>
            <forms-header-actions />
        </template>

        <div class="default-container pt-5">
            <form
                v-if="!!form"
                class="flex items-end gap-2"
                @submit.prevent="searchEvent">
                <div class="grid w-full grid-rows-2">
                    <div class="flex w-full items-end gap-2 px-2 lg:px-0">
                        <search-by
                            :value="form.filter"
                            :is-exact="form.is_exact"
                            :options="model.constructor.getFilterColumns()"
                            @isExact="form.is_exact = $event"
                            @searchBy="form.filter = $event" />
                        <text-input
                            v-if="form.filter !== 'event_id'"
                            placeholder="Search..."
                            v-model="form.search" />
                        <div
                            v-else
                            class="flex w-full flex-col">
                            <div class="grid grid-cols-4 items-center gap-0.5">
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
                                    autocomplete="event" />
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
                                    autocomplete="event" />
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
                                    autocomplete="event" />
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
                                    autocomplete="event" />
                            </div>
                            <InputError
                                class="mt-2"
                                :message="form.errors.event" />
                        </div>
                        <search-btn
                            type="submit"
                            :disabled="model?.processing"
                            class="w-[10rem] text-center">
                            <span v-if="!model?.processing">Search</span>
                            <span v-else>Searching</span>
                        </search-btn>
                    </div>
                    <div
                        v-if="eventFormFromApi"
                        class="flex w-full items-center gap-2">
                        <div
                            id="dtPaginatorContainer"
                            class="flex w-full items-center justify-center gap-1">
                            <!-- First Button -->
                            <paginate-btn
                                @click="
                                    form.page = 1;
                                    searchEvent();
                                "
                                :disabled="form.page === 1">
                                First
                            </paginate-btn>

                            <!-- Previous Button -->
                            <paginate-btn
                                @click="
                                    form.page = Math.max(1, form.page - 1);
                                    searchEvent();
                                "
                                :disabled="form.page === 1">
                                <template v-slot:icon>
                                    <arrow-left class="h-auto w-6" />
                                </template>
                                Prev
                            </paginate-btn>

                            <!-- Current Page Indicator -->
                            <div class="flex flex-col whitespace-nowrap text-center text-xs">
                                <span
                                    class="mx-1 font-medium"
                                    title="current page and total pages">
                                    <span>{{ eventFormFromApi?.current_page }}</span>
                                    /
                                    <span>{{ eventFormFromApi?.last_page }}</span>
                                </span>
                            </div>

                            <!-- Next Button -->
                            <paginate-btn
                                @click="
                                    form.page = Math.min(eventFormFromApi?.last_page, form.page + 1);
                                    searchEvent();
                                "
                                :disabled="form.page === eventFormFromApi?.last_page">
                                Next
                                <template v-slot:icon>
                                    <arrow-right class="h-auto w-6" />
                                </template>
                            </paginate-btn>

                            <!-- Last Button -->
                            <paginate-btn
                                @click="
                                    form.page = eventFormFromApi?.last_page;
                                    searchEvent();
                                "
                                :disabled="form.page === eventFormFromApi?.last_page">
                                Last
                            </paginate-btn>
                        </div>
                    </div>
                </div>
            </form>
            <div class="justify-center overflow-hidden border border-gray-300 bg-white sm:rounded-lg dark:bg-gray-800">
                <!-- Show forms when available -->
                <list-of-forms
                    v-if="eventFormFromApi && eventFormFromApi.total > 0 && !model.api.processing"
                    :forms-data="eventFormFromApi.data"
                    @removeModel="eventFormFromApi.data = eventFormFromApi.data.filter((form) => form.id !== $event.id)" />

                <!-- Show "Searching" when processing -->
                <div
                    v-else-if="model.api.processing"
                    class="rounded-lg border border-AB py-3 text-center">
                    Searching...
                </div>

                <!-- Show "Form does not exist" when search was performed but no results -->
                <div
                    v-else-if="eventFormFromApi && eventFormFromApi.total === 0 && form.search"
                    class="rounded-lg border border-AB py-3 text-center">
                    Form does not exist. Try using some filters.
                </div>

                <!-- Show "No forms available" when nothing was returned and no search was performed -->
                <div
                    v-else
                    class="rounded-lg border border-AB py-3 text-center">
                    No forms available.
                </div>
            </div>

            <div
                v-if="eventFormFromApi && eventFormFromApi.data?.length"
                class="mt-3 flex w-full items-center gap-2">
                <div
                    id="dtPaginatorContainer"
                    class="flex w-full items-center justify-center gap-1">
                    <!-- First Button -->
                    <paginate-btn
                        @click="
                            form.page = 1;
                            searchEvent();
                        "
                        :disabled="form.page === 1">
                        First
                    </paginate-btn>

                    <!-- Previous Button -->
                    <paginate-btn
                        @click="
                            form.page = Math.max(1, form.page - 1);
                            searchEvent();
                        "
                        :disabled="form.page === 1">
                        <template v-slot:icon>
                            <arrow-left class="h-auto w-6" />
                        </template>
                        Prev
                    </paginate-btn>

                    <!-- Current Page Indicator -->
                    <div class="flex flex-col whitespace-nowrap text-center text-xs">
                        <span
                            class="mx-1 font-medium"
                            title="current page and total pages">
                            <span>{{ eventFormFromApi?.current_page }}</span>
                            /
                            <span>{{ eventFormFromApi?.last_page }}</span>
                        </span>
                    </div>

                    <!-- Next Button -->
                    <paginate-btn
                        @click="
                            form.page = Math.min(eventFormFromApi?.last_page, form.page + 1);
                            searchEvent();
                        "
                        :disabled="form.page === eventFormFromApi?.last_page">
                        Next
                        <template v-slot:icon>
                            <arrow-right class="h-auto w-6" />
                        </template>
                    </paginate-btn>

                    <!-- Last Button -->
                    <paginate-btn
                        @click="
                            form.page = eventFormFromApi?.last_page;
                            searchEvent();
                        "
                        :disabled="form.page === eventFormFromApi?.last_page">
                        Last
                    </paginate-btn>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
