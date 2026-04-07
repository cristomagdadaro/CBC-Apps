<script>
import { Head } from '@inertiajs/vue3';
import ApiMixin from '@/Modules/mixins/ApiMixin';
import CameraScanner from '@/Components/CameraScanner.vue';
import TransactionHeaderAction from '@/Pages/Inventory/Transactions/components/TransactionHeaderAction.vue';
import { 
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
    Minus
} from 'lucide-vue-next';

export default {
    name: 'InventoryRecounting',
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
        Minus
    },
    mixins: [ApiMixin],
    data() {
        return {
            barcode: '',
            lookupLoading: false,
            submitLoading: false,
            lookupResult: null,
            form: {
                physical_count: null,
                location_code: '',
                location_label: '',
            },
            successMessage: '',
            errorMessage: '',
        };
    },
    computed: {
        storageLocations() {
            if (!Array.isArray(this.$page.props?.storage_locations)) {
                return [];
            }
            return this.$page.props.storage_locations.map((location) => ({
                code: String(location?.name ?? '').trim(),
                label: String(location?.label ?? '').trim(),
                display: `${String(location?.name ?? '').trim()} - ${String(location?.label ?? '').trim()}`,
            }));
        },
        hasLookupResult() {
            return !!this.lookupResult;
        },
        systemCount() {
            return Number(this.lookupResult?.remaining_quantity ?? 0);
        },
        adjustment() {
            if (this.form.physical_count === null || this.form.physical_count === '' || Number.isNaN(Number(this.form.physical_count))) {
                return null;
            }
            return Number(this.form.physical_count) - this.systemCount;
        },
        adjustmentType() {
            if (this.adjustment === null) return null;
            if (this.adjustment > 0) return 'incoming';
            if (this.adjustment < 0) return 'outgoing';
            return 'none';
        },
        canSubmit() {
            if (!this.hasLookupResult || this.submitLoading) return false;
            if (this.form.physical_count === null || this.form.physical_count === '') return false;
            return Number(this.form.physical_count) >= 0;
        },
        adjustmentIcon() {
            if (this.adjustmentType === 'incoming') return 'TrendingUp';
            if (this.adjustmentType === 'outgoing') return 'TrendingDown';
            return 'Minus';
        },
        adjustmentColorClass() {
            if (this.adjustmentType === 'incoming') return 'text-emerald-600 bg-emerald-50 border-emerald-200 dark:bg-emerald-900/20 dark:border-emerald-800';
            if (this.adjustmentType === 'outgoing') return 'text-rose-600 bg-rose-50 border-rose-200 dark:bg-rose-900/20 dark:border-rose-800';
            return 'text-gray-600 bg-gray-50 border-gray-200 dark:bg-gray-800 dark:border-gray-700';
        }
    },
    methods: {
        clearAlerts() {
            this.successMessage = '';
            this.errorMessage = '';
        },
        fillLocationFromResult() {
            this.form.location_code = this.lookupResult?.location?.location_code ?? '';
            this.form.location_label = this.lookupResult?.location?.location_label ?? '';
        },
        handleDecoded(decodedValue) {
            this.barcode = String(decodedValue ?? '').trim();
            this.lookupBarcode();
        },
        async lookupBarcode() {
            const value = String(this.barcode ?? '').trim();
            if (!value) {
                this.errorMessage = 'Scan or enter a barcode first.';
                return;
            }
            this.lookupLoading = true;
            this.clearAlerts();
            try {
                const response = await this.fetchGetApi('api.inventory.transactions.recounting.lookup', {
                    barcode: value,
                });
                this.lookupResult = response?.data ?? null;
                this.form.physical_count = this.lookupResult?.remaining_quantity ?? 0;
                this.fillLocationFromResult();
            } catch (error) {
                this.lookupResult = null;
                this.form.physical_count = null;
                this.form.location_code = '';
                this.form.location_label = '';
                this.errorMessage = error?.response?.data?.message || 'No matching inventory item found for this barcode.';
            } finally {
                this.lookupLoading = false;
            }
        },
        onLocationChange(event) {
            const selectedCode = String(event?.target?.value ?? '').trim();
            const selected = this.storageLocations.find((location) => location.code === selectedCode);

            this.form.location_code = selected?.code ?? '';
            this.form.location_label = selected?.label ?? '';
        },
        async submitAdjustment() {
            if (!this.canSubmit) {
                this.errorMessage = 'Enter a valid physical count before applying adjustment.';
                return;
            }
            this.submitLoading = true;
            this.clearAlerts();
            try {
                const payload = {
                    barcode: String(this.barcode ?? '').trim(),
                    physical_count: Number(this.form.physical_count),
                    ...(this.form.location_code && this.form.location_label
                        ? {
                            location_code: this.form.location_code,
                            location_label: this.form.location_label,
                        }
                        : {}),
                };
                const response = await this.fetchPostApi('api.inventory.transactions.recounting.adjust', payload);
                const data = response?.data?.data ?? null;
                this.successMessage = response?.data?.message || 'Inventory recount saved.';
                if (data?.item) {
                    this.lookupResult = data.item;
                    this.form.physical_count = data.item.remaining_quantity ?? 0;
                    this.fillLocationFromResult();
                }
            } catch (error) {
                this.errorMessage = error?.response?.data?.message || 'Failed to apply recounting adjustment.';
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
        <div class="min-h-screen bg-gray-50/50 dark:bg-gray-900/50 px-4 sm:p-6 lg:p-8">
            <div class="space-y-6">
                <!-- Process Overview -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-3">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                            <History class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="flex flex-col md:flex-row items-center gap-4">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recounting Workflow</h2>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                    <ScanLine class="w-3.5 h-3.5" />
                                    Scan barcode
                                </span>
                                <ArrowRightLeft class="w-4 h-4 text-gray-400" />
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                                    <Box class="w-3.5 h-3.5" />
                                    Review system count
                                </span>
                                <ArrowRightLeft class="w-4 h-4 text-gray-400" />
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                    <Calculator class="w-3.5 h-3.5" />
                                    Input physical count
                                </span>
                                <ArrowRightLeft class="w-4 h-4 text-gray-400" />
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    <Save class="w-3.5 h-3.5" />
                                    Apply adjustment transaction
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Alert Messages -->
                <transition-group name="fade">
                    <div v-if="successMessage" key="success" class="flex items-center gap-3 p-4 rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/20 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 shadow-sm">
                        <div class="p-2 bg-emerald-100 dark:bg-emerald-800 rounded-lg">
                            <CheckCircle2 class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-sm">Success</p>
                            <p class="text-sm opacity-90">{{ successMessage }}</p>
                        </div>
                    </div>
                    <div v-if="errorMessage" key="error" class="flex items-center gap-3 p-4 rounded-xl border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 text-red-800 dark:text-red-200 shadow-sm">
                        <div class="p-2 bg-red-100 dark:bg-red-800 rounded-lg">
                            <AlertCircle class="w-5 h-5 text-red-600 dark:text-red-400" />
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-sm">Error</p>
                            <p class="text-sm opacity-90">{{ errorMessage }}</p>
                        </div>
                    </div>
                </transition-group>
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Left Column: Scanning -->
                    <div class="lg:col-span-5 space-y-6">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50/50 to-transparent dark:from-blue-900/10 dark:to-transparent">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                                        <ScanLine class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white">Scan Barcode</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Use camera or manual entry</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-5 space-y-4">
                                <camera-scanner @decoded="handleDecoded" />
                                
                                <div class="relative">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <Barcode class="w-4 h-4 inline mr-1.5 text-gray-400" />
                                        Barcode
                                    </label>
                                    <div class="flex gap-2">
                                        <div class="relative flex-1">
                                            <input
                                                v-model="barcode"
                                                type="text"
                                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                                placeholder="Enter or scan barcode"
                                                :disabled="submitLoading"
                                                @keyup.enter="lookupBarcode"
                                            />
                                            <Barcode class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                        </div>
                                        <button
                                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium transition-all shadow-sm hover:shadow-md"
                                            :disabled="lookupLoading || submitLoading || !barcode"
                                            @click="lookupBarcode"
                                        >
                                            <Loader2 v-if="lookupLoading" class="w-4 h-4 animate-spin" />
                                            <Search v-else class="w-4 h-4" />
                                            <span class="hidden sm:inline">{{ lookupLoading ? 'Searching...' : 'Lookup' }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Quick Stats -->
                        <div v-if="hasLookupResult" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                <Package class="w-4 h-4 text-gray-400" />
                                Item Details
                            </h4>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Current Stock</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ systemCount }} units</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Location</span>
                                    <span class="font-medium text-gray-900 dark:text-white text-right">
                                        {{ lookupResult.location?.location_code || 'N/A' }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Last Updated</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-300">Just now</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Right Column: Recount & Adjust -->
                    <div class="lg:col-span-7">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden min-h-[500px]">
                            <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-indigo-50/50 to-transparent dark:from-indigo-900/10 dark:to-transparent">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                                        <Calculator class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white">Recount & Adjust</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Verify physical count and apply adjustments</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6">
                                <div v-if="hasLookupResult" class="space-y-6">
                                    <!-- Item Info Cards -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="group p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-700 transition-all">
                                            <div class="flex items-start gap-3">
                                                <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                                                    <Package class="w-5 h-5 text-blue-600" />
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Item Name</p>
                                                    <p class="font-semibold text-gray-900 dark:text-white truncate">{{ lookupResult.name }}</p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ lookupResult.brand || 'No brand specified' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="group p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 hover:border-purple-300 dark:hover:border-purple-700 transition-all">
                                            <div class="flex items-start gap-3">
                                                <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                                                    <Warehouse class="w-5 h-5 text-purple-600" />
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">System Quantity</p>
                                                    <p class="font-semibold text-gray-900 dark:text-white text-2xl">{{ systemCount }}</p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ lookupResult.unit || 'units' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Input Section -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="space-y-2">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                <Calculator class="w-4 h-4 inline mr-1.5 text-gray-400" />
                                                Physical Count
                                            </label>
                                            <div class="relative">
                                                <input
                                                    v-model.number="form.physical_count"
                                                    type="number"
                                                    min="0"
                                                    class="w-full px-4 py-3 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-lg font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                                    placeholder="0"
                                                    :disabled="submitLoading"
                                                />
                                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-gray-400">
                                                    {{ lookupResult.unit || 'units' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                <MapPin class="w-4 h-4 inline mr-1.5 text-gray-400" />
                                                Update Location
                                                <span class="text-xs font-normal text-gray-400 ml-1">(optional)</span>
                                            </label>
                                            <div class="relative">
                                                <select
                                                    class="w-full px-4 py-3 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all appearance-none"
                                                    :value="form.location_code"
                                                    :disabled="submitLoading"
                                                    @change="onLocationChange"
                                                >
                                                    <option value="">Keep current location</option>
                                                    <option
                                                        v-for="location in storageLocations"
                                                        :key="location.code"
                                                        :value="location.code"
                                                    >
                                                        {{ location.display }}
                                                    </option>
                                                </select>
                                                <MapPin class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" />
                                            </div>
                                        </div>
                                        <!-- Adjustment Preview -->
                                        <div 
                                            class="p-4 rounded-xl border-2 transition-all duration-300"
                                            :class="adjustmentColorClass"
                                        >
                                            <div class="flex items-center gap-2 mb-2">
                                                <TrendingUp v-if="adjustmentType === 'incoming'" class="w-5 h-5" />
                                                <TrendingDown v-else-if="adjustmentType === 'outgoing'" class="w-5 h-5" />
                                                <Minus v-else class="w-5 h-5" />
                                                <span class="text-xs font-bold uppercase tracking-wider">Adjustment</span>
                                            </div>
                                            <p class="text-2xl font-bold">
                                                <template v-if="adjustment === null">—</template>
                                                <template v-else-if="adjustment > 0">+{{ adjustment }}</template>
                                                <template v-else>{{ adjustment }}</template>
                                            </p>
                                            <p class="text-sm opacity-75 mt-1">
                                                <template v-if="adjustmentType === 'incoming'">Stock increase</template>
                                                <template v-else-if="adjustmentType === 'outgoing'">Stock decrease</template>
                                                <template v-else-if="adjustmentType === 'none'">No change</template>
                                                <template v-else>Enter count to see adjustment</template>
                                            </p>
                                        </div>
                                    </div>
                                    <!-- Action Button -->
                                    <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-700">
                                        <button
                                            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold transition-all shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/40 disabled:shadow-none transform hover:-translate-y-0.5 disabled:transform-none"
                                            :disabled="!canSubmit"
                                            @click="submitAdjustment"
                                        >
                                            <Loader2 v-if="submitLoading" class="w-5 h-5 animate-spin" />
                                            <Save v-else class="w-5 h-5" />
                                            <span>{{ submitLoading ? 'Applying Adjustment...' : 'Apply Inventory Adjustment' }}</span>
                                        </button>
                                    </div>
                                </div>
                                <!-- Empty State -->
                                <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                                    <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                                        <ScanLine class="w-12 h-12 text-gray-400" />
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Ready to Scan</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mb-6">
                                        Use the camera scanner or enter a barcode manually to begin the inventory recounting process.
                                    </p>
                                    <div class="flex items-center gap-2 text-xs text-gray-400">
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
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>