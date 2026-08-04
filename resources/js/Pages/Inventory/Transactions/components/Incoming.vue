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
        listConditions: {
            type: Array,
            default: () => [],
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

        <div class="flex flex-col px-3 sm:px-6 py-4 sm:py-6 gap-4 sm:gap-6 relative max-w-7xl mx-auto text-slate-900 dark:text-slate-100">
            <!-- Info Banner -->
            <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 rounded-2xl p-4 sm:p-5 shadow-xs">
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-amber-100 dark:bg-amber-900/60 rounded-xl flex-shrink-0">
                        <Info class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div class="space-y-1.5 text-xs sm:text-sm text-amber-900 dark:text-amber-200">
                        <p class="flex items-start gap-2">
                            <FileText class="w-4 h-4 mt-0.5 flex-shrink-0 text-amber-600 dark:text-amber-400" />
                            <span>Please refer to the <strong>RIS (Requisition and Issue Slip)</strong> for the correct details that should be entered in this form.</span>
                        </p>
                        <p class="flex items-start gap-2">
                            <Package class="w-4 h-4 mt-0.5 flex-shrink-0 text-amber-600 dark:text-amber-400" />
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
                        :list-conditions="listConditions"
                        @showNewItemForm="showNewItemForm = $event"
                    />
                </div>

                <!-- Side Panel: New Item Form -->
                <transition-container type="slide-right">
                    <div
                        v-if="showNewItemForm"
                        class="lg:w-96 w-full flex-shrink-0"
                    >
                        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/80">
                                <div class="flex items-center gap-2">
                                    <Plus class="w-4 h-4 text-lime-600 dark:text-lime-400" />
                                    <h3 class="font-bold text-xs sm:text-sm uppercase tracking-wider text-slate-800 dark:text-slate-200">New Item Catalog Entry</h3>
                                </div>
                                <button
                                    @click="showNewItemForm = false"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors"
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
                        class="fixed lg:absolute right-5 top-20 z-30 w-fit max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden"
                    >
                        <!-- Header -->
                        <div class="flex items-center justify-between px-4 py-3 bg-slate-900 dark:bg-slate-800 text-white border-b border-slate-800">
                            <div class="flex items-center gap-2">
                                <Warehouse class="w-5 h-5 text-lime-400" />
                                <h3 class="font-bold text-sm">Storage Locations Reference</h3>
                            </div>
                            <button
                                @click="toggleStorageReference"
                                class="p-1 rounded-lg hover:bg-white/20 transition-colors"
                            >
                                <X class="w-4 h-4" />
                            </button>
                        </div>

                        <!-- Table -->
                        <div class="max-h-[60vh] overflow-y-auto">
                            <table class="w-full text-xs sm:text-sm">
                                <thead class="bg-slate-50 dark:bg-slate-800/80 sticky top-0 border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="px-3 py-2 text-left font-bold text-slate-700 dark:text-slate-300 w-20">
                                        Room Code
                                    </th>
                                    <th class="px-3 py-2 text-left font-bold text-slate-700 dark:text-slate-300">
                                        Storage Facility
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr
                                    v-for="location in storage_locations"
                                    :key="location.name"
                                    class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors"
                                >
                                    <td class="px-3 py-2 font-mono text-lime-600 dark:text-lime-400 font-bold">
                                        {{ location.name }}
                                    </td>
                                    <td class="px-3 py-2 text-slate-700 dark:text-slate-300 font-medium">
                                        {{ location.label }}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer -->
                        <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-500 dark:text-slate-400 text-center">
                            {{ storage_locations.length }} storage locations registered
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
                    'flex items-center gap-2 px-4 py-3 rounded-full shadow-lg transition-all duration-300 active:scale-95',
                    showStorageReference
                        ? 'bg-slate-900 dark:bg-slate-800 text-white hover:bg-slate-800 border border-slate-700'
                        : 'bg-lime-600 hover:bg-lime-700 text-white font-bold shadow-lime-500/20 hover:shadow-xl hover:-translate-y-0.5'
                ]"
            >
                <MapPin class="w-5 h-5" />
                <span class="text-xs sm:text-sm font-semibold whitespace-nowrap">Storage Reference</span>
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
