<script>
import { Head } from "@inertiajs/vue3";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import CameraScanner from "@/Components/CameraScanner.vue";
import TransactionHeaderAction from "@/Pages/Inventory/Transactions/components/TransactionHeaderAction.vue";
import { ScanLine, Package, MapPin, Calculator, ArrowRightLeft, CheckCircle2, AlertCircle, Loader2, Search, Save, History, Box, Barcode, Warehouse, TrendingUp, TrendingDown, Minus } from "lucide-vue-next";

export default {
    name: "InventoryRecounting",
    components: {
        Head,
        CameraScanner,
        TransactionHeaderAction,
        ScanLine,
        Package,
        MapPin,
        Calculator,
        ArrowRightLeft,
        CheckCircle2,
        AlertCircle,
        Loader2,
        Search,
        Save,
        History,
        Box,
        Barcode,
        Warehouse,
        TrendingUp,
        TrendingDown,
        Minus,
    },
    mixins: [ApiMixin],
    data() {
        return {
            barcode: "",
            lookupLoading: false,
            submitLoading: false,
            lookupResult: null,
            form: {
                physical_count: null,
                location_code: "",
                location_label: "",
            },
            successMessage: "",
            errorMessage: "",
        };
    },
    computed: {
        storageLocations() {
            if (!Array.isArray(this.$page.props?.storage_locations)) {
                return [];
            }
            return this.$page.props.storage_locations.map((location) => ({
                code: String(location?.name ?? "").trim(),
                label: String(location?.label ?? "").trim(),
                display: `${String(location?.name ?? "").trim()} - ${String(location?.label ?? "").trim()}`,
            }));
        },
        hasLookupResult() {
            return !!this.lookupResult;
        },
        systemCount() {
            return Number(this.lookupResult?.remaining_quantity ?? 0);
        },
        adjustment() {
            if (this.form.physical_count === null || this.form.physical_count === "" || Number.isNaN(Number(this.form.physical_count))) {
                return null;
            }
            return Number(this.form.physical_count) - this.systemCount;
        },
        adjustmentType() {
            if (this.adjustment === null) return null;
            if (this.adjustment > 0) return "incoming";
            if (this.adjustment < 0) return "outgoing";
            return "none";
        },
        canSubmit() {
            if (!this.hasLookupResult || this.submitLoading) return false;
            if (this.form.physical_count === null || this.form.physical_count === "") return false;
            return Number(this.form.physical_count) >= 0;
        },
        adjustmentIcon() {
            if (this.adjustmentType === "incoming") return "TrendingUp";
            if (this.adjustmentType === "outgoing") return "TrendingDown";
            return "Minus";
        },
        adjustmentColorClass() {
            if (this.adjustmentType === "incoming") return "text-emerald-600 bg-emerald-50 border-emerald-200 dark:bg-emerald-900/20 dark:border-emerald-800";
            if (this.adjustmentType === "outgoing") return "text-rose-600 bg-rose-50 border-rose-200 dark:bg-rose-900/20 dark:border-rose-800";
            return "text-gray-600 bg-gray-50 border-gray-200 dark:bg-gray-800 dark:border-gray-700";
        },
    },
    methods: {
        clearAlerts() {
            this.successMessage = "";
            this.errorMessage = "";
        },
        fillLocationFromResult() {
            this.form.location_code = this.lookupResult?.location?.location_code ?? "";
            this.form.location_label = this.lookupResult?.location?.location_label ?? "";
        },
        handleDecoded(decodedValue) {
            this.barcode = String(decodedValue ?? "").trim();
            this.lookupBarcode();
        },
        async lookupBarcode() {
            const value = String(this.barcode ?? "").trim();
            if (!value) {
                this.errorMessage = "Scan or enter a barcode first.";
                return;
            }
            this.lookupLoading = true;
            this.clearAlerts();
            try {
                const response = await this.fetchGetApi("api.inventory.transactions.recounting.lookup", {
                    barcode: value,
                });
                this.lookupResult = response?.data ?? null;
                this.form.physical_count = this.lookupResult?.remaining_quantity ?? 0;
                this.fillLocationFromResult();
            } catch (error) {
                this.lookupResult = null;
                this.form.physical_count = null;
                this.form.location_code = "";
                this.form.location_label = "";
                this.errorMessage = error?.response?.data?.message || "No matching inventory item found for this barcode.";
            } finally {
                this.lookupLoading = false;
            }
        },
        onLocationChange(event) {
            const selectedCode = String(event?.target?.value ?? "").trim();
            const selected = this.storageLocations.find((location) => location.code === selectedCode);

            this.form.location_code = selected?.code ?? "";
            this.form.location_label = selected?.label ?? "";
        },
        async submitAdjustment() {
            if (!this.canSubmit) {
                this.errorMessage = "Enter a valid physical count before applying adjustment.";
                return;
            }
            this.submitLoading = true;
            this.clearAlerts();
            try {
                const payload = {
                    barcode: String(this.barcode ?? "").trim(),
                    physical_count: Number(this.form.physical_count),
                    ...(this.form.location_code && this.form.location_label
                        ? {
                              location_code: this.form.location_code,
                              location_label: this.form.location_label,
                          }
                        : {}),
                };
                const response = await this.fetchPostApi("api.inventory.transactions.recounting.adjust", payload);
                const data = response?.data?.data ?? null;
                this.successMessage = response?.data?.message || "Inventory recount saved.";
                if (data?.item) {
                    this.lookupResult = data.item;
                    this.form.physical_count = data.item.remaining_quantity ?? 0;
                    this.fillLocationFromResult();
                }
            } catch (error) {
                this.errorMessage = error?.response?.data?.message || "Failed to apply recounting adjustment.";
            } finally {
                this.submitLoading = false;
            }
        },
    },
};
</script>

<template>
    <Head title="Inventory Recounting" />
    <AppLayout>
        <template #header>
            <transaction-header-action />
        </template>
        <div class="min-h-screen bg-slate-50 dark:bg-slate-950 px-3 sm:px-6 py-4 sm:py-6 text-slate-900 dark:text-slate-100">
            <div class="space-y-4 sm:space-y-6">
                <!-- Process Overview -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-slate-100 dark:bg-slate-800 rounded-xl shrink-0">
                            <History class="w-6 h-6 text-lime-600 dark:text-lime-400" />
                        </div>
                        <div class="flex flex-col md:flex-row items-start md:items-center gap-3 md:gap-4">
                            <h2 class="text-sm sm:text-base font-bold uppercase tracking-wider text-slate-900 dark:text-slate-100 shrink-0">Recounting Workflow</h2>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    <ScanLine class="w-3.5 h-3.5 text-lime-600 dark:text-lime-400" />
                                    Scan barcode
                                </span>
                                <ArrowRightLeft class="w-3.5 h-3.5 text-slate-400" />
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    <Box class="w-3.5 h-3.5 text-lime-600 dark:text-lime-400" />
                                    Review system count
                                </span>
                                <ArrowRightLeft class="w-3.5 h-3.5 text-slate-400" />
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    <Calculator class="w-3.5 h-3.5 text-amber-500" />
                                    Input physical count
                                </span>
                                <ArrowRightLeft class="w-3.5 h-3.5 text-slate-400" />
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-lime-50 dark:bg-lime-950/40 text-lime-700 dark:text-lime-300 border border-lime-200 dark:border-lime-900/60">
                                    <Save class="w-3.5 h-3.5 text-lime-600 dark:text-lime-400" />
                                    Apply adjustment transaction
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Alert Messages -->
                <transition-group name="fade">
                    <div
                        v-if="successMessage"
                        key="success"
                        class="flex items-center gap-3 p-4 rounded-2xl border border-emerald-200 dark:border-emerald-900/60 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 shadow-xs">
                        <div class="p-2 bg-emerald-100 dark:bg-emerald-900/60 rounded-xl shrink-0">
                            <CheckCircle2 class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-xs sm:text-sm">Success</p>
                            <p class="text-xs sm:text-sm opacity-90">{{ successMessage }}</p>
                        </div>
                    </div>
                    <div
                        v-if="errorMessage"
                        key="error"
                        class="flex items-center gap-3 p-4 rounded-2xl border border-rose-200 dark:border-rose-900/60 bg-rose-50 dark:bg-rose-950/40 text-rose-900 dark:text-rose-200 shadow-xs">
                        <div class="p-2 bg-rose-100 dark:bg-rose-900/60 rounded-xl shrink-0">
                            <AlertCircle class="w-5 h-5 text-rose-600 dark:text-rose-400" />
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-xs sm:text-sm">Error</p>
                            <p class="text-xs sm:text-sm opacity-90">{{ errorMessage }}</p>
                        </div>
                    </div>
                </transition-group>
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                    <!-- Left Column: Scanning & Search -->
                    <div class="lg:col-span-5 space-y-4">
                        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 sm:p-5 space-y-4 shadow-xs">
                            <camera-scanner
                                class="w-full"
                                @decoded="handleDecoded" />

                            <div class="space-y-2">
                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <input
                                            v-model="barcode"
                                            type="text"
                                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs sm:text-sm font-mono focus:ring-2 focus:ring-lime-500 transition-all placeholder:text-slate-400"
                                            placeholder="Enter or scan barcode..."
                                            :disabled="submitLoading"
                                            @keyup.enter="lookupBarcode" />
                                        <Barcode class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                    </div>
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-lime-600 hover:bg-lime-700 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-xs sm:text-sm transition-all shrink-0"
                                        :disabled="lookupLoading || submitLoading || !barcode"
                                        @click="lookupBarcode">
                                        <Loader2
                                            v-if="lookupLoading"
                                            class="w-4 h-4 animate-spin" />
                                        <Search
                                            v-else
                                            class="w-4 h-4" />
                                        <span>{{ lookupLoading ? "Searching..." : "Lookup" }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Quick Stats -->
                        <div
                            v-if="hasLookupResult"
                            class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 sm:p-5 shadow-xs">
                            <h4 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-slate-100 mb-3 flex items-center gap-2">
                                <Package class="w-4 h-4 text-slate-400" />
                                Item Summary
                            </h4>
                            <div class="space-y-2.5 text-xs sm:text-sm">
                                <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-500 dark:text-slate-400 font-medium">Current Stock</span>
                                    <span class="font-bold text-slate-900 dark:text-slate-100">{{ systemCount }} {{ lookupResult.unit || "units" }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                                    <span class="text-slate-500 dark:text-slate-400 font-medium">Location</span>
                                    <span class="font-bold text-slate-900 dark:text-slate-100 text-right">
                                        {{ lookupResult.location?.location_code || "N/A" }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-slate-500 dark:text-slate-400 font-medium">Status</span>
                                    <span class="text-xs font-semibold text-lime-600 dark:text-lime-400 bg-lime-50 dark:bg-lime-950/40 px-2 py-0.5 rounded-md border border-lime-200 dark:border-lime-900/60">Verified</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Right Column: Recount & Adjust -->
                    <div class="lg:col-span-7">
                        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs min-h-[480px]">
                            <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/80">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl">
                                        <Calculator class="w-5 h-5 text-lime-600 dark:text-lime-400" />
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-xs sm:text-sm uppercase tracking-wider text-slate-900 dark:text-slate-100">Recount & Adjust</h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Verify physical count and apply adjustments</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 sm:p-6">
                                <div
                                    v-if="hasLookupResult"
                                    class="space-y-5">
                                    <!-- Item Info Cards -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800">
                                            <div class="flex items-start gap-3">
                                                <div class="p-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg">
                                                    <Package class="w-5 h-5 text-lime-600 dark:text-lime-400" />
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider">Item Name</p>
                                                    <p class="font-bold text-slate-900 dark:text-slate-100 text-sm truncate">
                                                        {{ lookupResult.name }}
                                                    </p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                                        {{ lookupResult.brand || "No brand specified" }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800">
                                            <div class="flex items-start gap-3">
                                                <div class="p-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg">
                                                    <Warehouse class="w-5 h-5 text-lime-600 dark:text-lime-400" />
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider">System Quantity</p>
                                                    <p class="font-bold text-slate-900 dark:text-slate-100 text-xl sm:text-2xl">
                                                        {{ systemCount }}
                                                    </p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                                        {{ lookupResult.unit || "units" }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Input Section -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                                <Calculator class="w-4 h-4 inline mr-1 text-slate-400" />
                                                Physical Count
                                            </label>
                                            <div class="relative">
                                                <input
                                                    v-model.number="form.physical_count"
                                                    type="number"
                                                    min="0"
                                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-base font-bold focus:ring-2 focus:ring-lime-500 transition-all"
                                                    placeholder="0"
                                                    :disabled="submitLoading" />
                                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400 font-semibold">
                                                    {{ lookupResult.unit || "units" }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                                <MapPin class="w-4 h-4 inline mr-1 text-slate-400" />
                                                Update Location
                                                <span class="text-[0.65rem] font-semibold text-slate-400 ml-1">(optional)</span>
                                            </label>
                                            <div class="relative">
                                                <select
                                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-lime-500 transition-all appearance-none"
                                                    :value="form.location_code"
                                                    :disabled="submitLoading"
                                                    @change="onLocationChange">
                                                    <option value="">Keep current location</option>
                                                    <option
                                                        v-for="location in storageLocations"
                                                        :key="location.code"
                                                        :value="location.code">
                                                        {{ location.display }}
                                                    </option>
                                                </select>
                                                <MapPin class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                                            </div>
                                        </div>
                                        <!-- Adjustment Preview -->
                                        <div
                                            class="p-3.5 rounded-xl border transition-all duration-300"
                                            :class="adjustmentColorClass">
                                            <div class="flex items-center gap-1.5 mb-1">
                                                <TrendingUp
                                                    v-if="adjustmentType === 'incoming'"
                                                    class="w-4 h-4" />
                                                <TrendingDown
                                                    v-else-if="adjustmentType === 'outgoing'"
                                                    class="w-4 h-4" />
                                                <Minus
                                                    v-else
                                                    class="w-4 h-4" />
                                                <span class="text-[0.65rem] font-bold uppercase tracking-wider">Adjustment</span>
                                            </div>
                                            <p class="text-xl font-extrabold">
                                                <template v-if="adjustment === null">—</template>
                                                <template v-else-if="adjustment > 0">+{{ adjustment }}</template>
                                                <template v-else>{{ adjustment }}</template>
                                            </p>
                                            <p class="text-xs opacity-80 mt-0.5 font-medium">
                                                <template v-if="adjustmentType === 'incoming'">Stock increase</template>
                                                <template v-else-if="adjustmentType === 'outgoing'">Stock decrease</template>
                                                <template v-else-if="adjustmentType === 'none'">No change</template>
                                                <template v-else>Enter count to see adjustment</template>
                                            </p>
                                        </div>
                                    </div>
                                    <!-- Action Button -->
                                    <div class="flex justify-end pt-3 border-t border-slate-200 dark:border-slate-800">
                                        <button
                                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-lime-600 hover:bg-lime-700 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs sm:text-sm transition-all"
                                            :disabled="!canSubmit"
                                            @click="submitAdjustment">
                                            <Loader2
                                                v-if="submitLoading"
                                                class="w-4 h-4 animate-spin" />
                                            <Save
                                                v-else
                                                class="w-4 h-4" />
                                            <span>
                                                {{ submitLoading ? "Applying Adjustment..." : "Apply Inventory Adjustment" }}
                                            </span>
                                        </button>
                                    </div>
                                </div>
                                <!-- Empty State -->
                                <div
                                    v-else
                                    class="flex flex-col items-center justify-center py-16 text-center">
                                    <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800/60 rounded-2xl flex items-center justify-center mb-3 border border-slate-200 dark:border-slate-700">
                                        <ScanLine class="w-10 h-10 text-slate-400" />
                                    </div>
                                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 mb-1">Ready to Scan</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mb-4">Use the camera scanner or enter a barcode manually to begin the inventory recounting process.</p>
                                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                                        <Barcode class="w-4 h-4" />
                                        <span>Barcode is read-only in recounting mode</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition:
        opacity 0.3s ease,
        transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
