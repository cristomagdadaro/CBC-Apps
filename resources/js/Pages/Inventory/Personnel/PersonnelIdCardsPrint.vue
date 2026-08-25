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
            registrationTypeFilter: "",
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
            let result = this.cards;

            if (this.registrationTypeFilter) {
                result = result.filter((card) => card.registration_type === this.registrationTypeFilter);
            }

            const term = this.search?.toLowerCase()?.trim();
            if (!term) {
                return result;
            }

            return result.filter((card) => {
                return [card.full_name, card.employee_id, card.course_program, card.registration_type_label].filter(Boolean).some((value) => String(value).toLowerCase().includes(term));
            });
        },
        selectedCount() {
            return Object.values(this.selected).filter(Boolean).length;
        },
        totalLabels() {
            return this.labels.length;
        },
        allSelected() {
            return this.filteredCards.length > 0 && this.filteredCards.every((card) => this.selected[this.cardKey(card)]);
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
            return this.sheetDimensions.widthCm - this.sheetMarginCm * 2;
        },
        sheetUsableHeightCm() {
            return this.sheetDimensions.heightCm - this.sheetMarginCm * 2;
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
            await Promise.all(
                images.map((image) => {
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
                }),
            );
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
                :route-link="fromUrl || route('personnels.registrations.index')">
                <transition-container
                    type="pop-in"
                    :duration="500">
                    <button
                        type="button"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                        :disabled="!previewReady"
                        @click="printCards">
                        <LuPrinter class="h-4 w-4 text-emerald-500" />
                        <span>Print IDs</span>
                    </button>
                </transition-container>
            </ActionHeaderLayout>
        </template>

        <div class="gap-5 p-5 md:grid md:grid-cols-12">
            <aside class="col-span-12 h-fit md:sticky md:top-5 md:col-span-2 md:self-start">
                <div class="shadow-xs rounded-2xl border border-slate-200 bg-white p-4 text-slate-900 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100">
                    <div class="mb-2 flex items-center gap-2">
                        <LuInfo class="h-5 w-5 text-lime-600 dark:text-lime-400" />
                        <h4 class="text-xs font-bold text-slate-900 sm:text-sm dark:text-slate-100">ID Workflow</h4>
                    </div>
                    <p class="text-xs text-slate-500 sm:text-sm dark:text-slate-400">Select approved IDs, choose single-card or sheet layout, preview, then print. Each card is fixed at A7 size.</p>
                </div>
            </aside>

            <main class="col-span-12 space-y-4 md:col-span-10">
                <div class="shadow-xs overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <div class="grid grid-cols-3 border-b border-slate-200 dark:border-slate-800">
                        <button
                            type="button"
                            class="flex items-center justify-center gap-2 px-4 py-3 text-xs font-semibold transition-colors sm:text-sm"
                            :class="activeTab === 'items' ? 'border-b-2 border-lime-600 bg-lime-50/60 font-bold text-lime-700 dark:bg-lime-950/40 dark:text-lime-300' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                            @click="activeTab = 'items'">
                            Select IDs
                            <span
                                v-if="selectedCount > 0"
                                class="ml-1 rounded-full bg-lime-600 px-2 py-0.5 text-[10px] font-bold text-white sm:text-xs">
                                {{ selectedCount }}
                            </span>
                        </button>
                        <button
                            type="button"
                            class="flex items-center justify-center gap-2 px-4 py-3 text-xs font-semibold transition-colors sm:text-sm"
                            :class="activeTab === 'settings' ? 'border-b-2 border-lime-600 bg-lime-50/60 font-bold text-lime-700 dark:bg-lime-950/40 dark:text-lime-300' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                            :disabled="selectedCount === 0"
                            @click="activeTab = 'settings'">
                            Print Settings
                        </button>
                        <button
                            type="button"
                            class="flex items-center justify-center gap-2 px-4 py-3 text-xs font-semibold transition-colors sm:text-sm"
                            :class="activeTab === 'preview' ? 'border-b-2 border-lime-600 bg-lime-50/60 font-bold text-lime-700 dark:bg-lime-950/40 dark:text-lime-300' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                            :disabled="!previewReady"
                            @click="activeTab = 'preview'">
                            Preview & Print
                        </button>
                    </div>

                    <section
                        v-if="activeTab === 'items'"
                        class="space-y-4 p-4 sm:p-6">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 sm:text-base dark:text-slate-100">Approved ID Cards</h3>
                                <p class="text-xs text-slate-500 sm:text-sm dark:text-slate-400">{{ selectedCount }} selected from {{ cards.length }} approved IDs.</p>
                            </div>
                            <div class="flex gap-2">
                                <div class="relative flex-1 md:w-64">
                                    <LuSearch class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                    <input
                                        v-model="search"
                                        type="search"
                                        class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-3 text-xs text-slate-900 focus:border-transparent focus:ring-2 focus:ring-lime-500 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                                        placeholder="Search IDs..." />
                                </div>
                                <select
                                    v-model="registrationTypeFilter"
                                    class="hidden rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-900 focus:border-transparent focus:ring-2 focus:ring-lime-500 sm:block sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                                    <option value="">All Types</option>
                                    <option value="student">Student</option>
                                    <option value="ojt">OJT</option>
                                    <option value="thesis">Thesis</option>
                                </select>
                                <button
                                    type="button"
                                    class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                    :disabled="filteredCards.length === 0"
                                    @click="toggleAll">
                                    {{ allSelected ? "Clear" : "Select All" }}
                                </button>
                            </div>
                        </div>

                        <div
                            v-if="filteredCards.length"
                            class="flex flex-wrap gap-4">
                            <button
                                v-for="card in filteredCards"
                                :key="cardKey(card)"
                                type="button"
                                class="shadow-xs overflow-hidden rounded-2xl border text-left transition-all duration-200"
                                :class="selected[cardKey(card)] ? 'border-lime-500 bg-lime-50/40 ring-2 ring-lime-500/20 dark:border-lime-500 dark:bg-lime-950/20' : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700'"
                                @click="toggleCard(card)">
                                <PersonnelIdCard
                                    :card="card"
                                    :card-style="cardStyle" />
                            </button>
                        </div>

                        <div
                            v-else
                            class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-xs text-slate-500 sm:text-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
                            No approved Student, OJT, or Thesis IDs are ready for printing.
                        </div>

                        <div class="flex justify-end pt-2">
                            <button
                                type="button"
                                class="shadow-xs flex items-center gap-2 rounded-xl bg-lime-600 px-6 py-2.5 text-xs font-bold text-white transition-all hover:bg-lime-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 sm:text-sm"
                                :disabled="selectedCount === 0"
                                @click="nextStep">
                                Continue to Settings
                                <LuArrowRight class="h-4 w-4" />
                            </button>
                        </div>
                    </section>

                    <section
                        v-if="activeTab === 'settings'"
                        class="space-y-6 p-4 sm:p-6">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 sm:text-base dark:text-slate-100">Print Settings</h3>
                            <p class="text-xs text-slate-500 sm:text-sm dark:text-slate-400">A7 card size is fixed at 7.4cm x 10.5cm.</p>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <button
                                type="button"
                                class="rounded-xl border-2 px-4 py-3 text-xs font-semibold transition-all sm:text-sm"
                                :class="layoutMode === 'single' ? 'border-lime-600 bg-lime-50 font-bold text-lime-700 dark:bg-lime-950/40 dark:text-lime-300' : 'border-slate-200 text-slate-700 hover:border-slate-300 dark:border-slate-700 dark:text-slate-300'"
                                @click="layoutMode = 'single'">
                                Single ID per Page
                            </button>
                            <button
                                type="button"
                                class="rounded-xl border-2 px-4 py-3 text-xs font-semibold transition-all sm:text-sm"
                                :class="layoutMode === 'sheet' ? 'border-lime-600 bg-lime-50 font-bold text-lime-700 dark:bg-lime-950/40 dark:text-lime-300' : 'border-slate-200 text-slate-700 hover:border-slate-300 dark:border-slate-700 dark:text-slate-300'"
                                @click="layoutMode = 'sheet'">
                                Sheet Layout
                            </button>
                        </div>

                        <div
                            v-if="layoutMode === 'sheet'"
                            class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Sheet Size</label>
                                <select
                                    v-model="sheetSize"
                                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 text-xs text-slate-900 focus:ring-lime-500 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                                    <option value="a4">A4</option>
                                    <option value="folio">Folio</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Sheet Margin (cm)</label>
                                <input
                                    v-model.number="sheetMarginCm"
                                    type="number"
                                    min="0"
                                    step="0.1"
                                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 text-xs text-slate-900 focus:ring-lime-500 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100" />
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200/80 bg-slate-50 p-4 text-xs font-medium text-slate-700 sm:text-sm dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300">
                            Sheet layout fits {{ labelsPerRow }} card{{ labelsPerRow === 1 ? "" : "s" }} per row and {{ labelsPerColumn }} row{{ labelsPerColumn === 1 ? "" : "s" }}
                            per page.
                        </div>

                        <div class="flex justify-between pt-2">
                            <button
                                type="button"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-100 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                                @click="prevStep">
                                Back
                            </button>
                            <button
                                type="button"
                                class="shadow-xs flex items-center gap-2 rounded-xl bg-lime-600 px-6 py-2.5 text-xs font-bold text-white transition-all hover:bg-lime-700 active:scale-95 sm:text-sm"
                                @click="buildLabels">
                                <LuSparkles class="h-4 w-4" />
                                Generate Preview
                            </button>
                        </div>
                    </section>

                    <section
                        v-if="activeTab === 'preview'"
                        class="space-y-6 p-4 sm:p-6">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 sm:text-base dark:text-slate-100">Preview</h3>
                                <p class="text-xs text-slate-500 sm:text-sm dark:text-slate-400">
                                    {{ totalLabels }} ID card{{ totalLabels === 1 ? "" : "s" }}
                                    ready for printing.
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-100 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                                    @click="prevStep">
                                    Back to Settings
                                </button>
                                <button
                                    type="button"
                                    class="shadow-xs flex items-center gap-2 rounded-xl bg-lime-600 px-6 py-2.5 text-xs font-bold text-white transition-all hover:bg-lime-700 active:scale-95 sm:text-sm"
                                    :disabled="!previewReady"
                                    @click="printCards">
                                    <LuPrinter class="h-4 w-4" />
                                    Print {{ totalLabels }} IDs
                                </button>
                            </div>
                        </div>

                        <div
                            v-if="previewReady"
                            class="rounded-2xl border border-slate-200 bg-slate-100 p-6 dark:border-slate-800 dark:bg-slate-950">
                            <div class="flex flex-wrap justify-center gap-4">
                                <PersonnelIdCard
                                    v-for="label in labels.slice(0, 6)"
                                    :key="label.key"
                                    :card="label.card"
                                    :card-style="cardStyle" />
                            </div>
                            <div
                                v-if="labels.length > 6"
                                class="mt-4 text-center text-xs font-semibold text-slate-500 dark:text-slate-400">
                                Showing 6 of {{ labels.length }} IDs
                            </div>
                        </div>
                    </section>
                </div>
            </main>
        </div>

        <Teleport to="body">
            <div
                v-if="previewReady && layoutMode === 'single'"
                class="print-area id-print-area">
                <PersonnelIdCard
                    v-for="label in labels"
                    :key="label.key"
                    :card="label.card"
                    :card-style="cardStyle" />
            </div>

            <div
                v-if="previewReady && layoutMode === 'sheet'"
                class="print-area-sheet">
                <div
                    v-for="(sheet, sheetIndex) in sheetedLabels"
                    :key="`id-sheet-${sheetIndex}`"
                    class="sheet-page"
                    :style="{
                        width: `${sheetDimensions.widthCm}cm`,
                        height: `${sheetDimensions.heightCm}cm`,
                        padding: `${sheetMarginCm}cm`,
                    }">
                    <div
                        class="sheet-grid"
                        :style="{
                            gridTemplateColumns: `repeat(${labelsPerRow}, ${resolvedWidthCm}cm)`,
                            gap: '0.25cm',
                        }">
                        <PersonnelIdCard
                            v-for="label in sheet"
                            :key="label.key"
                            :card="label.card"
                            :card-style="cardStyle" />
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
