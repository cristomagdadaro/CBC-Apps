<script>
import PersonnelIdCard from "./PersonnelIdCard.vue";
export default {
    name: "PersonnelIdCardsPrint",
    components: {
        PersonnelIdCard,
    },
    props: {
        cards: {
            type: Array,
            default: () => [],
        },
        fromUrl: {
            type: String,
            default: null,
        },
    },
    data() {
        return {
            search: "",
            selected: {},
            labels: [],
            previewReady: false,
            layoutMode: "single",
            sheetSize: "a4",
            sheetMarginCm: 0.5,
            activeTab: "items",
            isMobile: false,
        };
    },
    computed: {
        resolvedWidthCm() {
            return 7.4;
        },
        resolvedHeightCm() {
            return 10.5;
        },
        cardStyle() {
            return {
                width: `${this.resolvedWidthCm}cm`,
                height: `${this.resolvedHeightCm}cm`,
            };
        },
        filteredCards() {
            const term = this.search?.toLowerCase()?.trim();
            if (!term) {
                return this.cards;
            }

            return this.cards.filter((card) => {
                return [
                    card.full_name,
                    card.employee_id,
                    card.course_program,
                    card.registration_type_label,
                ]
                    .filter(Boolean)
                    .some((value) => String(value).toLowerCase().includes(term));
            });
        },
        selectedCount() {
            return Object.values(this.selected).filter(Boolean).length;
        },
        totalLabels() {
            return this.labels.length;
        },
        allSelected() {
            return this.filteredCards.length > 0
                && this.filteredCards.every((card) => this.selected[this.cardKey(card)]);
        },
        someSelected() {
            const selectedCount = this.filteredCards.filter((card) => this.selected[this.cardKey(card)]).length;

            return selectedCount > 0 && selectedCount < this.filteredCards.length;
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
            if (this.layoutMode === "single") {
                return [];
            }

            const sheets = [];
            for (let i = 0; i < this.labels.length; i += this.labelsPerSheet) {
                sheets.push(this.labels.slice(i, i + this.labelsPerSheet));
            }

            return sheets;
        },
        currentStepValid() {
            if (this.activeTab === "items") {
                return this.selectedCount > 0;
            }

            if (this.activeTab === "settings") {
                return true;
            }

            return this.previewReady;
        },
    },
    methods: {
        checkMobile() {
            this.isMobile = window.innerWidth < 768;
        },
        cardKey(card) {
            return String(card.id ?? card.employee_id);
        },
        toggleAll() {
            if (this.allSelected) {
                this.selected = {};
                this.labels = [];
                this.previewReady = false;
                return;
            }

            const next = {};
            this.filteredCards.forEach((card) => {
                next[this.cardKey(card)] = card;
            });
            this.selected = next;
        },
        toggleCard(card) {
            const key = this.cardKey(card);
            const next = { ...this.selected };

            if (next[key]) {
                delete next[key];
            } else {
                next[key] = card;
            }

            this.selected = next;
            this.previewReady = false;
        },
        buildLabels() {
            this.labels = Object.values(this.selected)
                .filter(Boolean)
                .map((card) => ({
                    key: this.cardKey(card),
                    card,
                }));
            this.previewReady = this.labels.length > 0;
            this.activeTab = "preview";
        },
        applyPrintPageSize() {
            if (typeof document === "undefined") {
                return;
            }

            const styleId = "personnel-id-dynamic-page-size";
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
        async waitForPrintImages() {
            await this.$nextTick();

            const images = Array.from(document.querySelectorAll(".print-area img, .print-area-sheet img"));
            await Promise.all(images.map((image) => {
                if (image.complete && image.naturalWidth > 0) {
                    return Promise.resolve();
                }

                if (typeof image.decode === "function") {
                    return image.decode().catch(() => {});
                }

                return new Promise((resolve) => {
                    image.addEventListener("load", resolve, { once: true });
                    image.addEventListener("error", resolve, { once: true });
                });
            }));
        },
        async printCards() {
            if (!this.previewReady) {
                return;
            }

            this.applyPrintPageSize();
            await this.waitForPrintImages();
            window.print();
        },
        nextStep() {
            if (this.activeTab === "items" && this.selectedCount > 0) {
                this.activeTab = "settings";
                return;
            }

            if (this.activeTab === "settings") {
                this.buildLabels();
            }
        },
        prevStep() {
            if (this.activeTab === "settings") {
                this.activeTab = "items";
                return;
            }

            if (this.activeTab === "preview") {
                this.activeTab = "settings";
            }
        },
    },
    watch: {
        layoutMode() {
            this.applyPrintPageSize();
        },
        sheetSize() {
            this.applyPrintPageSize();
        },
        sheetMarginCm() {
            this.applyPrintPageSize();
        },
        cards: {
            immediate: true,
            handler(value) {
                const next = {};
                (value || []).forEach((card) => {
                    next[this.cardKey(card)] = card;
                });
                this.selected = next;
                this.labels = [];
                this.previewReady = false;
                this.activeTab = "items";
            },
        },
    },
    mounted() {
        this.checkMobile();
        window.addEventListener("resize", this.checkMobile);
        this.applyPrintPageSize();
    },
    beforeUnmount() {
        window.removeEventListener("resize", this.checkMobile);
    },
};
</script>

<template>
    <Head title="Personnel ID Cards" />

    <AppLayout title="Personnel ID Cards">
        <template #header>
            <ActionHeaderLayout
                title="Personnel ID Cards"
                subtitle="Generate and print A7 ID cards for approved Student, OJT, and Thesis personnel."
                :route-link="fromUrl || route('personnels.registrations.index')"
            >
                <button
                    type="button"
                    class="inline-flex items-center rounded bg-AB px-3 py-2 text-sm font-semibold text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!previewReady"
                    @click="printCards"
                >
                    Print IDs
                </button>
            </ActionHeaderLayout>
        </template>

        <div class="md:grid md:grid-cols-12 gap-5 p-5">
            <aside class="col-span-12 md:col-span-2 h-fit md:sticky md:top-5 md:self-start">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-2 mb-2">
                        <LuInfo class="w-5 h-5 text-emerald-700" />
                        <h4 class="font-medium text-gray-900 dark:text-white">ID Workflow</h4>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Select approved IDs, choose single-card or sheet layout, preview, then print. Each card is fixed at A7 size.
                    </p>
                </div>
            </aside>

            <main class="col-span-12 md:col-span-10 space-y-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-3 border-b border-gray-200 dark:border-gray-700">
                        <button
                            type="button"
                            class="px-4 py-3 text-sm font-semibold"
                            :class="activeTab === 'items' ? 'text-emerald-700 border-b-2 border-emerald-700 bg-emerald-50' : 'text-gray-600'"
                            @click="activeTab = 'items'"
                        >
                            Select IDs
                        </button>
                        <button
                            type="button"
                            class="px-4 py-3 text-sm font-semibold"
                            :class="activeTab === 'settings' ? 'text-emerald-700 border-b-2 border-emerald-700 bg-emerald-50' : 'text-gray-600'"
                            :disabled="selectedCount === 0"
                            @click="activeTab = 'settings'"
                        >
                            Print Settings
                        </button>
                        <button
                            type="button"
                            class="px-4 py-3 text-sm font-semibold"
                            :class="activeTab === 'preview' ? 'text-emerald-700 border-b-2 border-emerald-700 bg-emerald-50' : 'text-gray-600'"
                            :disabled="!previewReady"
                            @click="activeTab = 'preview'"
                        >
                            Preview
                        </button>
                    </div>

                    <section v-if="activeTab === 'items'" class="p-4 space-y-4">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h3 class="font-bold text-gray-900">Approved ID Cards</h3>
                                <p class="text-sm text-gray-500">{{ selectedCount }} selected from {{ cards.length }} approved IDs.</p>
                            </div>
                            <div class="flex gap-2">
                                <input
                                    v-model="search"
                                    type="search"
                                    class="rounded-lg border-gray-300 text-sm focus:border-AB focus:ring-AB"
                                    placeholder="Search IDs..."
                                />
                                <button
                                    type="button"
                                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700"
                                    :disabled="filteredCards.length === 0"
                                    @click="toggleAll"
                                >
                                    {{ allSelected ? "Clear" : "Select All" }}
                                </button>
                            </div>
                        </div>

                        <div v-if="filteredCards.length" class="flex flex-wrap gap-3">
                            <button
                                v-for="card in filteredCards"
                                :key="cardKey(card)"
                                type="button"
                                class="rounded-lg border text-left transition hover:border-emerald-400 hover:bg-emerald-50 overflow-hidden"
                                :class="selected[cardKey(card)] ? 'border-emerald-600 bg-emerald-50' : 'border-gray-200 bg-white'"
                                @click="toggleCard(card)"
                            >
                                <PersonnelIdCard :card="card" :card-style="cardStyle"/>
                            </button>
                        </div>

                        <div v-else class="rounded border border-gray-200 bg-white p-8 text-center text-gray-600">
                            No approved Student, OJT, or Thesis IDs are ready for printing.
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="button"
                                class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="selectedCount === 0"
                                @click="nextStep"
                            >
                                Continue
                            </button>
                        </div>
                    </section>

                    <section v-if="activeTab === 'settings'" class="p-4 space-y-5">
                        <div>
                            <h3 class="font-bold text-gray-900">Print Settings</h3>
                            <p class="text-sm text-gray-500">A7 card size is fixed at 7.4cm x 10.5cm.</p>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <button
                                type="button"
                                class="rounded-lg border-2 px-4 py-3 text-sm font-semibold"
                                :class="layoutMode === 'single' ? 'border-emerald-600 bg-emerald-50 text-emerald-700' : 'border-gray-200 text-gray-700'"
                                @click="layoutMode = 'single'"
                            >
                                Single ID per Page
                            </button>
                            <button
                                type="button"
                                class="rounded-lg border-2 px-4 py-3 text-sm font-semibold"
                                :class="layoutMode === 'sheet' ? 'border-emerald-600 bg-emerald-50 text-emerald-700' : 'border-gray-200 text-gray-700'"
                                @click="layoutMode = 'sheet'"
                            >
                                Sheet Layout
                            </button>
                        </div>

                        <div v-if="layoutMode === 'sheet'" class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-gray-500">Sheet Size</label>
                                <select v-model="sheetSize" class="w-full rounded-lg border-gray-300 text-sm focus:border-AB focus:ring-AB">
                                    <option value="a4">A4</option>
                                    <option value="folio">Folio</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-gray-500">Sheet Margin (cm)</label>
                                <input v-model.number="sheetMarginCm" type="number" min="0" step="0.1" class="w-full rounded-lg border-gray-300 text-sm focus:border-AB focus:ring-AB" />
                            </div>
                        </div>

                        <div class="rounded-lg bg-gray-50 p-3 text-sm text-gray-700">
                            Sheet layout fits {{ labelsPerRow }} card{{ labelsPerRow === 1 ? "" : "s" }} per row and {{ labelsPerColumn }} row{{ labelsPerColumn === 1 ? "" : "s" }} per page.
                        </div>

                        <div class="flex justify-between">
                            <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700" @click="prevStep">
                                Back
                            </button>
                            <button type="button" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white" @click="buildLabels">
                                Generate Preview
                            </button>
                        </div>
                    </section>

                    <section v-if="activeTab === 'preview'" class="p-4 space-y-4">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h3 class="font-bold text-gray-900">Preview</h3>
                                <p class="text-sm text-gray-500">{{ totalLabels }} ID card{{ totalLabels === 1 ? "" : "s" }} ready.</p>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700" @click="prevStep">
                                    Back
                                </button>
                                <button type="button" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white" :disabled="!previewReady" @click="printCards">
                                    Print {{ totalLabels }} IDs
                                </button>
                            </div>
                        </div>

                        <div v-if="previewReady" class="rounded-lg bg-gray-100 p-4">
                            <div class="flex flex-wrap justify-center gap-4">
                                <PersonnelIdCard v-for="label in labels.slice(0, 6)" :key="label.key" :card="label.card" :card-style="cardStyle"/>
                            </div>
                            <div v-if="labels.length > 6" class="mt-3 text-center text-xs text-gray-500">
                                Showing 6 of {{ labels.length }} IDs
                            </div>
                        </div>
                    </section>
                </div>
            </main>
        </div>

        <Teleport to="body">
            <div v-if="previewReady && layoutMode === 'single'" class="print-area id-print-area">
                <PersonnelIdCard v-for="label in labels" :key="label.key" :card="label.card" :card-style="cardStyle"/>
            </div>

            <div v-if="previewReady && layoutMode === 'sheet'" class="print-area-sheet">
                <div
                    v-for="(sheet, sheetIndex) in sheetedLabels"
                    :key="`id-sheet-${sheetIndex}`"
                    class="sheet-page"
                    :style="{ width: `${sheetDimensions.widthCm}cm`, height: `${sheetDimensions.heightCm}cm`, padding: `${sheetMarginCm}cm` }"
                >
                    <div class="sheet-grid" :style="{ gridTemplateColumns: `repeat(${labelsPerRow}, ${resolvedWidthCm}cm)`, gap: '0.25cm' }">
                        <PersonnelIdCard v-for="label in sheet" :key="label.key" :card="label.card" :card-style="cardStyle"/>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>

<style>
.id-print-area {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    justify-content: center;
}

.personnel-id-card,
.personnel-id-card * {
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

.personnel-id-card {
    flex-shrink: 0;
}

.id-card,
.id-card-preview {
    width: 7.4cm;
    height: 10.5cm;
    padding: 0.5cm;
    border: 1.5px solid #1f6f43;
    background: white;
    color: #172033;
    position: relative;
    overflow: hidden;
    break-after: page;
    font-family: Arial, Helvetica, sans-serif;
}

.id-card-preview {
    transform: scale(0.86);
    transform-origin: top center;
    margin-bottom: -1.2cm;
}


.id-name {
    text-align: center;
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    line-height: 1.2;
    min-height: 1cm;
}

.id-type {
    text-align: center;
    color: #166534;
    font-size: 8px;
    font-weight: 700;
    text-transform: uppercase;
    margin-top: 1mm;
}

.id-fields {
    margin-top: 0.5cm;
    font-size: 8px;
    line-height: 1.45;
}

.id-fields dt {
    color: #64748b;
    text-transform: uppercase;
    font-size: 6px;
    font-weight: 700;
}

.id-fields dd {
    font-size: 9px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 2.5mm;
}

.id-footer {
    position: absolute;
    left: 0.5cm;
    right: 0.5cm;
    bottom: 0.4cm;
    border-top: 1px solid #d1d5db;
    padding-top: 2mm;
    font-size: 6px;
    color: #64748b;
    text-align: center;
}

.sheet-page {
    background: #ffffff;
    box-sizing: border-box;
    page-break-after: always;
    break-after: page;
}

.sheet-grid {
    display: grid;
    align-content: start;
    justify-content: start;
}

.print-area,
.print-area-sheet {
    display: none;
}

@media print {
    html,
    body {
        margin: 0 !important;
        padding: 0 !important;
    }

    body > *:not(.print-area):not(.print-area-sheet) {
        display: none !important;
    }

    .print-area,
    .print-area-sheet {
        position: absolute;
        left: 0;
        top: 0;
        margin: 0;
        padding: 0;
        display: block !important;
        visibility: visible !important;
    }

    .id-print-area {
        gap: 0;
        display: block;
        margin: 0;
        padding: 0;
    }

    .personnel-id-card {
        page-break-after: always;
        break-after: page;
        page-break-inside: avoid;
        break-inside: avoid-page;
        margin: 0 !important;
        box-shadow: none;
    }

    .personnel-id-card:last-child {
        page-break-after: auto;
        break-after: auto;
    }

    .sheet-page {
        page-break-after: always;
        break-after: page;
        page-break-inside: avoid;
        break-inside: avoid-page;
        margin: 0 !important;
    }

    .sheet-page:last-child {
        page-break-after: auto;
        break-after: auto;
    }

    .sheet-page .personnel-id-card {
        page-break-after: auto;
        break-after: auto;
    }
}
</style>
