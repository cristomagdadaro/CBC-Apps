<script>
import IncomingForm from "@/Pages/Inventory/Transactions/components/IncomingForm.vue";
import ItemForm from "@/Pages/Inventory/Items/components/ItemForm.vue";
import TransactionHeaderAction from "@/Pages/Inventory/Transactions/components/TransactionHeaderAction.vue";
import { Info, FileText, MapPin, X, ChevronDown, ChevronUp, Package, ArrowLeft, Plus, Warehouse } from "lucide-vue-next";

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
    <app-layout :title="isUpdate ? 'Update Transaction' : 'Incoming Transaction'">
        <template v-slot:header>
            <transaction-header-action />
        </template>

        <div class="relative flex flex-col gap-6 px-4 py-6 text-slate-900 sm:px-6 lg:px-8 dark:text-slate-100">
            <!-- Info Banner -->
            <div class="rounded-2xl border border-amber-200/60 bg-amber-50/80 p-5 shadow-sm backdrop-blur-xl transition-all dark:border-amber-500/20 dark:bg-amber-500/10">
                <div class="flex items-start gap-3.5">
                    <div class="shrink-0 rounded-xl border border-amber-200/50 bg-amber-100 p-2.5 dark:border-amber-500/30 dark:bg-amber-500/20">
                        <Info class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div class="mt-0.5 space-y-2 text-sm text-amber-900 dark:text-amber-200">
                        <p class="flex items-start gap-2.5">
                            <FileText class="mt-0.5 h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400" />
                            <span class="leading-relaxed">
                                Please refer to the
                                <span class="font-semibold">RIS (Requisition and Issue Slip)</span>
                                for the correct details that should be entered in this form.
                            </span>
                        </p>
                        <p class="flex items-start gap-2.5">
                            <Package class="mt-0.5 h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400" />
                            <span class="leading-relaxed">For older stocks without an RIS or proper documentation, please enter details that can be physically verified, such as serial numbers, PhilRice barcodes, or other identifiable markings.</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="relative flex flex-col gap-6 lg:flex-row">
                <!-- Primary Form -->
                <div class="min-w-0 flex-1">
                    <incoming-form
                        :data="data"
                        :attached-reports="attachedReports"
                        :attached-components="attachedComponents"
                        :parent-transaction="parentTransaction"
                        :list-conditions="listConditions"
                        @showNewItemForm="showNewItemForm = $event" />
                </div>

                <!-- Side Panel: New Item Form -->
                <transition-container type="slide-right">
                    <div
                        v-if="showNewItemForm"
                        class="w-full shrink-0 lg:w-[400px]">
                        <div class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white/90 shadow-xl ring-1 ring-slate-900/5 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 dark:ring-white/5">
                            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-5 py-4 dark:border-slate-800/60 dark:bg-slate-800/20">
                                <div class="flex items-center gap-2.5">
                                    <div class="rounded-lg bg-indigo-50 p-1.5 dark:bg-indigo-500/10">
                                        <Plus class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                                    </div>
                                    <h3 class="text-xs font-semibold uppercase tracking-widest text-slate-800 dark:text-slate-200">New Item Entry</h3>
                                </div>
                                <button
                                    @click="showNewItemForm = false"
                                    class="rounded-xl p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800">
                                    <X class="h-4 w-4" />
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
                        class="fixed bottom-20 right-4 z-30 flex w-[calc(100vw-2rem)] max-w-md flex-col overflow-hidden rounded-2xl border border-slate-200/60 bg-white/95 shadow-2xl backdrop-blur-xl sm:w-96 lg:absolute lg:bottom-auto lg:right-0 lg:top-0 dark:border-slate-800 dark:bg-slate-900/95">
                        <!-- Header -->
                        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-5 py-4 dark:border-slate-800 dark:bg-slate-800/40">
                            <div class="flex items-center gap-2.5">
                                <Warehouse class="h-4 w-4 text-indigo-500 dark:text-indigo-400" />
                                <h3 class="text-xs font-semibold uppercase tracking-widest text-slate-700 dark:text-slate-300">Storage Locations</h3>
                            </div>
                            <button
                                @click="toggleStorageReference"
                                class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-200 hover:text-slate-600 dark:hover:bg-slate-700">
                                <X class="h-4 w-4" />
                            </button>
                        </div>

                        <!-- Table -->
                        <div class="custom-scrollbar max-h-[50vh] overflow-y-auto">
                            <table class="w-full text-sm">
                                <thead class="sticky top-0 border-b border-slate-200 bg-slate-50 backdrop-blur-md dark:border-slate-700 dark:bg-slate-800/80">
                                    <tr>
                                        <th class="w-24 px-5 py-3 text-left text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Room Code</th>
                                        <th class="px-5 py-3 text-left text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Storage Facility</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800/60 dark:bg-slate-900/50">
                                    <tr
                                        v-for="location in storage_locations"
                                        :key="location.name"
                                        class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                        <td class="px-5 py-3 font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                                            {{ location.name }}
                                        </td>
                                        <td class="px-5 py-3 font-medium text-slate-700 dark:text-slate-300">
                                            {{ location.label }}
                                        </td>
                                    </tr>
                                    <tr v-if="!storage_locations.length">
                                        <td
                                            colspan="2"
                                            class="px-5 py-8 text-center text-sm font-medium text-slate-400 dark:text-slate-500">
                                            No storage locations registered.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer -->
                        <div class="border-t border-slate-100 bg-slate-50 px-5 py-3 text-center text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:border-slate-800 dark:bg-slate-800/30 dark:text-slate-400">{{ storage_locations.length }} Locations Registered</div>
                    </div>
                </transition-container>
            </div>

            <!-- Floating Action Button: Storage Reference -->
            <button
                type="button"
                @click="toggleStorageReference"
                :class="['fixed bottom-5 right-5 z-40', 'flex items-center gap-2 rounded-full border px-4 py-3 shadow-lg backdrop-blur-md transition-all duration-300 active:scale-95', showStorageReference ? 'border-slate-700 bg-slate-800/90 text-slate-200 shadow-xl hover:bg-slate-700 dark:bg-slate-800/90' : 'border-indigo-500/50 bg-indigo-600/95 font-medium text-white shadow-indigo-600/20 hover:-translate-y-0.5 hover:bg-indigo-700 hover:shadow-xl']">
                <MapPin class="h-4 w-4" />
                <span class="whitespace-nowrap text-sm font-semibold">Storage Reference</span>
                <component
                    :is="showStorageReference ? 'ChevronDown' : 'ChevronUp'"
                    class="h-4 w-4 transition-transform" />
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
