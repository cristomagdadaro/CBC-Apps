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
        <div class="min-h-screen bg-slate-50 px-3 py-4 text-slate-900 sm:px-6 sm:py-6 dark:bg-slate-950 dark:text-slate-100">
            <div class="space-y-4 sm:space-y-6">
                <!-- Process Overview -->
                <div class="shadow-xs rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-4">
                        <div class="shrink-0 rounded-xl bg-slate-100 p-3 dark:bg-slate-800">
                            <History class="h-6 w-6 text-lime-600 dark:text-lime-400" />
                        </div>
                        <div class="flex flex-col items-start gap-3 md:flex-row md:items-center md:gap-4">
                            <h2 class="shrink-0 text-sm font-bold uppercase tracking-wider text-slate-900 sm:text-base dark:text-slate-100">Recounting Workflow</h2>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    <ScanLine class="h-3.5 w-3.5 text-lime-600 dark:text-lime-400" />
                                    Scan barcode
                                </span>
                                <ArrowRightLeft class="h-3.5 w-3.5 text-slate-400" />
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    <Box class="h-3.5 w-3.5 text-lime-600 dark:text-lime-400" />
                                    Review system count
                                </span>
                                <ArrowRightLeft class="h-3.5 w-3.5 text-slate-400" />
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    <Calculator class="h-3.5 w-3.5 text-amber-500" />
                                    Input physical count
                                </span>
                                <ArrowRightLeft class="h-3.5 w-3.5 text-slate-400" />
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-lime-200 bg-lime-50 px-3 py-1 text-xs font-semibold text-lime-700 dark:border-lime-900/60 dark:bg-lime-950/40 dark:text-lime-300">
                                    <Save class="h-3.5 w-3.5 text-lime-600 dark:text-lime-400" />
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
                        class="shadow-xs flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
                        <div class="shrink-0 rounded-xl bg-emerald-100 p-2 dark:bg-emerald-900/60">
                            <CheckCircle2 class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-bold sm:text-sm">Success</p>
                            <p class="text-xs opacity-90 sm:text-sm">{{ successMessage }}</p>
                        </div>
                    </div>
                    <div
                        v-if="errorMessage"
                        key="error"
                        class="shadow-xs flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-rose-900 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-200">
                        <div class="shrink-0 rounded-xl bg-rose-100 p-2 dark:bg-rose-900/60">
                            <AlertCircle class="h-5 w-5 text-rose-600 dark:text-rose-400" />
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-bold sm:text-sm">Error</p>
                            <p class="text-xs opacity-90 sm:text-sm">{{ errorMessage }}</p>
                        </div>
                    </div>
                </transition-group>
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-12">
                    <!-- Left Column: Scanning & Search -->
                    <div class="space-y-4 lg:col-span-5">
                        <div class="shadow-xs space-y-4 rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 dark:border-slate-800 dark:bg-slate-900">
                            <camera-scanner
                                class="w-full"
                                @decoded="handleDecoded" />

                            <div class="space-y-2">
                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <input
                                            v-model="barcode"
                                            type="text"
                                            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 font-mono text-xs text-slate-900 transition-all placeholder:text-slate-400 focus:ring-2 focus:ring-lime-500 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                                            placeholder="Enter or scan barcode..."
                                            :disabled="submitLoading"
                                            @keyup.enter="lookupBarcode" />
                                        <Barcode class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                    </div>
                                    <button
                                        type="button"
                                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-lime-600 px-4 py-2.5 text-xs font-semibold text-white transition-all hover:bg-lime-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 sm:text-sm"
                                        :disabled="lookupLoading || submitLoading || !barcode"
                                        @click="lookupBarcode">
                                        <Loader2
                                            v-if="lookupLoading"
                                            class="h-4 w-4 animate-spin" />
                                        <Search
                                            v-else
                                            class="h-4 w-4" />
                                        <span>{{ lookupLoading ? "Searching..." : "Lookup" }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Quick Stats -->
                        <div
                            v-if="hasLookupResult"
                            class="shadow-xs rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 dark:border-slate-800 dark:bg-slate-900">
                            <h4 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-900 sm:text-sm dark:text-slate-100">
                                <Package class="h-4 w-4 text-slate-400" />
                                Item Summary
                            </h4>
                            <div class="space-y-2.5 text-xs sm:text-sm">
                                <div class="flex items-center justify-between border-b border-slate-100 py-2 dark:border-slate-800">
                                    <span class="font-medium text-slate-500 dark:text-slate-400">Current Stock</span>
                                    <span class="font-bold text-slate-900 dark:text-slate-100">{{ systemCount }} {{ lookupResult.unit || "units" }}</span>
                                </div>
                                <div class="flex items-center justify-between border-b border-slate-100 py-2 dark:border-slate-800">
                                    <span class="font-medium text-slate-500 dark:text-slate-400">Location</span>
                                    <span class="text-right font-bold text-slate-900 dark:text-slate-100">
                                        {{ lookupResult.location?.location_code || "N/A" }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between py-2">
                                    <span class="font-medium text-slate-500 dark:text-slate-400">Status</span>
                                    <span class="rounded-md border border-lime-200 bg-lime-50 px-2 py-0.5 text-xs font-semibold text-lime-600 dark:border-lime-900/60 dark:bg-lime-950/40 dark:text-lime-400">Verified</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Right Column: Recount & Adjust -->
                    <div class="lg:col-span-7">
                        <div class="shadow-xs min-h-[480px] overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                            <div class="border-b border-slate-200 bg-slate-50 p-4 sm:p-5 dark:border-slate-800 dark:bg-slate-800/80">
                                <div class="flex items-center gap-3">
                                    <div class="rounded-xl border border-slate-200 bg-white p-2 dark:border-slate-700 dark:bg-slate-800">
                                        <Calculator class="h-5 w-5 text-lime-600 dark:text-lime-400" />
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 sm:text-sm dark:text-slate-100">Recount & Adjust</h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Verify physical count and apply adjustments</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 sm:p-6">
                                <div
                                    v-if="hasLookupResult"
                                    class="space-y-5">
                                    <!-- Item Info Cards -->
                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                                            <div class="flex items-start gap-3">
                                                <div class="rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-700 dark:bg-slate-800">
                                                    <Package class="h-5 w-5 text-lime-600 dark:text-lime-400" />
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-[0.65rem] font-bold uppercase tracking-wider text-slate-400">Item Name</p>
                                                    <p class="truncate text-sm font-bold text-slate-900 dark:text-slate-100">
                                                        {{ lookupResult.name }}
                                                    </p>
                                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                                        {{ lookupResult.brand || "No brand specified" }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                                            <div class="flex items-start gap-3">
                                                <div class="rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-700 dark:bg-slate-800">
                                                    <Warehouse class="h-5 w-5 text-lime-600 dark:text-lime-400" />
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-[0.65rem] font-bold uppercase tracking-wider text-slate-400">System Quantity</p>
                                                    <p class="text-xl font-bold text-slate-900 sm:text-2xl dark:text-slate-100">
                                                        {{ systemCount }}
                                                    </p>
                                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                                        {{ lookupResult.unit || "units" }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Input Section -->
                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                                <Calculator class="mr-1 inline h-4 w-4 text-slate-400" />
                                                Physical Count
                                            </label>
                                            <div class="relative">
                                                <input
                                                    v-model.number="form.physical_count"
                                                    type="number"
                                                    min="0"
                                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-base font-bold text-slate-900 transition-all focus:ring-2 focus:ring-lime-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                                                    placeholder="0"
                                                    :disabled="submitLoading" />
                                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">
                                                    {{ lookupResult.unit || "units" }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                                <MapPin class="mr-1 inline h-4 w-4 text-slate-400" />
                                                Update Location
                                                <span class="ml-1 text-[0.65rem] font-semibold text-slate-400">(optional)</span>
                                            </label>
                                            <div class="relative">
                                                <select
                                                    class="w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-900 transition-all focus:ring-2 focus:ring-lime-500 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
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
                                                <MapPin class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                            </div>
                                        </div>
                                        <!-- Adjustment Preview -->
                                        <div
                                            class="rounded-xl border p-3.5 transition-all duration-300"
                                            :class="adjustmentColorClass">
                                            <div class="mb-1 flex items-center gap-1.5">
                                                <TrendingUp
                                                    v-if="adjustmentType === 'incoming'"
                                                    class="h-4 w-4" />
                                                <TrendingDown
                                                    v-else-if="adjustmentType === 'outgoing'"
                                                    class="h-4 w-4" />
                                                <Minus
                                                    v-else
                                                    class="h-4 w-4" />
                                                <span class="text-[0.65rem] font-bold uppercase tracking-wider">Adjustment</span>
                                            </div>
                                            <p class="text-xl font-extrabold">
                                                <template v-if="adjustment === null">—</template>
                                                <template v-else-if="adjustment > 0">+{{ adjustment }}</template>
                                                <template v-else>{{ adjustment }}</template>
                                            </p>
                                            <p class="mt-0.5 text-xs font-medium opacity-80">
                                                <template v-if="adjustmentType === 'incoming'">Stock increase</template>
                                                <template v-else-if="adjustmentType === 'outgoing'">Stock decrease</template>
                                                <template v-else-if="adjustmentType === 'none'">No change</template>
                                                <template v-else>Enter count to see adjustment</template>
                                            </p>
                                        </div>
                                    </div>
                                    <!-- Action Button -->
                                    <div class="flex justify-end border-t border-slate-200 pt-3 dark:border-slate-800">
                                        <button
                                            class="inline-flex items-center gap-2 rounded-xl bg-lime-600 px-6 py-2.5 text-xs font-bold text-white transition-all hover:bg-lime-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 sm:text-sm"
                                            :disabled="!canSubmit"
                                            @click="submitAdjustment">
                                            <Loader2
                                                v-if="submitLoading"
                                                class="h-4 w-4 animate-spin" />
                                            <Save
                                                v-else
                                                class="h-4 w-4" />
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
                                    <div class="mb-3 flex h-20 w-20 items-center justify-center rounded-2xl border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800/60">
                                        <ScanLine class="h-10 w-10 text-slate-400" />
                                    </div>
                                    <h3 class="mb-1 text-sm font-bold text-slate-900 sm:text-base dark:text-slate-100">Ready to Scan</h3>
                                    <p class="mb-4 max-w-sm text-xs text-slate-500 dark:text-slate-400">Use the camera scanner or enter a barcode manually to begin the inventory recounting process.</p>
                                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                                        <Barcode class="h-4 w-4" />
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
