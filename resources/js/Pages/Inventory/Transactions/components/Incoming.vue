<script>
import IncomingForm from "@/Pages/Inventory/Transactions/components/IncomingForm.vue";
import ItemForm from "@/Pages/Inventory/Items/components/ItemForm.vue";
import TransactionHeaderAction from "@/Pages/Inventory/Transactions/components/TransactionHeaderAction.vue";
import {
    Info,
    FileText,
    MapPin,
    X,
    ChevronDown,
    ChevronUp,
    Package,
    ArrowLeft,
    Plus,
    Warehouse
} from 'lucide-vue-next';

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
        parentTransaction: {
            type: Object,
            default: null,
        },
    },
    components: {
        IncomingForm,
        ItemForm,
        TransactionHeaderAction,
        Info,
        FileText,
        MapPin,
        X,
        ChevronDown,
        ChevronUp,
        Package,
        ArrowLeft,
        Plus,
        Warehouse,
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
        :title="isUpdate ? 'Update Transaction' : 'Incoming Transaction'"
    >
        <template v-slot:header>
            <transaction-header-action />
        </template>

        <div class="flex flex-col p-5 gap-5 relative max-w-7xl mx-auto">
            <!-- Info Banner -->
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <div class="p-1.5 bg-amber-100 dark:bg-amber-800 rounded-lg flex-shrink-0">
                        <Info class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div class="space-y-2 text-sm text-amber-800 dark:text-amber-200">
                        <p class="flex items-start gap-2">
                            <FileText class="w-4 h-4 mt-0.5 flex-shrink-0" />
                            <span>Please refer to the <strong>RIS (Requisition and Issue Slip)</strong> for the correct details that should be entered in this form.</span>
                        </p>
                        <p class="flex items-start gap-2">
                            <Package class="w-4 h-4 mt-0.5 flex-shrink-0" />
                            <span>For older stocks without an RIS or proper documentation, please enter details that can be physically verified, such as serial numbers, PhilRice barcodes, or other identifiable markings.</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="flex flex-col lg:flex-row gap-5 relative">
                <!-- Primary Form -->
                <div class="flex-1">
                    <incoming-form
                        :data="data"
                        :attached-reports="attachedReports"
                        :attached-components="attachedComponents"
                        :parent-transaction="parentTransaction"
                        @showNewItemForm="showNewItemForm = $event"
                    />
                </div>

                <!-- Side Panel: New Item Form -->
                <transition-container type="slide-right">
                    <div
                        v-if="showNewItemForm"
                        class="lg:w-96 w-full flex-shrink-0"
                    >
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                                <div class="flex items-center gap-2">
                                    <Plus class="w-4 h-4 text-AA" />
                                    <h3 class="font-semibold text-sm text-gray-900 dark:text-gray-100">New Item</h3>
                                </div>
                                <button
                                    @click="showNewItemForm = false"
                                    class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                >
                                    <X class="w-4 h-4" />
                                </button>
                            </div>
                            <div class="p-4">
                                <item-form @close="showNewItemForm = false" />
                            </div>
                        </div>
                    </div>
                </transition-container>

                <!-- Floating Storage Reference -->
                <transition-container type="pop-in">
                    <div
                        v-if="showStorageReference"
                        class="fixed lg:absolute right-5 top-20 z-30 w-fit max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden"
                    >
                        <!-- Header -->
                        <div class="flex items-center justify-between px-4 py-3 bg-AA text-white">
                            <div class="flex items-center gap-2">
                                <Warehouse class="w-5 h-5" />
                                <h3 class="font-semibold text-sm">Storage Locations</h3>
                            </div>
                            <button
                                @click="toggleStorageReference"
                                class="p-1 rounded-md hover:bg-white/20 transition-colors"
                            >
                                <X class="w-4 h-4" />
                            </button>
                        </div>

                        <!-- Table -->
                        <div class="max-h-[60vh] overflow-y-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700/50 sticky top-0">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 w-20">
                                        Room
                                    </th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700">
                                        Location
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr
                                    v-for="location in storage_locations"
                                    :key="location.name"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
                                >
                                    <td class="px-3 py-2 font-mono text-AA font-medium">
                                        {{ location.name }}
                                    </td>
                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                                        {{ location.label }}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer -->
                        <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400 text-center">
                            {{ storage_locations.length }} locations available
                        </div>
                    </div>
                </transition-container>
            </div>

            <!-- Floating Action Button: Storage Reference -->
            <button
                type="button"
                @click="toggleStorageReference"
                :class="[
                    'fixed right-5 bottom-5 z-20',
                    'flex items-center gap-2 px-4 py-3 rounded-full shadow-lg transition-all duration-300',
                    showStorageReference
                        ? 'bg-gray-800 dark:bg-gray-700 text-white hover:bg-gray-700 dark:hover:bg-gray-600'
                        : 'bg-AA text-white hover:bg-AA-dark hover:shadow-xl hover:-translate-y-0.5'
                ]"
            >
                <MapPin class="w-5 h-5" />
                <span class="text-sm font-medium whitespace-nowrap">Storage Reference</span>
                <component
                    :is="showStorageReference ? ChevronUp : ChevronDown"
                    class="w-4 h-4 transition-transform"
                />
            </button>
        </div>
    </app-layout>
</template>

<style scoped>
/* Smooth transitions for floating button */
button {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Custom scrollbar for storage reference table */
.max-h-\[60vh\]::-webkit-scrollbar {
    width: 6px;
}

.max-h-\[60vh\]::-webkit-scrollbar-track {
    background: transparent;
}

.max-h-\[60vh\]::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
}

.dark .max-h-\[60vh\]::-webkit-scrollbar-thumb {
    background-color: rgba(75, 85, 99, 0.5);
}

/* Responsive adjustments */
@media (max-width: 1024px) {
    .fixed.right-5 {
        right: 1rem;
    }
}
</style>
