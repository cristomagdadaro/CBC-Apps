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
    data() {
        return {
            showNewItemForm: false,
            extractedData: null,
            selectedExtractedItemIndex: 0,
        };
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
        activeExtractedItemData() {
            if (!this.extractedData) return null;
            return {
                supplier: this.extractedData.supplier,
                items: [this.extractedData.items?.[this.selectedExtractedItemIndex] || {}],
                transaction: this.extractedData.transaction,
            };
        },
    },
    methods: {
        handleOcrExtracted(data) {
            this.extractedData = data;
            this.selectedExtractedItemIndex = 0;
            
            // If item data is present, optionally auto-open the New Item form
            if (data?.items && data.items.length > 0) {
                this.showNewItemForm = true;
            }
        },
        handleOcrItemSelected(index) {
            this.selectedExtractedItemIndex = index;
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
                        @showNewItemForm="showNewItemForm = $event"
                        @ocr-extracted="handleOcrExtracted"
                        @ocr-item-selected="handleOcrItemSelected" />
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
                                    <h3 class="text-xs font-semibold uppercase text-slate-800 dark:text-slate-200">New Item Entry</h3>
                                </div>
                                <button
                                    @click="showNewItemForm = false"
                                    class="rounded-xl p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800">
                                    <X class="h-4 w-4" />
                                </button>
                            </div>
                            <div class="p-1">
                                <item-form 
                                    :extracted-data="activeExtractedItemData"
                                    @close="showNewItemForm = false" />
                            </div>
                        </div>
                    </div>
                </transition-container>
            </div>
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
