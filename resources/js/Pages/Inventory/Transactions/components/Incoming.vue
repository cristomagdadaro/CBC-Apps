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

        <div class="flex flex-col px-4 sm:px-6 lg:px-8 py-6 gap-6 relative max-w-[1600px] mx-auto text-slate-900 dark:text-slate-100">
            
            <!-- Info Banner -->
            <div class="bg-amber-50/80 dark:bg-amber-500/10 backdrop-blur-xl border border-amber-200/60 dark:border-amber-500/20 rounded-2xl p-5 shadow-sm transition-all">
                <div class="flex items-start gap-3.5">
                    <div class="p-2.5 bg-amber-100 dark:bg-amber-500/20 rounded-xl shrink-0 border border-amber-200/50 dark:border-amber-500/30">
                        <Info class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div class="space-y-2 text-sm text-amber-900 dark:text-amber-200 mt-0.5">
                        <p class="flex items-start gap-2.5">
                            <FileText class="w-4 h-4 mt-0.5 shrink-0 text-amber-600 dark:text-amber-400" />
                            <span class="leading-relaxed">Please refer to the <span class="font-semibold">RIS (Requisition and Issue Slip)</span> for the correct details that should be entered in this form.</span>
                        </p>
                        <p class="flex items-start gap-2.5">
                            <Package class="w-4 h-4 mt-0.5 shrink-0 text-amber-600 dark:text-amber-400" />
                            <span class="leading-relaxed">For older stocks without an RIS or proper documentation, please enter details that can be physically verified, such as serial numbers, PhilRice barcodes, or other identifiable markings.</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="flex flex-col lg:flex-row gap-6 relative">
                
                <!-- Primary Form -->
                <div class="flex-1 min-w-0">
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
                    <div v-if="showNewItemForm" class="lg:w-[400px] w-full shrink-0">
                        <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl rounded-2xl shadow-xl ring-1 ring-slate-900/5 dark:ring-white/5 border border-slate-200/60 dark:border-slate-800 overflow-hidden">
                            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/50 dark:bg-slate-800/20">
                                <div class="flex items-center gap-2.5">
                                    <div class="p-1.5 bg-indigo-50 dark:bg-indigo-500/10 rounded-lg">
                                        <Plus class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                                    </div>
                                    <h3 class="font-semibold text-xs uppercase tracking-widest text-slate-800 dark:text-slate-200">New Item Entry</h3>
                                </div>
                                <button
                                    @click="showNewItemForm = false"
                                    class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                >
                                    <X class="w-4 h-4" />
                                </button>
                            </div>
                            <div class="p-1">
                                <item-form @close="showNewItemForm = false" />
                            </div>
                        </div>
                    </div>
                </transition-container>

                <!-- Floating Storage Reference -->
                <transition-container type="pop-in">
                    <div
                        v-if="showStorageReference"
                        class="fixed lg:absolute right-4 bottom-20 lg:right-0 lg:top-0 lg:bottom-auto z-30 w-[calc(100vw-2rem)] sm:w-96 max-w-md bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-slate-200/60 dark:border-slate-800 overflow-hidden flex flex-col"
                    >
                        <!-- Header -->
                        <div class="flex items-center justify-between px-5 py-4 bg-slate-50/80 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-2.5">
                                <Warehouse class="w-4 h-4 text-indigo-500 dark:text-indigo-400" />
                                <h3 class="font-semibold text-xs uppercase tracking-widest text-slate-700 dark:text-slate-300">Storage Locations</h3>
                            </div>
                            <button
                                @click="toggleStorageReference"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors"
                            >
                                <X class="w-4 h-4" />
                            </button>
                        </div>

                        <!-- Table -->
                        <div class="max-h-[50vh] overflow-y-auto custom-scrollbar">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 dark:bg-slate-800/80 sticky top-0 border-b border-slate-200 dark:border-slate-700 backdrop-blur-md">
                                    <tr>
                                        <th class="px-5 py-3 text-left font-semibold text-[0.65rem] uppercase tracking-widest text-slate-500 dark:text-slate-400 w-24">
                                            Room Code
                                        </th>
                                        <th class="px-5 py-3 text-left font-semibold text-[0.65rem] uppercase tracking-widest text-slate-500 dark:text-slate-400">
                                            Storage Facility
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900/50">
                                    <tr
                                        v-for="location in storage_locations"
                                        :key="location.name"
                                        class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors"
                                    >
                                        <td class="px-5 py-3 font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                                            {{ location.name }}
                                        </td>
                                        <td class="px-5 py-3 text-slate-700 dark:text-slate-300 font-medium">
                                            {{ location.label }}
                                        </td>
                                    </tr>
                                    <tr v-if="!storage_locations.length">
                                        <td colspan="2" class="px-5 py-8 text-center text-slate-400 dark:text-slate-500 font-medium text-sm">
                                            No storage locations registered.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer -->
                        <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 text-center">
                            {{ storage_locations.length }} Locations Registered
                        </div>
                    </div>
                </transition-container>
            </div>

            <!-- Floating Action Button: Storage Reference -->
            <button
                type="button"
                @click="toggleStorageReference"
                :class="[
                    'fixed right-5 bottom-5 z-40',
                    'flex items-center gap-2 px-4 py-3 rounded-full shadow-lg transition-all duration-300 active:scale-95 border backdrop-blur-md',
                    showStorageReference
                        ? 'bg-slate-800/90 dark:bg-slate-800/90 text-slate-200 border-slate-700 hover:bg-slate-700 shadow-xl'
                        : 'bg-indigo-600/95 hover:bg-indigo-700 text-white font-medium border-indigo-500/50 shadow-indigo-600/20 hover:shadow-xl hover:-translate-y-0.5'
                ]"
            >
                <MapPin class="w-4 h-4" />
                <span class="text-sm font-semibold whitespace-nowrap">Storage Reference</span>
                <component
                    :is="showStorageReference ? 'ChevronDown' : 'ChevronUp'"
                    class="w-4 h-4 transition-transform"
                />
            </button>
        </div>
    </app-layout>
</template>

<style scoped>
/* Custom scrollbar for storage reference table */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(148, 163, 184, 0.4);
    border-radius: 9999px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(71, 85, 105, 0.4);
}

/* Responsive adjustments */
@media (max-width: 1024px) {
    .fixed.right-5 {
        right: 1rem;
    }
}
</style>