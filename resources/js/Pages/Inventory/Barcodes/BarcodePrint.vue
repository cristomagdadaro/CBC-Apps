<script>
import {Head} from "@inertiajs/vue3";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Transaction from "@/Modules/domain/Transaction";
import JsBarcode from "jsbarcode";

export default {
    name: "BarcodePrint",
    components: {Head},
    mixins: [ApiMixin],
    data() {
        return {
            items: [],
            loading: false,
            search: "",
            selected: {},
            labels: [],
            previewReady: false,
            exporting: false,
        };
    },
    computed: {
        filteredItems() {
            const term = this.search?.toLowerCase()?.trim();
            if (!term) return this.items;
            return this.items.filter(item => {
                return [item.name, item.brand, item.description, item.barcode]
                    .filter(Boolean)
                    .some(value => value.toLowerCase().includes(term));
            });
        },
        allSelected() {
            const selectable = this.filteredItems.filter(item => !!item.barcode);
            if (!selectable.length) return false;
            return selectable.every(item => this.selected[this.itemKey(item)]);
        },
        selectedCount() {
            return Object.values(this.selected).filter(Boolean).length;
        },
    },
    methods: {
        itemKey(item) {
            return `${item.item_id}-${item.barcode}-${item.unit}`;
        },
        async loadItems() {
            this.loading = true;
            this.model = new Transaction();
            this.setFormAction('get');
            this.form.per_page = '*';
            this.form.sort = 'name';
            this.form.order = 'asc';

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
            this.labels.forEach(label => {
                const el = document.getElementById(`barcode-${label.key}`);
                if (!el || !label.item?.barcode) return;
                JsBarcode(el, label.item.barcode, {
                    format: "CODE128",
                    displayValue: false,
                    width: 1.7,
                    height: 30,
                    margin: 0,
                });
            });
        },
        printLabels() {
            if (!this.previewReady) return;
            window.print();
        },
        async exportPdf() {
            if (!this.previewReady || this.exporting) return;
            this.exporting = true;
            try {
                const labels = this.labels.map(label => {
                    const svgEl = document.getElementById(`barcode-${label.key}`);
                    return {
                        name: label.item?.name ?? '',
                        brand: label.item?.brand ?? 'N/A',
                        barcode: label.item?.barcode ?? '',
                        svg: svgEl ? svgEl.outerHTML : '',
                    };
                });

                const response = await this.fetchPostApi('inventory.barcodes.pdf', { labels }, {
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
        this.loadItems();
    },
};
</script>

<template>
    <Head title="Print Barcodes" />

    <AppLayout title="Barcode Printing">
        <template #header>
            <div class="no-print flex flex-col gap-2">
                <h2 class="text-xl font-semibold text-gray-800">Barcode Printing</h2>
                <p class="text-sm text-gray-500">Select items and print labels in a 5cm x 3cm layout for Intermec PD43.</p>
            </div>
        </template>

        <div class="py-8 px-4 max-w-6xl mx-auto space-y-6">
            <div class="no-print bg-white shadow rounded-lg p-4 space-y-4">
                <div class="flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
                    <div class="flex-1">
                        <text-input
                            v-model="search"
                            placeholder="Search item, brand, or barcode"
                        />
                    </div>
                    <div class="flex items-center gap-2">
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
                        <submit-btn :disabled="!previewReady || exporting" @click="exportPdf">
                            {{ exporting ? 'Exporting...' : 'Export PDF' }}
                        </submit-btn>
                        <submit-btn :disabled="!previewReady" @click="printLabels">
                            Print Labels
                        </submit-btn>
                    </div>
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
                                <td class="py-2">{{ item.unit }}</td>
                                <td class="py-2">{{ item.remaining_quantity }}</td>
                                <td class="py-2">
                                    <input
                                        type="number"
                                        min="1"
                                        class="w-20 px-2 py-1 border rounded"
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

            <div v-if="previewReady" class="print-area">
                <div class="label-grid">
                    <div v-for="label in labels" :key="label.key" class="label-card">
                        <div class="label-text">
                            <div class="label-item">{{ label.item.name }} {{ label.item.description ? '(' + label.item.description + ')' : '' }}</div>
                            <div class="label-brand">{{ label.item.brand }}</div>
                        </div>
                        <svg :id="`barcode-${label.key}`"></svg>
                        <div class="label-barcode">{{ label.item.barcode }}</div>
                    </div>
                </div>
            </div>
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
    width: 5cm;
    height: 3cm;
    border: 1px dashed #cbd5f5;
    border-radius: 6px;
    padding: 4px;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    background: #ffffff;
    overflow: hidden;
}

.label-text {
    font-size: 10px;
    color: #111827;
    line-height: 1.2;
}

.label-item {
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.label-brand {
    color: #64748b;
    font-size: 9px;
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

@media print {
    :global(body) {
        margin: 0;
    }

    :global(body *) {
        visibility: hidden;
    }

    .print-area,
    .print-area * {
        visibility: visible;
    }

    .print-area {
        position: absolute;
        left: 0;
        top: 0;
    }

    .no-print {
        display: none;
    }

    .label-grid {
        gap: 0;
    }

    .label-card {
        page-break-after: always;
        border: none;
        border-radius: 0;
        width: 5cm;
        height: 3cm;
        padding: 0.1cm;
    }

    @page {
        size: 5cm 3cm;
        margin: 0;
    }
}
</style>
