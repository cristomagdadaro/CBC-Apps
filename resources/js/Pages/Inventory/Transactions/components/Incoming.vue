<script>
import IncomingForm from "@/Pages/Inventory/Transactions/components/IncomingForm.vue";
import ItemForm from "@/Pages/Inventory/Items/components/ItemForm.vue";
import TransactionHeaderAction from "@/Pages/Inventory/Transactions/components/TransactionHeaderAction.vue";

export default {
    name: "Incoming",
    props: {
        data: {
            type: Object,
            default: null,
        },
        attachedReports: {
            type: Array,
            default: () => [],
        },
        attachedComponents: {
            type: Array,
            default: () => [],
        },
    },
    components: {
        IncomingForm,
        ItemForm,
        TransactionHeaderAction,
    },
    computed: {
        isUpdate() {
            return !!this.data?.id;
        },
        storage_locations() {
            if (!Array.isArray(this.$page.props.storage_locations)) {
                return [];
            }

            return this.$page.props.storage_locations.map((location) => ({
                name: location.name,
                label: location.label,
            }));
        },
    },
    data() {
        return {
            showNewItemForm: false,
            showStorageReference: false,
        };
    },
    methods: {
        toggleStorageReference() {
            this.showStorageReference = !this.showStorageReference;
        },
    },
};
</script>

<template>
    <app-layout
        :title="
            isUpdate
                ? 'Update Transaction Details'
                : 'Incoming Transaction Details'
        "
    >
        <template v-slot:header>
            <transaction-header-action />
        </template>

        <div class="flex flex-col p-5 gap-5 relative">
            <div>
                <h3>
                    Reminder: Please refer to the RIS (Requisition and Issue
                    Slip) for the correct details that should be entered in this
                    form.
                </h3>
                <h3>
                    For older stocks without an RIS or proper documentation,
                    please enter details that can be physically verified, such
                    as serial numbers, PhilRice barcodes, or other identifiable
                    markings.
                </h3>
            </div>
            <div class="flex gap-5">
                <incoming-form
                    :data="data"
                    :attached-reports="attachedReports"
                    :attached-components="attachedComponents"
                    @showNewItemForm="showNewItemForm = $event"
                />
                <transition-container type="pop-in">
                    <item-form
                        v-if="showNewItemForm"
                        @close="showNewItemForm = false"
                    />
                </transition-container>
                <transition-container type="pop-in">
                    <div
                        v-show="showStorageReference"
                        class="absolute right-5 top-20 z-20 w-fit max-w-[40rem] bg-white dark:bg-gray-800 border-collapse border rounded-lg shadow-lg"
                    >
                        <table class="w-full">
                            <thead class="bg-AA text-white">
                                <tr>
                                    <th class="border px-2 py-1 text-center">
                                        Room #
                                    </th>
                                    <th class="border px-2 py-1">Label</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="location in storage_locations"
                                    :key="location.name"
                                >
                                    <td
                                        class="border px-2 py-1 text-sm text-center"
                                    >
                                        {{ location.name }}
                                    </td>
                                    <td class="border px-2 py-1 text-sm">
                                        {{ location.label }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </transition-container>
            </div>
            <button
                type="button"
                class="absolute right-5 top-5 bg-AA text-white rounded-full px-4 py-2 text-sm shadow-md"
                @click="toggleStorageReference"
            >
                Storage Location Reference
                <span
                    class="absolute -bottom-2 left-6 w-0 h-0 border-l-[10px] border-l-transparent border-r-[10px] border-r-transparent border-t-[10px] border-t-AA"
                ></span>
            </button>
        </div>
    </app-layout>
</template>

<style scoped></style>
