<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Transaction from "@/Modules/domain/Transaction";
import JsBarcode from "jsbarcode";
import QrcodeVue from "qrcode.vue";
import CustomDropdown from '@/Components/CustomDropdown/CustomDropdown.vue';
import DialogModal from "@/Components/DialogModal.vue";
import TextInput from '@/Components/TextInput.vue';

export default {
    name: "BarcodePrint",
    components: { QrcodeVue, CustomDropdown, TextInput },
    mixins: [ApiMixin],
    data() {
        return {
            items: [],
            loading: false,
            search: "",
            categoryId: 7, // Default to "Laboratory Equipment" category
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
        };
    },
    computed: {
        sizeTemplates() {
            return [
                { key: "3x5", heightCm: 3, widthCm: 5, label: "3cm x 5cm", name: "3x5" },
                { key: "4.8x5.5", heightCm: 4.8, widthCm: 5.5, label: "4.8cm x 5.5cm", name: "4.8x5.5" },
                { key: "8x5", heightCm: 8, widthCm: 5, label: "8cm x 5cm", name: "8x5" },
                { key: "1.5x6", heightCm: 1.5, widthCm: 6, label: "1.5cm x 6cm", name: "1.5x6" },
                { key: "custom", heightCm: null, widthCm: null, label: "Custom", name: "custom" },
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
            const height = this.normalizeSize(this.customBarcodeHeight, 30);
            return Math.max(12, Math.min(height, this.maxBarcodeHeightPx));
        },
        qrSize() {
            const size = this.normalizeSize(this.customQRSize, 60);
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
                return [item.name, item.brand, item.description, item.barcode]
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
        selectedCount() {
            return Object.values(this.selected).filter(Boolean).length;
        },
        sheetDimensions() {
            // A4: 21cm x 29.7cm, Folio: 21.6cm x 33cm
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
    },
    watch: {
        printMode() {
            this.$nextTick(() => {
                this.renderBarcodes();
                this.renderModalBarcode();
            });
        },
        barcodeHeight() {
            this.$nextTick(() => {
                this.renderBarcodes();
                this.renderModalBarcode();
            });
        },
    },
    methods: {
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
                width: 1.7,
                height: this.modalBarcodeHeight,
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

            const path = `/laboratory/equipments/${encodeURIComponent(barcode)}`;
            if (typeof window === "undefined") {
                return path;
            }

            return `${window.location.origin}${path}`;
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

            this.$nextTick(() => {
                this.renderBarcodes();
            });
        },
        renderBarcodes() {
            if (!this.hasBarcodeMode) return;

            this.labels.forEach(label => {
                const barcodeValue = label.item?.barcode;
                if (!barcodeValue) return;

                // 1. Render to On-Screen Preview
                const previewEl = document.getElementById(`barcode-${label.key}`);
                if (previewEl) {
                    JsBarcode(previewEl, barcodeValue, {
                        format: "CODE128",
                        displayValue: false,
                        width: 1.7,
                        height: this.barcodeHeight,
                        margin: 0,
                    });
                }

                // 2. Render to the Print (Teleport) DOM
                const printEl = document.getElementById(`print-barcode-${label.key}`);
                if (printEl) {
                    JsBarcode(printEl, barcodeValue, {
                        format: "CODE128",
                        displayValue: false,
                        width: 1.7,
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
    },
    mounted() {
        this.applyPrintPageSize();
        this.loadItems();
    },
};
</script>

<template>
    <Head title="Print Barcodes" />

    <AppLayout title="Barcode Printing">
        <template #header>
            <ActionHeaderLayout title="Barcode Printing" subtitle="Select items and print labels in a 5cm x 3cm layout for Intermec PD43." />
        </template>

        <div class="py-8 px-4 mx-auto space-y-6 flex gap-5">
            <div class="no-print bg-white shadow rounded-lg p-4 space-y-4">
                <p>
                    <span class="text-sm text-gray-700">Tips for Intermec PD43 Printer</span>
                    <ul>
                        <li class="text-xs text-gray-500">- Always recalibrate the printer after turning it on or changing the label roll. <b>Go to printer Wizards>Calibrate>Media</b>.</li>
                        <li class="text-xs text-gray-500">- Make sure the selected Size Template matches the actual label size loaded in the printer for best results.</li>
                        <li class="text-xs text-gray-500">- Use the "Select Filtered" button to quickly select all items that match your search and category filters.</li>
                        <li class="text-xs text-gray-500">- For Laboratory Logger only Laboratory Equipments will work.</li>
                    </ul>
                </p>
                <div class="grid grid-cols-6 grid-rows-4 gap-2">
                        <div class="col-span-6">
                            <text-input
                                v-model="search"
                                placeholder="Search item, brand, or barcode"
                            />
                        </div>
                        <custom-dropdown :value="categoryId" @selectedChange="onCategoryChange($event)" :options="categoryOptions" label="Category" placeholder="Filter by category" :withAllOption="false" />
                        <custom-dropdown :value="printMode" @selectedChange="printMode = $event" :options="[{name:'barcode', label:'Barcode'}, {name:'qr', label:'QR Code'}, {name:'both', label:'Both'}]" label="Mode" placeholder="Select a mode" :withAllOption="false" />
                        <custom-dropdown :value="sizeTemplate" @selectedChange="sizeTemplate = $event" :options="sizeTemplates" label="Size Template" placeholder="Select a size template" :withAllOption="false" />
                        <custom-dropdown :value="layoutMode" @selectedChange="layoutMode = $event" :options="[{name:'single', label:'Single Page'}, {name:'sheet', label:'Sheet Layout'}]" label="Layout" placeholder="Select layout mode" :withAllOption="false" />
                        
                        <div v-if="layoutMode === 'sheet'" class="flex items-center gap-2">
                            <custom-dropdown :value="sheetSize" @selectedChange="sheetSize = $event" :options="[{name:'a4', label:'A4 (21×29.7cm)'}, {name:'folio', label:'Folio (21.6×33cm)'}]" label="Sheet Size" placeholder="Select sheet size" :withAllOption="false" />
                        </div>

                         <div v-if="layoutMode === 'sheet'" class="flex items-center gap-2">
                            <text-input v-model.number="sheetMarginCm" type="number" min="0" max="2" step="0.1" :label="'Margin(cm) ' + labelsPerRow + ' × ' + labelsPerColumn + ' = ' + labelsPerSheet + '/sheet'" placeholder="Margin (cm)" />
                            <div class="text-xs text-gray-600 ml-2"></div>
                        </div>
                        
                        <div v-if="isCustomSize" class="flex items-center gap-2">
                            <label class="text-xs text-gray-500">H(cm)</label>
                            <input v-model.number="customHeightCm" type="number" min="0.5" step="0.1" class="w-20 px-2 py-1 border rounded text-sm" />
                            <label class="text-xs text-gray-500">W(cm)</label>
                            <input v-model.number="customWidthCm" type="number" min="0.5" step="0.1" class="w-20 px-2 py-1 border rounded text-sm" />
                        </div>

                        <custom-dropdown :value="orientation" @selectedChange="orientation = $event" :options="[{name:'portrait', label:'Portrait'}, {name:'landscape', label:'Landscape'}]" label="Orientation" placeholder="Select orientation" :withAllOption="false" />
                        <custom-dropdown :value="rotationDeg" @selectedChange="rotationDeg = $event" :options="[{name:0, label:'0°'}, {name:90, label:'90°'}, {name:180, label:'180°'}, {name:270, label:'270°'}]" label="Rotate" placeholder="Rotate" :withAllOption="false" />

                        <text-input v-model.number="customFontSize" type="number" min="6" max="20" step="1" label="Font Size" placeholder="Font Size (px)" />
                        <text-input v-if="hasBarcodeMode" v-model.number="customBarcodeHeight" type="number" min="12" :max="maxBarcodeHeightPx" step="1" label="Barcode Height (px)" placeholder="Barcode Height (px)" />

                        <text-input v-if="hasQrMode" v-model.number="customQRSize" type="number" min="20" :max="maxQrSizePx" step="1" label="QR Size (px)" placeholder="QR Size (px)" />

                        <div class="flex gap-5 items-center">
                            <label class="inline-flex items-center gap-1 text-sm text-gray-600">
                                <input v-model="flipHorizontal" type="checkbox" /> Flip X
                            </label>
                            <label class="inline-flex items-center gap-1 text-sm text-gray-600">
                                <input v-model="flipVertical" type="checkbox" /> Flip Y
                            </label>
                        </div>

                        <button
                            type="button"
                            class="px-3 py-2 text-sm rounded border border-gray-300 text-gray-700 hover:bg-gray-50"
                            @click="toggleAll"
                        >
                            {{ allSelected ? 'Clear Selection' : 'Select Filtered' }}
                        </button>
                        <submit-btn :disabled="selectedCount === 0" @click="buildLabels">
                            Generate Preview ({{ selectedCount }})
                        </submit-btn>
                        <submit-btn :disabled="!previewReady || exporting" @click="exportPdf" class="hidden">
                            {{ exporting ? 'Exporting...' : 'Export PDF' }}
                        </submit-btn>
                        <submit-btn :disabled="!previewReady" @click="printLabels">
                            Print Labels
                        </submit-btn>
                    </div>
                <div v-if="loading" class="text-sm text-gray-500">Loading items...</div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2">Select</th>
                                <th class="py-2">Item</th>
                                <th class="py-2">Brand</th>
                                <th class="py-2">Barcode</th>
                                <th class="py-2">PRRI Barcode</th>
                                <th class="py-2">Unit</th>
                                <th class="py-2">Remaining</th>
                                <th class="py-2">Qty to Print</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in filteredItems" :key="itemKey(item)" class="border-b">
                                <td class="py-2">
                                    <input
                                        type="checkbox"
                                        :disabled="!item.barcode"
                                        :checked="!!selected[itemKey(item)]"
                                        @change="toggleItem(item)"
                                    />
                                </td>
                                <td class="py-2">
                                    <div class="font-medium text-gray-800">{{ item.name }}</div>
                                    <div class="text-xs text-gray-500" v-if="item.description">{{ item.description }}</div>
                                </td>
                                <td class="py-2">{{ item.brand }}</td>
                                <td class="py-2">
                                    <span v-if="item.barcode">{{ item.barcode }}</span>
                                    <span v-else class="text-xs text-red-500">No barcode</span>
                                </td>
                                <td class="py-2">
                                    <span v-if="item.barcode_prri">{{ item.barcode_prri }}</span>
                                    <span v-else class="text-xs text-red-500">No PRRI barcode</span>
                                </td>
                                <td class="py-2">{{ item.unit }}</td>
                                <td class="py-2">{{ item.remaining_quantity }}</td>
                                <td class="py-2">
                                    <input
                                        type="number"
                                        min="1"
                                        class="w-20 px-2 py-1 border rounded disabled:bg-gray-100 disabled:border-gray-200 disabled:text-gray-500"
                                        :disabled="!selected[itemKey(item)]"
                                        :value="selected[itemKey(item)]?.qty ?? 1"
                                        @input="updateQty(itemKey(item), $event.target.value)"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div v-if="previewReady && layoutMode === 'single'" class="print-area">
                <div class="label-grid">
                    <div v-for="label in labels" :key="label.key" class="label-card cursor-pointer" :style="cardStyle" @dblclick="openLabelModal(label)" title="Double-click to enlarge">
                        <div class="label-card-inner" :style="cardInnerStyle">
                            <div class="label-text" :style="{ fontSize: `${labelFontSize}px` }">
                                <div class="label-item">{{ label.item.name }} {{ label.item.description ? '(' + label.item.description + ')' : '' }}</div>
                                <div class="label-brand">{{ label.item.brand }}</div>
                            </div>
                            <svg v-if="printMode !== 'qr'" :id="`barcode-${label.key}`"></svg>
                            <qrcode-vue v-if="printMode !== 'barcode'" :key="`preview-qr-${label.key}-${qrSize}-${printMode}`" :value="label.equipmentUrl" :size="qrSize" level="M" render-as="canvas" class="label-qr mx-auto" />
                            <div v-if="printMode !== 'qr'" class="label-barcode mx-auto" :style="{ fontSize: `${labelFontSize}px` }">{{ label.item.barcode }}</div>
                            <div v-else class="label-qr-caption" :style="{ fontSize: `${labelFontSize * 0.9}px` }">{{ label.item.barcode }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-if="previewReady && layoutMode === 'sheet'" class="print-area-sheet">
                <div v-for="(sheet, sheetIndex) in sheetedLabels" :key="`sheet-${sheetIndex}`" class="sheet-page" :style="{ width: `${sheetDimensions.widthCm}cm`, height: `${sheetDimensions.heightCm}cm`, padding: `${sheetMarginCm}cm` }">
                    <div class="sheet-grid" :style="{ display: 'grid', gridTemplateColumns: `repeat(${labelsPerRow}, 1fr)`, gap: '0px' }">
                        <div v-for="label in sheet" :key="label.key" class="label-card cursor-pointer" :style="cardStyle" @dblclick="openLabelModal(label)" title="Double-click to enlarge">
                            <div class="label-card-inner" :style="cardInnerStyle">
                                <div class="label-text" :style="{ fontSize: `${labelFontSize}px` }">
                                    <div class="label-item">{{ label.item.name }} {{ label.item.description ? '(' + label.item.description + ')' : '' }}</div>
                                    <div class="label-brand">{{ label.item.brand }}</div>
                                </div>
                                <svg v-if="printMode !== 'qr'" :id="`barcode-${label.key}`"></svg>
                                <qrcode-vue v-if="printMode !== 'barcode'" :key="`sheet-qr-${label.key}-${qrSize}-${printMode}`" :value="label.equipmentUrl" :size="qrSize" level="M" render-as="canvas" class="label-qr mx-auto" />
                                <div v-if="printMode !== 'qr'" class="label-barcode mx-auto" :style="{ fontSize: `${labelFontSize}px` }">{{ label.item.barcode }}</div>
                                <div v-else class="label-qr-caption" :style="{ fontSize: `${labelFontSize * 0.9}px` }">{{ label.item.barcode }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <Teleport to="body">
                <div v-if="previewReady && layoutMode === 'single'" class="print-area">
                    <div class="label-grid">
                        <div v-for="label in labels" :key="label.key" class="label-card" :style="cardStyle">
                            <div class="label-card-inner" :style="cardInnerStyle">
                                <div class="label-text" :style="{ fontSize: `${labelFontSize}px` }">
                                    <div class="label-item">{{ label.item.name }}</div>
                                    <div class="label-brand">{{ label.item.brand }} {{ label.item.description ? '(' + label.item.description + ')' : '' }}</div>
                                </div>
                                <svg v-if="printMode !== 'qr'" :id="`print-barcode-${label.key}`"></svg>
                                <qrcode-vue v-if="printMode !== 'barcode'" :key="`print-preview-qr-${label.key}-${qrSize}-${printMode}`" :value="label.equipmentUrl" :size="qrSize" level="M" render-as="canvas" class="label-qr mx-auto" />
                                <div v-if="printMode !== 'qr'" class="label-barcode mx-auto" :style="{ fontSize: `${labelFontSize}px` }">{{ label.item.barcode }}</div>
                                <div v-else class="label-qr-caption" :style="{ fontSize: `${labelFontSize * 0.9}px` }">{{ label.item.barcode }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-if="previewReady && layoutMode === 'sheet'" class="print-area-sheet">
                    <div v-for="(sheet, sheetIndex) in sheetedLabels" :key="`print-sheet-${sheetIndex}`" class="sheet-page" :style="{ width: `${sheetDimensions.widthCm}cm`, height: `${sheetDimensions.heightCm}cm`, padding: `${sheetMarginCm}cm` }">
                        <div class="sheet-grid" :style="{ display: 'grid', gridTemplateColumns: `repeat(${labelsPerRow}, 1fr)`, gap: '0px' }">
                            <div v-for="label in sheet" :key="label.key" class="label-card" :style="cardStyle">
                                <div class="label-card-inner" :style="cardInnerStyle">
                                    <div class="label-text" :style="{ fontSize: `${labelFontSize}px` }">
                                        <div class="label-item">{{ label.item.name }}</div>
                                        <div class="label-brand">{{ label.item.brand }} {{ label.item.description ? '(' + label.item.description + ')' : '' }}</div>
                                    </div>
                                    <svg v-if="printMode !== 'qr'" :id="`print-barcode-${label.key}`"></svg>
                                    <qrcode-vue v-if="printMode !== 'barcode'" :key="`print-sheet-qr-${label.key}-${qrSize}-${printMode}`" :value="label.equipmentUrl" :size="qrSize" level="M" render-as="canvas" class="label-qr mx-auto" />
                                    <div v-if="printMode !== 'qr'" class="label-barcode mx-auto" :style="{ fontSize: `${labelFontSize}px` }">{{ label.item.barcode }}</div>
                                    <div v-else class="label-qr-caption" :style="{ fontSize: `${labelFontSize * 0.9}px` }">{{ label.item.barcode }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Teleport>

            <DialogModal :show="showLabelModal" @close="closeLabelModal" >
                <template #content>
                    <div class="flex justify-center items-center" v-if="selectedLabelForModal">
                        <div
                        class="scale-75"
                            :style="{
                                width: `${resolvedWidthCm * 4}cm`,
                                height: `${resolvedHeightCm * 4}cm`,
                                aspectRatio: `${resolvedWidthCm * 4} / ${resolvedHeightCm * 4}`,
                                border: '1px solid #000000',
                                borderRadius: '6px',
                                padding: '1rem',
                                background: '#ffffff',
                            }"
                        >
                            <div class="justify-between flex flex-col h-full" :style="cardInnerStyle">
                                <div class="label-text" :style="{ fontSize: `${labelFontSize * 3}px` }">
                                    <div class="label-item">{{ selectedLabelForModal.item.name }}</div>
                                    <div class="label-brand" :style="{ fontSize: `${labelFontSize * 2.6}px` }">{{ selectedLabelForModal.item.brand }} {{ selectedLabelForModal.item.description ? '(' + selectedLabelForModal.item.description + ')' : '' }}</div>
                                </div>
                                <svg v-if="printMode !== 'qr'" :id="'modal-barcode-' + selectedLabelForModal.key" :style="{ width: '100%', height: `${modalBarcodeHeight}px`, display: 'block' }"></svg>
                                <qrcode-vue
                                    v-if="printMode !== 'barcode'"
                                    :key="`modal-qr-${selectedLabelForModal.key}-${modalQRSize}-${printMode}`"
                                    :value="selectedLabelForModal.equipmentUrl"
                                    :size="modalQRSize"
                                    level="M"
                                    render-as="canvas"
                                    class="mx-auto"
                                    style="display: flex; justify-content: center; align-items: center; width: 100%;"
                                />
                                <div v-if="printMode !== 'qr'" class="label-barcode mx-auto" :style="{ fontSize: `${labelFontSize * 4}px` }">{{ selectedLabelForModal.item.barcode }}</div>
                                <div v-else class="label-qr-caption" :style="{ fontSize: `${labelFontSize * 2.6}px` }">{{ selectedLabelForModal.item.barcode }}</div>
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
        </div>
    </AppLayout>
</template>

<style scoped>
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
    font-size: 9px;
    font-weight: 600;
}

.label-barcode {
    font-size: 10px;
    text-align: center;
    color: #1f2937;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
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

    /* Prevent hiding BOTH single and sheet print areas */
    :global(body > *:not(.print-area):not(.print-area-sheet)) {
        display: none !important;
    }

    /* Absolute positioning locks it to the physical page edges */
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

        /* OVERRIDE INLINE STYLES to prevent fractional pixel overflow */
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
