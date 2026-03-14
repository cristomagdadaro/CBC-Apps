<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";

export default {
    name: "RentalVehicleApprovalCard",
    mixins: [ApiMixin, DataFormatterMixin],
    props: {
        data: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            showModal: false,
            formState: { ...this.data },
        };
    },
    computed: {
        canApprove() {
            const permissions = this.$page.props.auth?.permissions ?? [];
            return permissions.includes('*') || permissions.includes('rental.request.approve');
        },
    },
    methods: {
        statusClass(status) {
            if (status === 'approved') return 'text-green-600';
            if (status === 'rejected') return 'text-red-600';
            return 'text-yellow-600';
        },
        async updateStatus(status) {
            if (!this.canApprove || this.processing) {
                return;
            }

            try {
                const response = await this.fetchPutApi(
                    'api.rental.vehicles.update-status',
                    this.formState.id,
                    {
                        status,
                        notes: this.formState.notes,
                    }
                );

                this.formState = { ...response.data.data };
                this.$emit('updated', this.formState);
            } catch (error) {
                this.$emit('failedUpdate', error);
            }
        },
    },
    watch: {
        data: {
            handler(newData) {
                this.formState = { ...newData };
            },
            deep: true,
        },
    },
};
</script>

<template>
    <div class="p-2 rounded-md flex flex-col gap-2 max-w-full min-w-[20rem] w-full shadow justify-between bg-gray-100 hover:bg-gray-200 border overflow-x-auto">
        <div @dblclick="showModal = true" class="relative h-full flex flex-row p-2 rounded-md justify-between py-4 gap-2 cursor-pointer">
            <div class="flex flex-col w-full text-sm">
                <label class="leading-none font-semibold">{{ formState.requested_by || 'N/A' }}</label>
                <p class="leading-snug break-all">{{ formState.vehicle_type }}</p>
                <p class="leading-none break-all">{{ formatDate(formState.date_from) }} - {{ formatDate(formState.date_to) }}</p>
                <p class="leading-none break-all">{{ formatTime(formState.time_from) }} - {{ formatTime(formState.time_to) }}</p>
            </div>
            <div class="flex flex-col items-center justify-center text-right">
                <label class="text-lg leading-none font-[900] uppercase" :class="statusClass(formState.status)">{{ formState.status }}</label>
                <span class="text-[0.65rem] leading-none">{{ formatDate(formState.updated_at) }}</span>
            </div>
        </div>

        <Modal :show="showModal" :closeable="true" @close="showModal = false">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="mt-3 text-center sm:text-start w-full">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 pb-2 uppercase text-center">
                        Vehicle Rental Request
                    </h3>

                    <div class="flex flex-row bg-gray-200 p-2 rounded-md justify-between shadow py-4 gap-2">
                        <div class="flex flex-col text-sm">
                            <label class="leading-none font-semibold">{{ formState.requested_by || 'N/A' }}</label>
                            <p class="leading-snug break-all">{{ formState.vehicle_type }}</p>
                            <p class="leading-none break-all">{{ formatDate(formState.date_from) }} - {{ formatDate(formState.date_to) }}</p>
                            <p class="leading-none break-all">{{ formatTime(formState.time_from) }} - {{ formatTime(formState.time_to) }}</p>
                        </div>
                        <div class="flex flex-col items-end justify-center">
                            <label class="text-xl leading-none font-[1000] uppercase" :class="statusClass(formState.status)">{{ formState.status }}</label>
                            <span class="text-[0.65rem] leading-none">on {{ formatDate(formState.updated_at) }}</span>
                        </div>
                    </div>

                    <div class="mt-4 text-sm text-gray-700 dark:text-gray-300 space-y-1">
                        <p><b>Purpose:</b> {{ formState.purpose || 'N/A' }}</p>
                        <p><b>Contact Number:</b> {{ formState.contact_number || 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-2 px-6 py-4 bg-gray-100 dark:bg-gray-800">
                <text-area v-model="formState.notes" label="Reviewer Notes" :disabled="!canApprove" />
                <div class="flex justify-end">
                    <div v-if="canApprove" class="flex">
                        <button
                            type="button"
                            @click="updateStatus('rejected')"
                            :disabled="formState.status === 'rejected' || processing"
                            :class="[
                                'px-3 py-1 rounded-l text-sm font-semibold',
                                (formState.status === 'rejected' || processing)
                                    ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                    : 'bg-red-500 text-white'
                            ]"
                        >
                            {{ formState.status === 'rejected' ? 'Declined' : 'Decline' }}
                        </button>
                        <button
                            type="button"
                            @click="updateStatus('approved')"
                            :disabled="formState.status === 'approved' || processing"
                            :class="[
                                'px-3 py-1 rounded-r text-sm font-semibold',
                                (formState.status === 'approved' || processing)
                                    ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                    : 'bg-green-500 text-white'
                            ]"
                        >
                            {{ formState.status === 'approved' ? 'Approved' : 'Approve' }}
                        </button>
                    </div>
                </div>
            </div>
        </Modal>
    </div>
</template>
