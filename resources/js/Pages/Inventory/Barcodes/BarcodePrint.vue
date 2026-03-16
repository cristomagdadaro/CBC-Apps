<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Transaction from "@/Modules/domain/Transaction";
import JsBarcode from "jsbarcode";
import QrcodeVue from "qrcode.vue";
import LabelCard from "./components/LabelCard.vue";

export default {
    name: "BarcodePrint",
    components: { 
        QrcodeVue, 
        LabelCard,
    },
    mixins: [ApiMixin],
    data() {
        return {
            items: [],
            loading: false,
            search: "",
            categoryId: 7,
            selected: {},
            labels: [],
            previewReady: false,
            exporting: false,
            printMode: "qr",
            sizeTemplate: "3x5",
            customHeightCm: 3,
            customWidthCm: 5,
            rotationDeg: 0,
            flipHorizontal: false,
            flipVertical: false,
            showLabelModal: false,
            selectedLabelForModal: null,
            orientation: "portrait",
            customFontSize: 10,
            customBarcodeHeight: 30,
            customQRSize: 60,
            layoutMode: "single",
            sheetSize: "a4",
            sheetMarginCm: 0.5,
            equipmentRouteMap: {
                7: "laboratory",
                4: "ict",
            },
            defaultPreviewBarcode: "CBC-00-000000",
            defaultPreviewBarcodeId: "default-preview-barcode-cbc-00-000000",
            activeTab: 'items',
            isMobile: false,
            showMobilePreview: false,
        };
    },
    computed: {
        sizeTemplates() {
            return [
                { key: "3x5", heightCm: 3, widthCm: 5, label: "3cm × 5cm", name: "3x5", icon: 'LuBarcode' },
                { key: "4.8x5.5", heightCm: 4.8, widthCm: 5.5, label: "4.8cm × 5.5cm", name: "4.8x5.5", icon: 'LuQrCode' },
                { key: "8x5", heightCm: 8, widthCm: 5, label: "8cm × 5cm", name: "8x5", icon: 'LuLayers' },
                { key: "1.5x6", heightCm: 1.5, widthCm: 6, label: "1.5cm × 6cm", name: "1.5x6", icon: 'LuBarcode' },
                { key: "custom", heightCm: null, widthCm: null, label: "Custom Size", name: "custom", icon: 'LuSettings2' },
            ];
        },
        isCustomSize() {
            return this.sizeTemplate === "custom";
        },
        baseHeightCm() {
            if (this.isCustomSize) {
                return this.normalizeSize(this.customHeightCm, 3);
            }
            const selected = this.sizeTemplates.find(item => item.key === this.sizeTemplate);
            return selected?.heightCm ?? 3;
        },
        baseWidthCm() {
            if (this.isCustomSize) {
                return this.normalizeSize(this.customWidthCm, 5);
            }
            const selected = this.sizeTemplates.find(item => item.key === this.sizeTemplate);
            return selected?.widthCm ?? 5;
        },
        resolvedHeightCm() {
            if (this.orientation === "landscape") {
                return this.baseWidthCm;
            }
            return this.baseHeightCm;
        },
        resolvedWidthCm() {
            if (this.orientation === "landscape") {
                return this.baseHeightCm;
            }
            return this.baseWidthCm;
        },
        labelFontSize() {
            return this.normalizeSize(this.customFontSize, 10);
        },
        hasBarcodeMode() {
            return this.printMode !== "qr";
        },
        hasQrMode() {
            return this.printMode !== "barcode";
        },
        cardWidthPx() {
            return this.resolvedWidthCm * 37.795;
        },
        cardHeightPx() {
            return this.resolvedHeightCm * 37.795;
        },
        cardUsableWidthPx() {
            return Math.max(36, this.cardWidthPx - 24);
        },
        cardUsableHeightPx() {
            return Math.max(28, this.cardHeightPx - 24);
        },
        textReservePx() {
            return Math.max(18, this.labelFontSize * 2.8);
        },
        captionReservePx() {
            return this.hasBarcodeMode ? Math.max(14, this.labelFontSize * 1.6) : Math.max(12, this.labelFontSize * 1.4);
        },
        graphicsAvailableHeightPx() {
            return Math.max(16, this.cardUsableHeightPx - this.textReservePx - this.captionReservePx);
        },
        maxQrSizePx() {
            const modeHeightLimit = this.printMode === "both"
                ? this.graphicsAvailableHeightPx * 0.58
                : this.graphicsAvailableHeightPx;
            return Math.max(60, Math.floor(Math.min(this.cardUsableWidthPx, modeHeightLimit)));
        },
        maxBarcodeHeightPx() {
            const modeHeightLimit = this.printMode === "both"
                ? this.graphicsAvailableHeightPx * 0.42
                : this.graphicsAvailableHeightPx;
            return Math.max(12, Math.floor(modeHeightLimit));
        },
        barcodeHeight() {
            const height = this.normalizeSize(this.customBarcodeHeight, this.maxBarcodeHeightPx);
            return Math.max(12, Math.min(height, this.maxBarcodeHeightPx));
        },
        qrSize() {
            const size = this.normalizeSize(this.customQRSize, this.maxQrSizePx);
            return Math.max(20, Math.min(size, this.maxQrSizePx));
        },
        modalBarcodeHeight() {
            return this.barcodeHeight * 4;
        },
        modalQRSize() {
            return this.qrSize * 5;
        },
        cardStyle() {
            return {
                width: `${this.resolvedWidthCm}cm`,
                height: `${this.resolvedHeightCm}cm`,
            };
        },
        cardInnerStyle() {
            const scaleX = this.flipHorizontal ? -1 : 1;
            const scaleY = this.flipVertical ? -1 : 1;
            return {
                transform: `rotate(${this.rotationDeg}deg) scale(${scaleX}, ${scaleY})`,
                transformOrigin: "center center",
            };
        },
        filteredItems() {
            const term = this.search?.toLowerCase()?.trim();
            if (!term) return this.items;
            return this.items.filter(item => {
                return [item.name, item.brand, item.description, item.barcode, item.barcode_prri]
                    .filter(Boolean)
                    .some(value => value.toLowerCase().includes(term));
            });
        },
        categoryOptions() {
            return this.$page?.props?.categories ?? [];
        },
        allSelected() {
            const selectable = this.filteredItems.filter(item => !!item.barcode);
            if (!selectable.length) return false;
            return selectable.every(item => this.selected[this.itemKey(item)]);
        },
        someSelected() {
            const selectable = this.filteredItems.filter(item => !!item.barcode);
            const selectedCount = selectable.filter(item => this.selected[this.itemKey(item)]).length;
            return selectedCount > 0 && selectedCount < selectable.length;
        },
        selectedCount() {
            return Object.values(this.selected).filter(Boolean).length;
        },
        totalLabels() {
            return Object.values(this.selected).reduce((sum, sel) => sum + (sel?.qty || 1), 0);
        },
        sheetDimensions() {
            if (this.sheetSize === "folio") {
                return { widthCm: 21.6, heightCm: 33 };
            }
            return { widthCm: 21, heightCm: 29.7 };
        },
        sheetUsableWidthCm() {
            return this.sheetDimensions.widthCm - (this.sheetMarginCm * 2);
        },
        sheetUsableHeightCm() {
            return this.sheetDimensions.heightCm - (this.sheetMarginCm * 2);
        },
        labelsPerRow() {
            return Math.max(1, Math.floor(this.sheetUsableWidthCm / this.resolvedWidthCm));
        },
        labelsPerColumn() {
            return Math.max(1, Math.floor(this.sheetUsableHeightCm / this.resolvedHeightCm));
        },
        labelsPerSheet() {
            return this.labelsPerRow * this.labelsPerColumn;
        },
        sheetedLabels() {
            if (this.layoutMode === "single") return [];
            const sheets = [];
            for (let i = 0; i < this.labels.length; i += this.labelsPerSheet) {
                sheets.push(this.labels.slice(i, i + this.labelsPerSheet));
            }
            return sheets;
        },
        printModeOptions() {
            return [
                { name: 'barcode', label: 'Barcode Only', icon: 'LuBarcode' },
                { name: 'qr', label: 'QR Code Only', icon: 'LuQrCode' },
                { name: 'both', label: 'Both', icon: 'LuLayers' },
            ];
        },
        currentStepValid() {
            if (this.activeTab === 'items') return this.selectedCount > 0;
            if (this.activeTab === 'settings') return true;
            return this.previewReady;
        },
    },
    watch: {
        printMode() {
            this.$nextTick(() => {
                this.renderBarcodes();
                this.renderModalBarcode();
                this.renderDefaultPreviewBarcode();
            });
        },
        barcodeHeight() {
            this.$nextTick(() => {
                this.renderBarcodes();
                this.renderModalBarcode();
                this.renderDefaultPreviewBarcode();
            });
        },
        sizeTemplate() {
            this.$nextTick(() => this.renderDefaultPreviewBarcode());
        },
        customWidthCm() {
            this.$nextTick(() => this.renderDefaultPreviewBarcode());
        },
        customHeightCm() {
            this.$nextTick(() => this.renderDefaultPreviewBarcode());
        },
        orientation() {
            this.$nextTick(() => this.renderDefaultPreviewBarcode());
        },
    },
    methods: {
        checkMobile() {
            this.isMobile = window.innerWidth < 768;
        },
        openLabelModal(label) {
            this.selectedLabelForModal = label;
            this.showLabelModal = true;
            this.$nextTick(() => {
                this.renderModalBarcode();
            });
        },
        closeLabelModal() {
            this.showLabelModal = false;
            this.selectedLabelForModal = null;
        },
        renderModalBarcode() {
            if (this.printMode === "qr" || !this.selectedLabelForModal) return;
            const el = document.getElementById(`modal-barcode-${this.selectedLabelForModal.key}`);
            if (!el || !this.selectedLabelForModal.item?.barcode) return;
            JsBarcode(el, this.selectedLabelForModal.item.barcode, {
                format: "CODE128",
                displayValue: false,
                width: this.getBarcodeModuleWidth(this.selectedLabelForModal.item.barcode),
                height: this.modalBarcodeHeight,
                margin: 0,
            });
        },
        renderDefaultPreviewBarcode() {
            if (this.printMode === "qr") return;
            const el = document.getElementById(this.defaultPreviewBarcodeId);
            if (!el) return;
            JsBarcode(el, this.defaultPreviewBarcode, {
                format: "CODE128",
                displayValue: false,
                width: this.getBarcodeModuleWidth(this.defaultPreviewBarcode),
                height: this.barcodeHeight,
                margin: 0,
            });
        },
        normalizeSize(value, fallback) {
            const num = Number(value);
            if (!Number.isFinite(num) || num <= 0) {
                return fallback;
            }
            return Math.max(0.5, Number(num.toFixed(2)));
        },
        applyPrintPageSize() {
            if (typeof document === "undefined") return;
            const styleId = "barcode-dynamic-page-size";
            let styleTag = document.getElementById(styleId);
            if (!styleTag) {
                styleTag = document.createElement("style");
                styleTag.id = styleId;
                document.head.appendChild(styleTag);
            }
            let pageSize = `${this.resolvedWidthCm}cm ${this.resolvedHeightCm}cm`;
            if (this.layoutMode === "sheet") {
                pageSize = `${this.sheetDimensions.widthCm}cm ${this.sheetDimensions.heightCm}cm`;
            }
            styleTag.textContent = `@media print { @page { size: ${pageSize}; margin: 0; } }`;
        },
        getEquipmentUrl(barcode) {
            if (!barcode) return "";
            const loggerSegment = this.equipmentRouteMap?.[Number(this.categoryId)] || "laboratory";
            const path = `/${loggerSegment}/equipments/${encodeURIComponent(barcode)}`;
            if (typeof window === "undefined") {
                return path;
            }
            const secureOrigin = window.location.origin.replace(/^http:/i, "https:");
            return `${secureOrigin}${path}`;
        },
        getBarcodeModuleWidth(barcodeValue) {
            const value = String(barcodeValue || "");
            const estimatedModules = Math.max(88, value.length * 11 + 35);
            const moduleWidth = this.cardUsableWidthPx / estimatedModules;
            return Math.max(1.1, Math.min(moduleWidth, 3.2));
        },
        itemKey(item) {
            return `${item.item_id}-${item.barcode}-${item.unit}`;
        },
        onCategoryChange(value) {
            this.categoryId = value || null;
            this.selected = {};
            this.labels = [];
            this.previewReady = false;
            this.loadItems();
        },
        async loadItems() {
            this.loading = true;
            this.model = new Transaction();
            this.setFormAction('get');
            this.form.per_page = '*';
            this.form.sort = 'name';
            this.form.order = 'asc';
            this.form.filter = this.categoryId ? 'category' : null;
            this.form.filter_by = this.categoryId ? this.categoryId : null;

            await this.fetchGetApi('api.inventory.transactions.remaining-stocks', this.form.data())
                .then((response) => {
                    this.items = response?.data ?? [];
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        toggleAll() {
            if (this.allSelected) {
                this.selected = {};
                return;
            }
            const next = {};
            this.filteredItems.forEach(item => {
                if (!item.barcode) return;
                const key = this.itemKey(item);
                next[key] = {
                    qty: (this.selected[key]?.qty ?? 1),
                    item,
                };
            });
            this.selected = next;
        },
        toggleItem(item) {
            if (!item.barcode) return;
            const key = this.itemKey(item);
            const next = { ...this.selected };
            if (next[key]) {
                delete next[key];
            } else {
                next[key] = { qty: 1, item };
            }
            this.selected = next;
        },
        updateQty(key, value) {
            const qty = Number(value);
            if (!this.selected[key]) return;
            this.selected[key].qty = Number.isFinite(qty) && qty > 0 ? qty : 1;
        },
        buildLabels() {
            const labels = [];
            Object.entries(this.selected).forEach(([key, selection]) => {
                const qty = selection?.qty ?? 1;
                const item = selection?.item;
                if (!item || !item.barcode) return;
                for (let i = 0; i < qty; i += 1) {
                    labels.push({
                        key: `${key}-${i}`,
                        item,
                        equipmentUrl: this.getEquipmentUrl(item.barcode),
                    });
                }
            });
            this.labels = labels;
            this.previewReady = labels.length > 0;
            this.activeTab = 'preview';
            this.$nextTick(() => {
                this.renderBarcodes();
            });
        },
        renderBarcodes() {
            if (!this.hasBarcodeMode) return;
            this.labels.forEach(label => {
                const barcodeValue = label.item?.barcode;
                if (!barcodeValue) return;
                const previewEl = document.getElementById(`barcode-${label.key}`);
                if (previewEl) {
                    JsBarcode(previewEl, barcodeValue, {
                        format: "CODE128",
                        displayValue: false,
                        width: this.getBarcodeModuleWidth(barcodeValue),
                        height: this.barcodeHeight,
                        margin: 0,
                    });
                }
            });
        },
        printLabels() {
            if (!this.previewReady) return;
            this.applyPrintPageSize();
            window.print();
        },
        async exportPdf() {
            if (!this.previewReady || this.exporting) return;
            this.exporting = true;
            try {
                const labels = this.labels.map(label => {
                    return {
                        name: label.item?.name ?? '',
                        brand: label.item?.brand ?? 'N/A',
                        barcode: label.item?.barcode ?? '',
                        qrUrl: this.printMode !== 'barcode' ? label.equipmentUrl : null,
                    };
                });

                const response = await this.fetchPostApi('inventory.generate-pdf', {
                    type: 'barcode-labels',
                    printMode: this.printMode,
                    paperWidth: this.resolvedWidthCm,
                    paperHeight: this.resolvedHeightCm,
                    qrSize: this.qrSize,
                    barcodeHeight: this.barcodeHeight,
                    labels,
                }, {
                    responseType: 'blob',
                });

                const blob = new Blob([response.data], { type: 'application/pdf' });
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                const disposition = response.headers?.['content-disposition'] ?? '';
                const match = disposition.match(/filename="?([^";]+)"?/i);
                link.href = url;
                link.download = match?.[1] ?? 'barcodes.pdf';
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);
            } finally {
                this.exporting = false;
            }
        },
        nextStep() {
            if (this.activeTab === 'items' && this.selectedCount > 0) {
                this.activeTab = 'settings';
            } else if (this.activeTab === 'settings') {
                this.buildLabels();
            }
        },
        prevStep() {
            if (this.activeTab === 'settings') {
                this.activeTab = 'items';
            } else if (this.activeTab === 'preview') {
                this.activeTab = 'settings';
            }
        },
    },
    mounted() {
        this.checkMobile();
        window.addEventListener('resize', this.checkMobile);
        this.applyPrintPageSize();
        this.loadItems();
        this.$nextTick(() => this.renderDefaultPreviewBarcode());
    },
    beforeUnmount() {
        window.removeEventListener('resize', this.checkMobile);
    },
};
</script>

<template>
    <Head title="Print Barcodes" />

    <AppLayout title="Barcode Printing">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <LuPrinter class="w-6 h-6" />
                        Barcode Printing
                    </h2>
                    <p class="text-sm text-white/80 mt-1">Generate and print labels for inventory items</p>
                </div>
                <div v-if="previewReady" class="flex items-center gap-2">
                    <span class="text-sm text-white/90 bg-white/10 px-3 py-1 rounded-full">
                        {{ totalLabels }} labels ready
                    </span>
                </div>
            </div>
        </template>
        <div class="md:grid md:grid-cols-12 gap-5 p-5">
        <!-- Help Section -->
        <div class="grid grid-cols-1 grid-rows-3 gap-4 col-span-12 md:col-span-2 h-fit md:sticky md:top-5 md:self-start">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2 mb-2">
                    <LuInfo class="w-5 h-5 text-blue-600" />
                    <h4 class="font-medium text-gray-900 dark:text-white">Printer Setup</h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Always recalibrate your Intermec PD43 after powering on or changing label rolls. Go to <strong>Wizards → Calibrate → Media</strong>.
                </p>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2 mb-2">
                    <LuQrCode class="w-5 h-5 text-blue-600" />
                    <h4 class="font-medium text-gray-900 dark:text-white">QR Code Usage</h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Scan QR codes with any mobile device to quickly access equipment logging pages. Supports Laboratory and ICT equipment.
                </p>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2 mb-2">
                    <LuLayers class="w-5 h-5 text-blue-600" />
                    <h4 class="font-medium text-gray-900 dark:text-white">Label Sizes</h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Choose from preset sizes or create custom dimensions. Ensure your selected size matches the loaded label stock for best print quality.
                </p>
            </div>
        </div>
        <div class="w-full cols-span-12 md:col-span-10">
            <!-- Mobile Tab Navigation -->
            <div class="md:hidden bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="flex border-b border-gray-200 dark:border-gray-700">
                    <button @click="activeTab = 'items'" class="flex-1 px-3 py-3 text-xs font-medium transition-colors"
                        :class="activeTab === 'items' ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50/50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-400'">
                        <div class="flex flex-col items-center gap-1">
                            <LuPackage class="w-4 h-4" />
                            <span>Select</span>
                            <span v-if="selectedCount > 0" class="text-[10px] bg-blue-600 text-white px-1.5 py-0.5 rounded-full">{{ selectedCount }}</span>
                        </div>
                    </button>
                    <button @click="activeTab = 'settings'" class="flex-1 px-3 py-3 text-xs font-medium transition-colors"
                        :class="activeTab === 'settings' ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50/50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-400'">
                        <div class="flex flex-col items-center gap-1">
                            <LuSettings2 class="w-4 h-4" />
                            <span>Settings</span>
                        </div>
                    </button>
                    <button @click="activeTab = 'preview'" :disabled="!previewReady" class="flex-1 px-3 py-3 text-xs font-medium transition-colors disabled:opacity-50"
                        :class="activeTab === 'preview' ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50/50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-400'">
                        <div class="flex flex-col items-center gap-1">
                            <LuPrinter class="w-4 h-4" />
                            <span>Print</span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Desktop Tab Navigation -->
            <div class="hidden md:block bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="flex border-b border-gray-200 dark:border-gray-700">
                    <button @click="activeTab = 'items'" class="flex-1 px-4 py-3 text-sm font-medium transition-colors flex items-center justify-center gap-2"
                        :class="activeTab === 'items' ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50/50 dark:bg-blue-900/20 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'">
                        <LuPackage class="w-4 h-4" />
                        Select Items
                        <span v-if="selectedCount > 0" class="ml-1 px-2 py-0.5 bg-blue-600 text-white text-xs rounded-full">{{ selectedCount }}</span>
                    </button>
                    <button @click="activeTab = 'settings'" class="flex-1 px-4 py-3 text-sm font-medium transition-colors flex items-center justify-center gap-2"
                        :class="activeTab === 'settings' ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50/50 dark:bg-blue-900/20 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'">
                        <LuSettings2 class="w-4 h-4" />
                        Label Settings
                    </button>
                    <button @click="activeTab = 'preview'" :disabled="!previewReady" class="flex-1 px-4 py-3 text-sm font-medium transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="activeTab === 'preview' ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50/50 dark:bg-blue-900/20 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'">
                        <LuEye class="w-4 h-4" />
                        Preview & Print
                    </button>
                </div>

                <!-- Items Tab -->
                <div v-show="activeTab === 'items'" class="p-4 sm:p-6 space-y-4">
                    <!-- Filters -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="flex-1 relative">
                            <LuSearch class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input v-model="search" type="text" placeholder="Search items, brands, or barcodes..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white text-sm" />
                        </div>
                        <custom-dropdown :value="categoryId" @selectedChange="onCategoryChange($event)" :options="categoryOptions"
                            placeholder="All Categories" class="w-full sm:w-64" />
                    </div>

                    <!-- Bulk Actions -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" :checked="allSelected" :indeterminate="someSelected" @change="toggleAll"
                                class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500" />
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ allSelected ? 'Deselect All' : 'Select All' }}
                            </span>
                        </div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ selectedCount }} of {{ filteredItems.filter(i => i.barcode).length }} selected
                        </span>
                    </div>

                    <!-- Items Table -->
                    <div v-if="loading" class="flex items-center justify-center py-12">
                        <LuLoader2 class="w-8 h-8 text-blue-600 animate-spin" />
                    </div>

                    <div v-else-if="filteredItems.length === 0" class="text-center py-12">
                        <LuPackageX class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" />
                        <p class="text-gray-500 dark:text-gray-400">No items found</p>
                    </div>

                    <div v-else class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                                    <tr>
                                        <th class="px-4 py-3 w-10"></th>
                                        <th class="px-4 py-3 text-left">Item</th>
                                        <th class="px-4 py-3 text-left">Barcode</th>
                                        <th class="px-4 py-3 text-left">Property No.</th>
                                        <th class="px-4 py-3 text-right w-24">Qty</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="item in filteredItems" :key="itemKey(item)" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
                                        :class="{ 'bg-blue-50/50 dark:bg-blue-900/10': selected[itemKey(item)] }">
                                        <td class="px-4 py-3">
                                            <input type="checkbox" :disabled="!item.barcode" :checked="!!selected[itemKey(item)]" @change="toggleItem(item)"
                                                class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 disabled:opacity-50" />
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-gray-900 dark:text-white"><b>{{ item.name }}</b> ({{ item.brand }})</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 sm:hidden">{{ item.brand }}</div>
                                            <div v-if="item.description" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ item.description }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span v-if="item.barcode" class="font-mono text-sm bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ item.barcode }}</span>
                                            <span v-else class="text-xs text-red-500 flex items-center gap-1">
                                                <LuAlertCircle class="w-3 h-3" />
                                                No barcode
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span v-if="item.barcode_prri" class="font-mono text-sm bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ item.barcode_prri }}</span>
                                            <span v-else class="text-xs text-gray-500 dark:text-gray-400">—</span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <input v-if="selected[itemKey(item)]" type="number" min="1"
                                                :value="selected[itemKey(item)]?.qty ?? 1" @input="updateQty(itemKey(item), $event.target.value)"
                                                class="w-16 px-2 py-1 text-right border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white text-sm" />
                                            <span v-else class="text-gray-400">—</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Next Button -->
                    <div class="flex justify-end pt-4">
                        <button @click="nextStep" :disabled="selectedCount === 0"
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                            Continue to Settings
                            <LuArrowRight class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <!-- Settings Tab -->
                <div v-show="activeTab === 'settings'" class="p-4 sm:p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Print Mode -->
                        <div class="space-y-3">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Print Mode</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button v-for="mode in printModeOptions" :key="mode.name" @click="printMode = mode.name"
                                    class="flex flex-col items-center gap-2 p-3 border-2 rounded-lg transition-all"
                                    :class="printMode === mode.name ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'">
                                    <component :is="mode.icon" class="w-6 h-6" />
                                    <span class="text-xs font-medium">{{ mode.label }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Size Template -->
                        <div class="space-y-3">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Label Size</label>
                            <custom-dropdown :value="sizeTemplate" @selectedChange="sizeTemplate = $event" :options="sizeTemplates"
                                class="w-full" />
                            <div v-if="isCustomSize" class="flex gap-2">
                                <div class="flex-1">
                                    <label class="text-xs text-gray-500">Height (cm)</label>
                                    <input v-model.number="customHeightCm" type="number" min="0.5" step="0.1"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" />
                                </div>
                                <div class="flex-1">
                                    <label class="text-xs text-gray-500">Width (cm)</label>
                                    <input v-model.number="customWidthCm" type="number" min="0.5" step="0.1"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" />
                                </div>
                            </div>
                        </div>

                        <!-- Layout Mode -->
                        <div class="space-y-3">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Layout</label>
                            <div class="flex gap-2">
                                <button @click="layoutMode = 'single'"
                                    class="flex-1 px-4 py-2 border-2 rounded-lg text-sm font-medium transition-colors"
                                    :class="layoutMode === 'single' ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 text-blue-700' : 'border-gray-200 dark:border-gray-600'">
                                    Single Label
                                </button>
                                <button @click="layoutMode = 'sheet'"
                                    class="flex-1 px-4 py-2 border-2 rounded-lg text-sm font-medium transition-colors"
                                    :class="layoutMode === 'sheet' ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 text-blue-700' : 'border-gray-200 dark:border-gray-600'">
                                    Sheet Layout
                                </button>
                            </div>
                        </div>

                        <!-- Orientation -->
                        <div class="space-y-3">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Orientation</label>
                            <div class="flex gap-2">
                                <button @click="orientation = 'portrait'"
                                    class="flex-1 px-4 py-2 border-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2"
                                    :class="orientation === 'portrait' ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 text-blue-700' : 'border-gray-200 dark:border-gray-600'">
                                    <LuSmartphone class="w-4 h-4" />
                                    Portrait
                                </button>
                                <button @click="orientation = 'landscape'"
                                    class="flex-1 px-4 py-2 border-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2"
                                    :class="orientation === 'landscape' ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 text-blue-700' : 'border-gray-200 dark:border-gray-600'">
                                    <LuSmartphone class="w-4 h-4 rotate-90" />
                                    Landscape
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Settings -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <LuSlidersHorizontal class="w-4 h-4" />
                            Advanced Settings
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="text-xs text-gray-500 dark:text-gray-400 block mb-1">Font Size</label>
                                <input v-model.number="customFontSize" type="number" min="6" max="20"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm" />
                            </div>
                            <div v-if="hasBarcodeMode">
                                <label class="text-xs text-gray-500 dark:text-gray-400 block mb-1">Barcode Height</label>
                                <input v-model.number="customBarcodeHeight" type="number" min="12" :max="maxBarcodeHeightPx"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm" />
                            </div>
                            <div v-if="hasQrMode">
                                <label class="text-xs text-gray-500 dark:text-gray-400 block mb-1">QR Size</label>
                                <input v-model.number="customQRSize" type="number" min="20" :max="maxQrSizePx"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm" />
                            </div>
                            <div>
                                <label class="text-xs text-gray-500 dark:text-gray-400 block mb-1">Rotation</label>
                                <custom-dropdown :value="rotationDeg" @selectedChange="rotationDeg = $event"
                                    :options="[{name: 0, label: '0°'}, {name: 90, label: '90°'}, {name: 180, label: '180°'}, {name: 270, label: '270°'}]" />
                            </div>
                        </div>
                    </div>

                    <!-- Generate Button -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button @click="prevStep" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            Back
                        </button>
                        <button @click="buildLabels"
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                            <LuSparkles class="w-4 h-4" />
                            Generate Preview
                        </button>
                    </div>
                </div>

                <!-- Preview Tab -->
                <div v-show="activeTab === 'preview'" class="p-4 sm:p-6 space-y-6">
                    <div v-if="!previewReady" class="text-center py-12">
                        <LuEyeOff class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" />
                        <p class="text-gray-500 dark:text-gray-400">Generate a preview first</p>
                    </div>

                    <template v-else>
                        <!-- Label Preview Grid -->
                        <div class="bg-gray-100 dark:bg-gray-900 rounded-xl p-4 sm:p-8 overflow-x-auto">
                            <div class="flex flex-wrap justify-center gap-4">
                                <LabelCard
                                    v-for="label in labels"
                                    :key="label.key"
                                    :label="label"
                                    :print-mode="printMode"
                                    :label-font-size="labelFontSize"
                                    :qr-size="qrSize"
                                    :barcode-height="barcodeHeight"
                                    :barcode-module-width="getBarcodeModuleWidth(label.item?.barcode)"
                                    :card-style="cardStyle"
                                    :card-inner-style="cardInnerStyle"
                                />
                            </div>
                            <div v-if="labels.length > 12" class="text-center mt-4 text-sm text-gray-500 dark:text-gray-400">
                                Showing 12 of {{ labels.length }} labels
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row justify-end gap-3">
                            <button @click="prevStep" class="px-4 py-2.5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                Back to Settings
                            </button>
                            <button @click="exportPdf" :disabled="exporting"
                                class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors flex items-center justify-center gap-2 disabled:opacity-50">
                                <LuFileDown v-if="!exporting" class="w-4 h-4" />
                                <LuLoader2 v-else class="w-4 h-4 animate-spin" />
                                {{ exporting ? 'Exporting...' : 'Export PDF' }}
                            </button>
                            <button @click="printLabels"
                                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                                <LuPrinter class="w-4 h-4" />
                                Print Labels
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Mobile Content Areas -->
            <div class="md:hidden space-y-4">
                <!-- Mobile Items View -->
                <div v-if="activeTab === 'items'" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 space-y-4">
                    <div class="relative">
                        <LuSearch class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <input v-model="search" type="text" placeholder="Search items..."
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm" />
                    </div>
                    
                    <custom-dropdown :value="categoryId" @selectedChange="onCategoryChange($event)" :options="categoryOptions"
                        placeholder="All Categories" class="w-full" />

                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" :checked="allSelected" :indeterminate="someSelected" @change="toggleAll"
                                class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500" />
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ allSelected ? 'Deselect All' : 'Select All' }}
                            </span>
                        </div>
                        <span class="text-sm text-gray-500">{{ selectedCount }} selected</span>
                    </div>

                    <div v-if="loading" class="flex justify-center py-8">
                        <LuLoader2 class="w-6 h-6 text-blue-600 animate-spin" />
                    </div>

                    <div v-else-if="filteredItems.length === 0" class="text-center py-8 text-gray-500">
                        No items found
                    </div>

                    <div v-else class="space-y-2">
                        <div v-for="item in filteredItems" :key="itemKey(item)" 
                            class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg"
                            :class="{ 'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-700': selected[itemKey(item)] }">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" :disabled="!item.barcode" :checked="!!selected[itemKey(item)]" 
                                    @change="toggleItem(item)"
                                    class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 mt-1 disabled:opacity-50" />
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900 dark:text-white text-sm">{{ item.name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ item.brand }}</div>
                                    <div v-if="item.barcode" class="mt-1 font-mono text-xs bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded inline-block">
                                        {{ item.barcode }}
                                    </div>
                                    <div v-else class="mt-1 text-xs text-red-500 flex items-center gap-1">
                                        <LuAlertCircle class="w-3 h-3" />
                                        No barcode
                                    </div>
                                    
                                    <div v-if="selected[itemKey(item)]" class="mt-2 flex items-center gap-2">
                                        <label class="text-xs text-gray-500">Qty:</label>
                                        <input type="number" min="1" :value="selected[itemKey(item)]?.qty ?? 1"
                                            @input="updateQty(itemKey(item), $event.target.value)"
                                            class="w-16 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-white" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button @click="nextStep" :disabled="selectedCount === 0"
                        class="w-full py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                        Continue
                        <LuArrowRight class="w-4 h-4" />
                    </button>
                </div>

                <!-- Mobile Settings View -->
                <div v-if="activeTab === 'settings'" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 space-y-4">
                    <div class="space-y-3">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Print Mode</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button v-for="mode in printModeOptions" :key="mode.name" @click="printMode = mode.name"
                                class="flex flex-col items-center gap-1 p-2 border-2 rounded-lg transition-all text-xs"
                                :class="printMode === mode.name ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 text-blue-700' : 'border-gray-200 dark:border-gray-600'">
                                <component :is="mode.icon" class="w-5 h-5" />
                                <span class="font-medium">{{ mode.label }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Label Size</label>
                        <custom-dropdown :value="sizeTemplate" @selectedChange="sizeTemplate = $event" :options="sizeTemplates"
                            class="w-full" />
                        <div v-if="isCustomSize" class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-xs text-gray-500">Height (cm)</label>
                                <input v-model.number="customHeightCm" type="number" min="0.5" step="0.1"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm" />
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Width (cm)</label>
                                <input v-model.number="customWidthCm" type="number" min="0.5" step="0.1"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm" />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button @click="layoutMode = 'single'"
                            class="px-4 py-2 border-2 rounded-lg text-sm font-medium transition-colors"
                            :class="layoutMode === 'single' ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 text-blue-700' : 'border-gray-200 dark:border-gray-600'">
                            Single Label
                        </button>
                        <button @click="layoutMode = 'sheet'"
                            class="px-4 py-2 border-2 rounded-lg text-sm font-medium transition-colors"
                            :class="layoutMode === 'sheet' ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 text-blue-700' : 'border-gray-200 dark:border-gray-600'">
                            Sheet Layout
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button @click="orientation = 'portrait'"
                            class="px-4 py-2 border-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2"
                            :class="orientation === 'portrait' ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 text-blue-700' : 'border-gray-200 dark:border-gray-600'">
                            <LuSmartphone class="w-4 h-4" />
                            Portrait
                        </button>
                        <button @click="orientation = 'landscape'"
                            class="px-4 py-2 border-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2"
                            :class="orientation === 'landscape' ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 text-blue-700' : 'border-gray-200 dark:border-gray-600'">
                            <LuSmartphone class="w-4 h-4 rotate-90" />
                            Landscape
                        </button>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-3">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white flex items-center gap-2">
                            <LuSlidersHorizontal class="w-4 h-4" />
                            Advanced
                        </h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-500 dark:text-gray-400 block mb-1">Font Size</label>
                                <input v-model.number="customFontSize" type="number" min="6" max="20"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm" />
                            </div>
                            <div v-if="hasBarcodeMode">
                                <label class="text-xs text-gray-500 dark:text-gray-400 block mb-1">Barcode Height</label>
                                <input v-model.number="customBarcodeHeight" type="number" min="12"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm" />
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button @click="prevStep" class="flex-1 px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            Back
                        </button>
                        <button @click="buildLabels"
                            class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                            <LuSparkles class="w-4 h-4" />
                            Preview
                        </button>
                    </div>
                </div>

                <!-- Mobile Preview View -->
                <div v-if="activeTab === 'preview'" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 space-y-4">
                    <div v-if="!previewReady" class="text-center py-8">
                        <LuEyeOff class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" />
                        <p class="text-gray-500 dark:text-gray-400">Generate a preview first</p>
                    </div>

                    <template v-else>
                        <div class="bg-gray-100 dark:bg-gray-900 rounded-lg p-4 overflow-x-auto">
                            <div class="flex flex-wrap justify-center gap-3">
                                <div v-for="label in labels.slice(0, 6)" :key="label.key"
                                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
                                    :style="{ width: '140px', height: 'auto', aspectRatio: `${resolvedWidthCm}/${resolvedHeightCm}` }"
                                    @click="openLabelModal(label)">
                                    <div class="p-2 h-full flex flex-col justify-between" :style="cardInnerStyle">
                                        <div class="text-center" :style="{ fontSize: `${Math.max(8, labelFontSize - 2)}px` }">
                                            <div class="font-bold text-gray-900 dark:text-white truncate">{{ label.item.name }}</div>
                                            <div class="text-gray-600 dark:text-gray-400 text-[10px] truncate">{{ label.item.brand }}</div>
                                        </div>
                                        <div class="flex flex-col items-center gap-1">
                                            <svg v-if="printMode !== 'qr'" :id="`barcode-${label.key}`" class="w-full" style="height: 20px;"></svg>
                                            <qrcode-vue v-if="printMode !== 'barcode'" :value="label.equipmentUrl" :size="Math.min(60, qrSize)" level="M"
                                                render-as="canvas" class="mx-auto" />
                                            <div v-if="printMode !== 'qr'" class="text-center font-mono text-[10px] text-gray-600 dark:text-gray-400">
                                                {{ label.item.barcode }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="labels.length > 6" class="text-center mt-3 text-xs text-gray-500">
                                Showing 6 of {{ labels.length }} labels
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <button @click="prevStep" class="px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors text-sm">
                                Back
                            </button>
                            <button @click="exportPdf" :disabled="exporting"
                                class="px-4 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors flex items-center justify-center gap-2 disabled:opacity-50 text-sm">
                                <LuFileDown v-if="!exporting" class="w-4 h-4" />
                                <LuLoader2 v-else class="w-4 h-4 animate-spin" />
                                PDF
                            </button>
                        </div>
                        <button @click="printLabels"
                            class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                            <LuPrinter class="w-4 h-4" />
                            Print {{ totalLabels }} Labels
                        </button>
                    </template>
                </div>
            </div>
        </div>
        </div>

        <!-- Print Areas (Hidden) -->
        <Teleport to="body">
            <div v-if="previewReady && layoutMode === 'single'" class="print-area flex flex-wrap justify-center gap-4 hidden">
                <LabelCard
                    v-for="label in labels"
                    :key="label.key"
                    :label="label"
                    :print-mode="printMode"
                    :label-font-size="labelFontSize"
                    :qr-size="qrSize"
                    :barcode-height="barcodeHeight"
                    :barcode-module-width="getBarcodeModuleWidth(label.item?.barcode)"
                    :card-style="cardStyle"
                    :card-inner-style="cardInnerStyle"
                />
            </div>
            
            <div v-if="previewReady && layoutMode === 'sheet'" class="print-area-sheet hidden">
                <div v-for="(sheet, sheetIndex) in sheetedLabels" :key="`print-sheet-${sheetIndex}`" class="sheet-page" 
                    :style="{ width: `${sheetDimensions.widthCm}cm`, height: `${sheetDimensions.heightCm}cm`, padding: `${sheetMarginCm}cm` }">
                    <div class="sheet-grid" :style="{ display: 'grid', gridTemplateColumns: `repeat(${labelsPerRow}, 1fr)`, gap: '5px' }">
                        <LabelCard
                            v-for="label in sheet"
                            :key="label.key"
                            :label="label"
                            :print-mode="printMode"
                            :label-font-size="labelFontSize"
                            :qr-size="qrSize"
                            :barcode-height="barcodeHeight"
                            :barcode-module-width="getBarcodeModuleWidth(label.item?.barcode)"
                            :card-style="cardStyle"
                            :card-inner-style="cardInnerStyle"
                        />
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Label Detail Modal -->
        <DialogModal :show="showLabelModal" @close="closeLabelModal" max-width="2xl">
            <template #content>
                <div class="flex justify-center items-center py-8" v-if="selectedLabelForModal">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden"
                        :style="{ width: `${resolvedWidthCm * 3}cm`, height: `${resolvedHeightCm * 3}cm`, border: '1px solid #e5e7eb' }">
                        <div class="h-full flex flex-col justify-between p-4" :style="cardInnerStyle">
                            <div :style="{ fontSize: `${labelFontSize * 2.5}px` }">
                                <div class="font-bold text-gray-900">{{ selectedLabelForModal.item.name }}</div>
                                <div class="text-gray-600" :style="{ fontSize: `${labelFontSize * 2}px` }">
                                    {{ selectedLabelForModal.item.brand }} {{ selectedLabelForModal.item.description ? '(' + selectedLabelForModal.item.description + ')' : '' }}
                                </div>
                            </div>
                            
                            <div class="flex flex-col items-center">
                                <svg v-if="printMode !== 'qr'" :id="'modal-barcode-' + selectedLabelForModal.key" 
                                    :style="{ width: '100%', height: `${modalBarcodeHeight}px` }"></svg>
                                <qrcode-vue v-if="printMode !== 'barcode'" :value="selectedLabelForModal.equipmentUrl" 
                                    :size="modalQRSize" level="M" render-as="canvas" />
                                <div v-if="printMode !== 'qr'" class="font-mono text-gray-600 mt-1" 
                                    :style="{ fontSize: `${labelFontSize * 3}px` }">{{ selectedLabelForModal.item.barcode }}</div>
                                <div v-else class="label-qr-caption" :style="{ fontSize: `${labelFontSize * 2.6}px` }">
                                    {{ selectedLabelForModal.item.barcode }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template #footer>
                <button @click="closeLabelModal" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    Close
                </button>
            </template>
        </DialogModal>
    </AppLayout>
</template>

<style>
.label-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.label-card {
    border: 1px dashed #cbd5f5;
    border-radius: 6px;
    padding: 0.2rem;
    box-sizing: border-box;
    background: #ffffff;
    overflow: hidden;
}

.label-card-inner {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
    gap: 2px;
}

.label-text {
    font-size: 10px;
    color: #111827;
    line-height: 1.2;
}

.label-item {
    font-weight: 700;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.label-brand {
    color: #111827;
    font-weight: 600;
}

.label-barcode {
    font-size: 10px;
    text-align: center;
    color: #1f2937;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 600;
}

.label-card svg {
    width: 100%;
    height: 30px;
    display: block;
}

.label-qr {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    max-width: 100%;
    max-height: 100%;
    overflow: hidden;
}

.label-qr :deep(canvas),
.label-qr :deep(svg) {
    max-width: 100%;
    max-height: 100%;
    display: block;
}

.label-qr-caption {
    font-size: 9px;
    font-weight: 600;
    text-align: center;
    color: #111827;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sheet-page {
    background: #ffffff;
    box-sizing: border-box;
    page-break-after: always;
    break-after: page;
}

.sheet-grid {
    width: 100%;
    height: 100%;
}

.print-area-sheet {
    display: flex;
    flex-direction: column;
}

@media print {
    :global(body), :global(html) {
        margin: 0 !important;
        padding: 0 !important;
    }

    :global(body > *:not(.print-area):not(.print-area-sheet)) {
        display: none !important;
    }

    .print-area, .print-area-sheet {
        position: absolute;
        left: 0;
        top: 0;
        margin: 0;
        padding: 0;
        display: block !important;
        visibility: visible !important;
    }

    .no-print {
        display: none !important;
    }

    .label-grid {
        gap: 0;
        display: block;
        margin: 0;
        padding: 0;
    }

    .label-card {
        page-break-after: always;
        break-after: page;
        page-break-inside: avoid;
        break-inside: avoid-page;
        width: 100vw !important;
        height: 100vh !important;
        max-width: 100vw !important;
        max-height: 100vh !important;
        border: none !important;
        border-radius: 0;
        padding: 0.2rem;
        margin: 0 !important;
        overflow: hidden;
        box-shadow: none;
    }

    .label-card:last-child {
        page-break-after: auto;
        break-after: auto;
    }

    .label-card-inner {
        overflow: hidden;
        justify-content: space-between;
    }

    .label-text,
    .label-barcode,
    .label-qr-caption {
        flex-shrink: 0;
    }

    .label-qr {
        overflow: hidden;
        max-width: 100%;
        max-height: 100%;
    }

    .print-area-sheet {
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    .sheet-page {
        width: 100% !important;
        height: 100% !important;
        page-break-after: always;
        break-after: page;
        page-break-inside: avoid;
        break-inside: avoid-page;
        margin: 0 !important;
        background: #ffffff;
        box-sizing: border-box;
    }

    .sheet-page:last-child {
        page-break-after: auto;
        break-after: auto;
    }

    .sheet-grid {
        width: 100% !important;
        height: 100% !important;
        display: grid !important;
        gap: 0 !important;
    }

    .sheet-page .label-card {
        page-break-inside: avoid;
        break-inside: avoid-page;
        page-break-after: auto;
        break-after: auto;
        border: none !important;
        border-radius: 0;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        height: 100% !important;
        box-sizing: border-box;
    }
}
</style>
