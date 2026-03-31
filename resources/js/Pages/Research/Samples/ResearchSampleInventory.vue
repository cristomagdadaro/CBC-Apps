<script>
import axios from "axios";
import { Head } from "@inertiajs/vue3";
import JsBarcode from "jsbarcode";
import QrcodeVue from "qrcode.vue";
import CameraScanner from "@/Components/CameraScanner.vue";
import LabelCard from "@/Pages/Inventory/Barcodes/components/LabelCard.vue";
import { subscribeToRealtimeChannels } from "@/Modules/realtime/subscriptions";

export default {
  name: "ResearchSampleInventory",
  components: {
    CameraScanner,
    Head,
    LabelCard,
    QrcodeVue,
  },
  data() {
    return {
      loading: false,
      searching: false,
      scannerOpen: false,
      search: "",
      samples: [],
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
      activeTab: "items",
      isMobile: false,
      hoveredKey: null,
      selectedSample: null,
      scanMessage: "",
      scanError: "",
      realtimeCleanup: null,
      realtimeRefreshTimer: null,
    };
  },
  computed: {
    sizeTemplates() {
      return [
        { key: "3x5", heightCm: 3, widthCm: 5, label: "3cm × 5cm" },
        { key: "4.8x5.5", heightCm: 4.8, widthCm: 5.5, label: "4.8cm × 5.5cm" },
        { key: "8x5", heightCm: 8, widthCm: 5, label: "8cm × 5cm" },
        { key: "1.5x6", heightCm: 1.5, widthCm: 6, label: "1.5cm × 6cm" },
        { key: "custom", heightCm: null, widthCm: null, label: "Custom Size" },
      ];
    },
    isCustomSize() {
      return this.sizeTemplate === "custom";
    },
    baseHeightCm() {
      if (this.isCustomSize) {
        return this.normalizeSize(this.customHeightCm, 3);
      }

      const selected = this.sizeTemplates.find((item) => item.key === this.sizeTemplate);
      return selected?.heightCm ?? 3;
    },
    baseWidthCm() {
      if (this.isCustomSize) {
        return this.normalizeSize(this.customWidthCm, 5);
      }

      const selected = this.sizeTemplates.find((item) => item.key === this.sizeTemplate);
      return selected?.widthCm ?? 5;
    },
    resolvedHeightCm() {
      return this.orientation === "landscape" ? this.baseWidthCm : this.baseHeightCm;
    },
    resolvedWidthCm() {
      return this.orientation === "landscape" ? this.baseHeightCm : this.baseWidthCm;
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
      return this.hasBarcodeMode
        ? Math.max(14, this.labelFontSize * 1.6)
        : Math.max(12, this.labelFontSize * 1.4);
    },
    graphicsAvailableHeightPx() {
      return Math.max(
        16,
        this.cardUsableHeightPx - this.textReservePx - this.captionReservePx
      );
    },
    maxQrSizePx() {
      const modeHeightLimit =
        this.printMode === "both"
          ? this.graphicsAvailableHeightPx * 0.58
          : this.graphicsAvailableHeightPx;

      return Math.max(60, Math.floor(Math.min(this.cardUsableWidthPx, modeHeightLimit)));
    },
    maxBarcodeHeightPx() {
      const modeHeightLimit =
        this.printMode === "both"
          ? this.graphicsAvailableHeightPx * 0.42
          : this.graphicsAvailableHeightPx;

      return Math.max(12, Math.floor(modeHeightLimit));
    },
    barcodeHeight() {
      const height = this.normalizeSize(
        this.customBarcodeHeight,
        this.maxBarcodeHeightPx
      );
      return Math.max(12, Math.min(height, this.maxBarcodeHeightPx));
    },
    qrSize() {
      const size = this.normalizeSize(this.customQRSize, this.maxQrSizePx);
      return Math.max(20, Math.min(size, this.maxQrSizePx));
    },
    cardStyle() {
      return {
        width: `${this.resolvedWidthCm}cm`,
        height: `${this.resolvedHeightCm}cm`,
      };
    },
    modalCardStyle() {
      return {
        width: `${this.resolvedWidthCm * 3}cm`,
        height: `${this.resolvedHeightCm * 3}cm`,
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
    filteredSamples() {
      const term = this.search?.toLowerCase()?.trim();
      if (!term) {
        return this.samples;
      }

      return this.samples.filter((sample) => {
        return [
          sample.uid,
          sample.accession_name,
          sample.pr_code,
          sample.line_label,
          sample.current_status,
          sample.sample_type,
          sample.experiment?.title,
          sample.experiment?.study?.title,
          sample.experiment?.study?.project?.title,
        ]
          .filter(Boolean)
          .some((value) => String(value).toLowerCase().includes(term));
      });
    },
    allSelected() {
      const selectable = this.filteredSamples.filter((sample) => !!sample.uid);
      if (!selectable.length) {
        return false;
      }

      return selectable.every((sample) => this.selected[this.sampleKey(sample)]);
    },
    someSelected() {
      const selectable = this.filteredSamples.filter((sample) => !!sample.uid);
      const selectedCount = selectable.filter(
        (sample) => this.selected[this.sampleKey(sample)]
      ).length;
      return selectedCount > 0 && selectedCount < selectable.length;
    },
    selectedCount() {
      return Object.values(this.selected).filter(Boolean).length;
    },
    totalLabels() {
      return Object.values(this.selected).reduce(
        (sum, selection) => sum + (selection?.qty || 1),
        0
      );
    },
    selectedSampleIds() {
      return Object.values(this.selected)
        .map((selection) => selection?.sample?.id)
        .filter(Boolean);
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
      for (let index = 0; index < this.labels.length; index += this.labelsPerSheet) {
        sheets.push(this.labels.slice(index, index + this.labelsPerSheet));
      }

      return sheets;
    },
    printModeOptions() {
      return [
        { name: "barcode", label: "Barcode Only", icon: "LuBarcode" },
        { name: "qr", label: "QR Code Only", icon: "LuQrCode" },
        { name: "both", label: "Both", icon: "LuLayers" },
      ];
    },
  },
  watch: {
    printMode() {
      this.applyPrintPageSize();
    },
    sizeTemplate() {
      this.applyPrintPageSize();
    },
    customWidthCm() {
      this.applyPrintPageSize();
    },
    customHeightCm() {
      this.applyPrintPageSize();
    },
    orientation() {
      this.applyPrintPageSize();
    },
    layoutMode() {
      this.applyPrintPageSize();
    },
    sheetSize() {
      this.applyPrintPageSize();
    },
    sheetMarginCm() {
      this.applyPrintPageSize();
    },
  },
  mounted() {
    this.checkMobile();
    window.addEventListener("resize", this.checkMobile);
    this.applyPrintPageSize();
    this.loadSamples();
    this.configureRealtime();
  },
  beforeUnmount() {
    window.removeEventListener("resize", this.checkMobile);
    if (this.realtimeRefreshTimer) {
      clearTimeout(this.realtimeRefreshTimer);
    }
    this.cleanupRealtime();
  },
  methods: {
    checkMobile() {
      this.isMobile = window.innerWidth < 768;
    },
    sampleKey(sample) {
      return String(sample.id);
    },
    normalizeSize(value, fallback) {
      const num = Number(value);
      if (!Number.isFinite(num) || num <= 0) {
        return fallback;
      }

      return Math.max(0.5, Number(num.toFixed(2)));
    },
    applyPrintPageSize() {
      if (typeof document === "undefined") {
        return;
      }

      const styleId = "research-sample-dynamic-page-size";
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
    qrPayload(sample) {
      return `sample:${sample.uid}|experiment:${sample.experiment_id}|sample_id:${sample.id}`;
    },
    labelItem(sample) {
      return {
        name: sample.accession_name || sample.uid,
        brand: sample.sample_type || "Research Sample",
        description:
          sample.current_status || sample.generation || sample.experiment?.title || "",
        barcode: sample.uid,
      };
    },
    barcodeModuleWidth(barcodeValue) {
      const value = String(barcodeValue || "");
      const estimatedModules = Math.max(88, value.length * 11 + 35);
      const moduleWidth = this.cardUsableWidthPx / estimatedModules;
      return Math.max(1.1, Math.min(moduleWidth, 3.2));
    },
    labelPayload(sample, copyIndex = 0) {
      return {
        key: `${this.sampleKey(sample)}-${copyIndex}`,
        item: this.labelItem(sample),
        equipmentUrl: this.qrPayload(sample),
        sample,
      };
    },
    async loadSamples() {
      this.loading = true;
      this.scanError = "";

      try {
        const response = await axios.get(route("api.research.samples.inventory.index"), {
          params: {
            search: this.search,
            limit: 200,
          },
        });

        this.samples = response.data?.data || [];
        this.selected = {};
        this.labels = [];
        this.previewReady = false;
      } catch (error) {
        this.scanError =
          error?.response?.data?.message || "Unable to load sample inventory.";
      } finally {
        this.loading = false;
      }
    },
    async lookup(uid, source = "manual_lookup") {
      this.searching = true;
      this.scanError = "";
      this.scanMessage = "";

      try {
        const response = await axios.get(
          route("api.research.samples.inventory.lookup", uid),
          {
            params: {
              source,
              qr_payload: `sample:${uid}`,
            },
          }
        );

        this.selectedSample = response.data?.data || null;
        this.scanMessage = `Sample ${uid} loaded for retrieval and monitoring.`;
        this.$nextTick(() => this.renderSingleBarcode());
      } catch (error) {
        this.selectedSample = null;
        this.scanError =
          error?.response?.data?.message || "No sample matched the scanned code.";
      } finally {
        this.searching = false;
      }
    },
    async onScan(payload) {
      this.searching = true;
      this.scanError = "";
      this.scanMessage = "";

      try {
        const response = await axios.post(route("api.research.samples.inventory.scan"), {
          payload,
          source: "camera_scan",
        });

        this.selectedSample = response.data?.data || null;
        this.scanMessage = `Scan matched sample ${this.selectedSample?.uid || ""}.`;
        this.$nextTick(() => this.renderSingleBarcode());
      } catch (error) {
        this.selectedSample = null;
        this.scanError =
          error?.response?.data?.message ||
          "Scanned code is not linked to a research sample.";
      } finally {
        this.searching = false;
      }
    },
    toggleAll() {
      if (this.allSelected) {
        this.selected = {};
        return;
      }

      const next = {};
      this.filteredSamples.forEach((sample) => {
        if (!sample.uid) {
          return;
        }

        next[this.sampleKey(sample)] = {
          qty: this.selected[this.sampleKey(sample)]?.qty ?? 1,
          sample,
        };
      });

      this.selected = next;
    },
    toggleSample(sample) {
      if (!sample.uid) {
        return;
      }

      const key = this.sampleKey(sample);
      const next = { ...this.selected };

      if (next[key]) {
        delete next[key];
      } else {
        next[key] = { qty: 1, sample };
      }

      this.selected = next;
    },
    updateQty(key, value) {
      const qty = Number(value);
      if (!this.selected[key]) {
        return;
      }

      this.selected[key].qty = Number.isFinite(qty) && qty > 0 ? qty : 1;
    },
    buildLabels() {
      const labels = [];
      Object.entries(this.selected).forEach(([key, selection]) => {
        const qty = selection?.qty ?? 1;
        const sample = selection?.sample;
        if (!sample?.uid) {
          return;
        }

        for (let index = 0; index < qty; index += 1) {
          labels.push({
            ...this.labelPayload(sample, index),
            key: `${key}-${index}`,
          });
        }
      });

      this.labels = labels;
      this.previewReady = labels.length > 0;
      this.activeTab = "preview";
    },
    async logSelectedLabels() {
      if (!this.selectedSampleIds.length) {
        return;
      }

      await axios.post(route("api.research.samples.inventory.labels.print"), {
        sample_ids: this.selectedSampleIds,
      });
    },
    async printLabels() {
      if (!this.previewReady) {
        return;
      }

      await this.logSelectedLabels();
      this.applyPrintPageSize();
      window.print();
    },
    async exportPdf() {
      if (!this.previewReady || this.exporting) {
        return;
      }

      this.exporting = true;

      try {
        await this.logSelectedLabels();

        const labels = this.labels.map((label) => ({
          name: label.item?.name ?? "",
          brand: label.item?.brand ?? "N/A",
          description: label.item?.description ?? "",
          barcode: label.item?.barcode ?? "",
          qrUrl: this.printMode !== "barcode" ? label.equipmentUrl : null,
        }));

        const response = await axios.post(
          route("inventory.generate-pdf"),
          {
            type: "barcode-labels",
            printMode: this.printMode,
            paperWidth: this.resolvedWidthCm,
            paperHeight: this.resolvedHeightCm,
            qrSize: this.qrSize,
            barcodeHeight: this.barcodeHeight,
            labels,
          },
          {
            responseType: "blob",
          }
        );

        const blob = new Blob([response.data], { type: "application/pdf" });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        const disposition = response.headers?.["content-disposition"] ?? "";
        const match = disposition.match(/filename="?([^";]+)"?/i);
        link.href = url;
        link.download = match?.[1] ?? "research-sample-labels.pdf";
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
      } finally {
        this.exporting = false;
      }
    },
    nextStep() {
      if (this.activeTab === "items" && this.selectedCount > 0) {
        this.activeTab = "settings";
      } else if (this.activeTab === "settings") {
        this.buildLabels();
      }
    },
    prevStep() {
      if (this.activeTab === "settings") {
        this.activeTab = "items";
      } else if (this.activeTab === "preview") {
        this.activeTab = "settings";
      }
    },
    openLabelModal(label) {
      this.selectedLabelForModal = label;
      this.showLabelModal = true;
    },
    closeLabelModal() {
      this.showLabelModal = false;
      this.selectedLabelForModal = null;
    },
    renderSingleBarcode() {
      if (!this.selectedSample) {
        return;
      }

      const target = document.getElementById("research-selected-sample-barcode");
      if (!target) {
        return;
      }

      JsBarcode(target, this.selectedSample.uid, {
        format: "CODE128",
        width: 2,
        height: 54,
        displayValue: false,
        margin: 0,
      });
    },
    cleanupRealtime() {
      if (typeof this.realtimeCleanup === "function") {
        this.realtimeCleanup();
      }

      this.realtimeCleanup = null;
    },
    scheduleRealtimeRefresh() {
      if (this.realtimeRefreshTimer) {
        clearTimeout(this.realtimeRefreshTimer);
      }

      this.realtimeRefreshTimer = setTimeout(() => {
        this.loadSamples();
      }, 400);
    },
    configureRealtime() {
      this.cleanupRealtime();

      this.realtimeCleanup = subscribeToRealtimeChannels([
        {
          type: "private",
          channel: "research.samples",
          event: "research.sample.inventory.changed",
          feature: "research",
          handler: () => this.scheduleRealtimeRefresh(),
        },
      ]);
    },
  },
};
</script>

<template>
  <Head title="Research Sample Inventory" />

  <AppLayout title="Research Sample Inventory">
    <template #header>
      <ActionHeaderLayout
        title="Research Sample Inventory"
        subtitle="Barcode printing, QR retrieval, and sample label preview using the standard barcode workflow."
        :route-link="route('research.samples.inventory')"
      >
        <Link
          :href="`${route('manuals.index')}?section=researchMonitoring`"
          class="rounded-lg border border-white/25 px-4 py-2 text-sm font-medium text-white hover:bg-white/10"
        >
          Manuals & Guides
        </Link>
        <Link
          :href="route('research.dashboard')"
          class="rounded-lg bg-white/15 px-4 py-2 text-sm font-medium text-white hover:bg-white/25"
        >
          Dashboard
        </Link>
      </ActionHeaderLayout>
    </template>

    <div class="md:grid md:grid-cols-12 gap-5 p-5">
      <div
        class="grid grid-cols-1 grid-rows-3 gap-4 col-span-12 md:col-span-2 h-fit md:sticky md:top-5 md:self-start"
      >
        <div class="bg-white rounded-xl p-4 border border-gray-200">
          <div class="flex items-center gap-2 mb-2">
            <LuScanLine class="w-5 h-5 text-emerald-600" />
            <h4 class="font-medium text-gray-900">Research Scanner</h4>
          </div>
          <p class="text-sm text-gray-600">
            Scan research sample barcodes or QR payloads here before printing labels or
            pulling sample details.
          </p>
        </div>

        <div class="bg-white rounded-xl p-4 border border-gray-200">
          <div class="flex items-center gap-2 mb-2">
            <LuQrCode class="w-5 h-5 text-emerald-600" />
            <h4 class="font-medium text-gray-900">QR Retrieval</h4>
          </div>
          <p class="text-sm text-gray-600">
            The QR code keeps the research retrieval payload, while the printed barcode
            keeps the visible UID standard.
          </p>
        </div>

        <div class="bg-white rounded-xl p-4 border border-gray-200">
          <div class="flex items-center gap-2 mb-2">
            <LuLayers class="w-5 h-5 text-emerald-600" />
            <h4 class="font-medium text-gray-900">Label Standard</h4>
          </div>
          <p class="text-sm text-gray-600">
            This page now uses the same label sizing, preview, print, and PDF export flow
            as the inventory BarcodePrint module.
          </p>
        </div>
      </div>

      <div class="w-full col-span-12 md:col-span-10 space-y-5">
        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
          <div class="grid gap-4 md:grid-cols-[1fr_auto] md:items-center">
            <div class="flex gap-3">
              <div class="flex-1 relative">
                <LuSearch
                  class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                />
                <input
                  v-model="search"
                  class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm"
                  placeholder="Search by UID, accession, PR code, study, or experiment"
                  @keyup.enter="loadSamples"
                />
              </div>
              <button
                type="button"
                class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-black"
                @click="loadSamples"
              >
                Search
              </button>
            </div>

            <div class="flex flex-wrap gap-3">
              <button
                type="button"
                class="rounded-lg border px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                @click="scannerOpen = !scannerOpen"
              >
                {{ scannerOpen ? "Hide Scanner" : "Open Scanner" }}
              </button>
              <button
                type="button"
                class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700"
                @click="activeTab = 'items'"
              >
                Select Samples
              </button>
            </div>
          </div>

          <div
            v-if="scanError"
            class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
          >
            {{ scanError }}
          </div>
          <div
            v-if="scanMessage"
            class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
          >
            {{ scanMessage }}
          </div>
        </section>

        <section
          v-if="scannerOpen"
          class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm"
        >
          <h2 class="text-2xl font-semibold text-gray-900">Scan Research Sample Code</h2>
          <p class="mt-2 text-sm text-gray-600">
            Use barcodes for inventory workflow and QR retrieval payloads for monitoring
            and presentation lookups.
          </p>
          <div class="mt-4">
            <CameraScanner
              :enabled="true"
              :model-value="true"
              border-color="emerald"
              @decoded="onScan"
            />
          </div>
        </section>

        <section
          v-if="selectedSample"
          class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm"
        >
          <h2 class="text-2xl font-semibold text-gray-900">Selected Sample Snapshot</h2>
          <p class="mt-2 text-sm text-gray-600">
            {{ selectedSample.uid }} • {{ selectedSample.accession_name }}
          </p>

          <div class="mt-4 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl bg-gray-50 p-4">
              <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">
                Barcode
              </p>
              <svg id="research-selected-sample-barcode" class="mt-3 h-16 w-56"></svg>
              <p class="mt-2 text-xs font-semibold text-gray-700">
                {{ selectedSample.uid }}
              </p>
            </div>
            <div class="rounded-2xl bg-gray-50 p-4">
              <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">
                QR Retrieval Payload
              </p>
              <div class="mt-3 inline-block rounded-lg bg-white p-2">
                <QrcodeVue
                  :value="qrPayload(selectedSample)"
                  :size="92"
                  level="M"
                  render-as="canvas"
                />
              </div>
              <p class="mt-2 text-xs text-gray-700">Scan to retrieve display payload.</p>
            </div>
            <div class="rounded-2xl bg-gray-50 p-4 text-sm text-gray-700">
              <p>
                <span class="font-semibold text-gray-900">Project:</span>
                {{ selectedSample.experiment?.study?.project?.title || "N/A" }}
              </p>
              <p class="mt-2">
                <span class="font-semibold text-gray-900">Study:</span>
                {{ selectedSample.experiment?.study?.title || "N/A" }}
              </p>
              <p class="mt-2">
                <span class="font-semibold text-gray-900">Experiment:</span>
                {{ selectedSample.experiment?.title || "N/A" }}
              </p>
              <p class="mt-2">
                <span class="font-semibold text-gray-900">Status:</span>
                {{ selectedSample.current_status || "Pending" }}
              </p>
              <p class="mt-2">
                <span class="font-semibold text-gray-900">Location:</span>
                {{
                  selectedSample.current_location ||
                  selectedSample.storage_location ||
                  "N/A"
                }}
              </p>
            </div>
          </div>
        </section>

        <div
          class="md:hidden bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
        >
          <div class="flex border-b border-gray-200">
            <button
              @click="activeTab = 'items'"
              class="flex-1 px-3 py-3 text-xs font-medium transition-colors"
              :class="
                activeTab === 'items'
                  ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50/50'
                  : 'text-gray-600'
              "
            >
              <div class="flex flex-col items-center gap-1">
                <LuPackage class="w-4 h-4" />
                <span>Select</span>
                <span
                  v-if="selectedCount > 0"
                  class="text-[10px] bg-blue-600 text-white px-1.5 py-0.5 rounded-full"
                  >{{ selectedCount }}</span
                >
              </div>
            </button>
            <button
              @click="activeTab = 'settings'"
              class="flex-1 px-3 py-3 text-xs font-medium transition-colors"
              :class="
                activeTab === 'settings'
                  ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50/50'
                  : 'text-gray-600'
              "
            >
              <div class="flex flex-col items-center gap-1">
                <LuSettings2 class="w-4 h-4" />
                <span>Settings</span>
              </div>
            </button>
            <button
              @click="activeTab = 'preview'"
              :disabled="!previewReady"
              class="flex-1 px-3 py-3 text-xs font-medium transition-colors disabled:opacity-50"
              :class="
                activeTab === 'preview'
                  ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50/50'
                  : 'text-gray-600'
              "
            >
              <div class="flex flex-col items-center gap-1">
                <LuPrinter class="w-4 h-4" />
                <span>Print</span>
              </div>
            </button>
          </div>
        </div>

        <div
          class="hidden md:block bg-white rounded-xl shadow-sm border border-gray-200 overflow-visible"
        >
          <div class="flex border-b border-gray-200">
            <button
              @click="activeTab = 'items'"
              class="flex-1 px-4 py-3 text-sm font-medium transition-colors flex items-center justify-center gap-2"
              :class="
                activeTab === 'items'
                  ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50/50'
                  : 'text-gray-600 hover:text-gray-900'
              "
            >
              <LuPackage class="w-4 h-4" />
              Select Samples
              <span
                v-if="selectedCount > 0"
                class="ml-1 px-2 py-0.5 bg-blue-600 text-white text-xs rounded-full"
                >{{ selectedCount }}</span
              >
            </button>
            <button
              @click="activeTab = 'settings'"
              class="flex-1 px-4 py-3 text-sm font-medium transition-colors flex items-center justify-center gap-2"
              :class="
                activeTab === 'settings'
                  ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50/50'
                  : 'text-gray-600 hover:text-gray-900'
              "
            >
              <LuSettings2 class="w-4 h-4" />
              Label Settings
            </button>
            <button
              @click="activeTab = 'preview'"
              :disabled="!previewReady"
              class="flex-1 px-4 py-3 text-sm font-medium transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
              :class="
                activeTab === 'preview'
                  ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50/50'
                  : 'text-gray-600 hover:text-gray-900'
              "
            >
              <LuEye class="w-4 h-4" />
              Preview & Print
            </button>
          </div>

          <div v-show="activeTab === 'items'" class="p-4 sm:p-6 space-y-4">
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center gap-3">
                <input
                  type="checkbox"
                  :checked="allSelected"
                  :indeterminate="someSelected"
                  @change="toggleAll"
                  class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                />
                <span class="text-sm font-medium text-gray-700">
                  {{ allSelected ? "Deselect All" : "Select All" }}
                </span>
              </div>
              <span class="text-sm text-gray-500"
                >{{ selectedCount }} of
                {{ filteredSamples.filter((sample) => sample.uid).length }} selected</span
              >
            </div>

            <div v-if="loading" class="flex items-center justify-center py-12">
              <LuLoader2 class="w-8 h-8 text-blue-600 animate-spin" />
            </div>

            <div v-else-if="filteredSamples.length === 0" class="text-center py-12">
              <LuPackageX class="w-12 h-12 text-gray-300 mx-auto mb-3" />
              <p class="text-gray-500">No research samples found</p>
            </div>

            <div v-else class="border border-gray-200 rounded-lg overflow-hidden">
              <div class="overflow-x-auto">
                <table class="w-full text-sm">
                  <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                      <th class="px-4 py-3 w-10"></th>
                      <th class="px-4 py-3 text-left">Sample</th>
                      <th class="px-4 py-3 text-left">UID</th>
                      <th class="px-4 py-3 text-left">Study / Experiment</th>
                      <th class="px-4 py-3 text-right w-24">Qty</th>
                      <th class="px-4 py-3 text-left">Retrieve</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr
                      v-for="sample in filteredSamples"
                      :key="sampleKey(sample)"
                      class="hover:bg-gray-50 transition-colors cursor-pointer"
                      :class="{ 'bg-blue-50/50': selected[sampleKey(sample)] }"
                      @dblclick="toggleSample(sample)"
                    >
                      <td class="px-4 py-3">
                        <input
                          type="checkbox"
                          :checked="!!selected[sampleKey(sample)]"
                          @change="toggleSample(sample)"
                          class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                        />
                      </td>
                      <td class="px-4 py-3">
                        <div class="text-gray-900 font-semibold">
                          {{ sample.accession_name || sample.uid }}
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5">
                          {{ sample.sample_type || "Research Sample"
                          }}<span v-if="sample.current_status">
                            • {{ sample.current_status }}</span
                          >
                        </div>
                        <div
                          v-if="sample.pr_code || sample.line_label"
                          class="text-xs text-gray-500 mt-0.5"
                        >
                          {{ sample.pr_code || sample.line_label }}
                        </div>
                      </td>
                      <td class="px-4 py-3">
                        <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">{{
                          sample.uid
                        }}</span>
                      </td>
                      <td class="px-4 py-3">
                        <div class="text-gray-900">
                          {{ sample.experiment?.study?.title || "Study" }}
                        </div>
                        <div class="text-xs text-gray-500">
                          {{ sample.experiment?.title || "Experiment pending" }}
                        </div>
                      </td>
                      <td class="px-4 py-3 text-right">
                        <input
                          v-if="selected[sampleKey(sample)]"
                          type="number"
                          min="1"
                          :value="selected[sampleKey(sample)]?.qty ?? 1"
                          @input="updateQty(sampleKey(sample), $event.target.value)"
                          class="w-16 px-2 py-1 text-right border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                        />
                        <span v-else class="text-gray-400">—</span>
                      </td>
                      <td class="px-4 py-3">
                        <button
                          type="button"
                          class="rounded-lg border px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50"
                          @click.stop="lookup(sample.uid)"
                        >
                          Retrieve
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="flex justify-end pt-4">
              <button
                @click="nextStep"
                :disabled="selectedCount === 0"
                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors flex items-center gap-2"
              >
                Continue to Settings
                <LuArrowRight class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div v-show="activeTab === 'settings'" class="p-4 sm:p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-3">
                <label class="text-sm font-medium text-gray-700">Print Mode</label>
                <div class="grid grid-cols-3 gap-2">
                  <button
                    v-for="mode in printModeOptions"
                    :key="mode.name"
                    @click="printMode = mode.name"
                    class="flex flex-col items-center gap-2 p-3 border-2 rounded-lg transition-all"
                    :class="
                      printMode === mode.name
                        ? 'border-blue-600 bg-blue-50 text-blue-700'
                        : 'border-gray-200 hover:border-gray-300'
                    "
                  >
                    <component :is="mode.icon" class="w-6 h-6" />
                    <span class="text-xs font-medium">{{ mode.label }}</span>
                  </button>
                </div>
              </div>

              <div class="space-y-3">
                <label class="text-sm font-medium text-gray-700">Label Size</label>
                <select
                  v-model="sizeTemplate"
                  class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm"
                >
                  <option
                    v-for="template in sizeTemplates"
                    :key="template.key"
                    :value="template.key"
                  >
                    {{ template.label }}
                  </option>
                </select>
                <div v-if="isCustomSize" class="flex gap-2">
                  <div class="flex-1">
                    <label class="text-xs text-gray-500">Height (cm)</label>
                    <input
                      v-model.number="customHeightCm"
                      type="number"
                      min="0.5"
                      step="0.1"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    />
                  </div>
                  <div class="flex-1">
                    <label class="text-xs text-gray-500">Width (cm)</label>
                    <input
                      v-model.number="customWidthCm"
                      type="number"
                      min="0.5"
                      step="0.1"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    />
                  </div>
                </div>
              </div>

              <div class="space-y-3">
                <label class="text-sm font-medium text-gray-700">Layout</label>
                <div class="flex gap-2">
                  <button
                    @click="layoutMode = 'single'"
                    class="flex-1 px-4 py-2 border-2 rounded-lg text-sm font-medium transition-colors"
                    :class="
                      layoutMode === 'single'
                        ? 'border-blue-600 bg-blue-50 text-blue-700'
                        : 'border-gray-200'
                    "
                  >
                    Single Label
                  </button>
                  <button
                    @click="layoutMode = 'sheet'"
                    class="flex-1 px-4 py-2 border-2 rounded-lg text-sm font-medium transition-colors"
                    :class="
                      layoutMode === 'sheet'
                        ? 'border-blue-600 bg-blue-50 text-blue-700'
                        : 'border-gray-200'
                    "
                  >
                    Sheet Layout
                  </button>
                </div>
              </div>

              <div class="space-y-3">
                <label class="text-sm font-medium text-gray-700">Orientation</label>
                <div class="flex gap-2">
                  <button
                    @click="orientation = 'portrait'"
                    class="flex-1 px-4 py-2 border-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2"
                    :class="
                      orientation === 'portrait'
                        ? 'border-blue-600 bg-blue-50 text-blue-700'
                        : 'border-gray-200'
                    "
                  >
                    <LuSmartphone class="w-4 h-4" />
                    Portrait
                  </button>
                  <button
                    @click="orientation = 'landscape'"
                    class="flex-1 px-4 py-2 border-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2"
                    :class="
                      orientation === 'landscape'
                        ? 'border-blue-600 bg-blue-50 text-blue-700'
                        : 'border-gray-200'
                    "
                  >
                    <LuSmartphone class="w-4 h-4 rotate-90" />
                    Landscape
                  </button>
                </div>
              </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
              <h4 class="text-sm font-medium text-gray-900 mb-4 flex items-center gap-2">
                <LuSlidersHorizontal class="w-4 h-4" />
                Advanced Settings
              </h4>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                  <label class="text-xs text-gray-500 block mb-1">Font Size</label>
                  <input
                    v-model.number="customFontSize"
                    type="number"
                    min="6"
                    max="20"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                  />
                </div>
                <div v-if="hasBarcodeMode">
                  <label class="text-xs text-gray-500 block mb-1">Barcode Height</label>
                  <input
                    v-model.number="customBarcodeHeight"
                    type="number"
                    min="12"
                    :max="maxBarcodeHeightPx"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                  />
                </div>
                <div v-if="hasQrMode">
                  <label class="text-xs text-gray-500 block mb-1">QR Size</label>
                  <input
                    v-model.number="customQRSize"
                    type="number"
                    min="20"
                    :max="maxQrSizePx"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                  />
                </div>
                <div>
                  <label class="text-xs text-gray-500 block mb-1">Rotation</label>
                  <select
                    v-model.number="rotationDeg"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                  >
                    <option :value="0">0°</option>
                    <option :value="90">90°</option>
                    <option :value="180">180°</option>
                    <option :value="270">270°</option>
                  </select>
                </div>
              </div>

              <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                  <input
                    v-model="flipHorizontal"
                    type="checkbox"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  />
                  Flip Horizontal
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                  <input
                    v-model="flipVertical"
                    type="checkbox"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  />
                  Flip Vertical
                </label>
                <div v-if="layoutMode === 'sheet'">
                  <label class="text-xs text-gray-500 block mb-1">Sheet Size</label>
                  <select
                    v-model="sheetSize"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                  >
                    <option value="a4">A4</option>
                    <option value="folio">Folio</option>
                  </select>
                </div>
                <div v-if="layoutMode === 'sheet'">
                  <label class="text-xs text-gray-500 block mb-1"
                    >Sheet Margin (cm)</label
                  >
                  <input
                    v-model.number="sheetMarginCm"
                    type="number"
                    min="0"
                    step="0.1"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                  />
                </div>
              </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
              <button
                @click="prevStep"
                class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
              >
                Back
              </button>
              <button
                @click="buildLabels"
                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2"
              >
                <LuSparkles class="w-4 h-4" />
                Generate Preview
              </button>
            </div>
          </div>

          <div
            v-show="activeTab === 'preview'"
            class="p-4 sm:p-6 space-y-6 overflow-visible"
          >
            <div v-if="!previewReady" class="text-center py-12">
              <LuEyeOff class="w-12 h-12 text-gray-300 mx-auto mb-3" />
              <p class="text-gray-500">Generate a preview first</p>
            </div>

            <template v-else>
              <div class="bg-gray-100 rounded-xl p-4 sm:p-8 overflow-visible">
                <div class="flex flex-wrap justify-center gap-4">
                  <div
                    v-for="label in labels"
                    :key="label.key"
                    @mouseenter="hoveredKey = label.key"
                    @mouseleave="hoveredKey = null"
                    :class="[
                      'transition-all duration-300',
                      hoveredKey && hoveredKey !== label.key
                        ? 'blur-sm scale-95 opacity-60'
                        : 'blur-0 scale-100 opacity-100 hover:z-10',
                    ]"
                    @click="openLabelModal(label)"
                  >
                    <LabelCard
                      :label="label"
                      :print-mode="printMode"
                      :label-font-size="labelFontSize"
                      :qr-size="qrSize"
                      :barcode-height="barcodeHeight"
                      :barcode-module-width="barcodeModuleWidth(label.item?.barcode)"
                      :card-style="cardStyle"
                      :card-inner-style="cardInnerStyle"
                    />
                  </div>
                </div>
              </div>

              <div class="flex flex-col sm:flex-row justify-end gap-3">
                <button
                  @click="prevStep"
                  class="px-4 py-2.5 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                >
                  Back to Settings
                </button>
                <button
                  @click="exportPdf"
                  :disabled="exporting"
                  class="px-4 py-2.5 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors flex items-center justify-center gap-2 disabled:opacity-50"
                >
                  <LuFileDown v-if="!exporting" class="w-4 h-4" />
                  <LuLoader2 v-else class="w-4 h-4 animate-spin" />
                  {{ exporting ? "Exporting..." : "Export PDF" }}
                </button>
                <button
                  @click="printLabels"
                  class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2"
                >
                  <LuPrinter class="w-4 h-4" />
                  Print Labels
                </button>
              </div>
            </template>
          </div>
        </div>

        <div class="md:hidden space-y-4">
          <div
            v-if="activeTab === 'items'"
            class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 space-y-4"
          >
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center gap-3">
                <input
                  type="checkbox"
                  :checked="allSelected"
                  :indeterminate="someSelected"
                  @change="toggleAll"
                  class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                />
                <span class="text-sm font-medium text-gray-700">{{
                  allSelected ? "Deselect All" : "Select All"
                }}</span>
              </div>
              <span class="text-sm text-gray-500">{{ selectedCount }} selected</span>
            </div>

            <div v-if="loading" class="flex justify-center py-8">
              <LuLoader2 class="w-6 h-6 text-blue-600 animate-spin" />
            </div>

            <div
              v-else-if="filteredSamples.length === 0"
              class="text-center py-8 text-gray-500"
            >
              No samples found
            </div>

            <div v-else class="space-y-2">
              <div
                v-for="sample in filteredSamples"
                :key="sampleKey(sample)"
                class="p-3 border border-gray-200 rounded-lg"
                :class="{ 'bg-blue-50 border-blue-300': selected[sampleKey(sample)] }"
              >
                <div class="flex items-start gap-3">
                  <input
                    type="checkbox"
                    :checked="!!selected[sampleKey(sample)]"
                    @change="toggleSample(sample)"
                    class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 mt-1"
                  />
                  <div class="flex-1 min-w-0">
                    <div class="font-medium text-gray-900 text-sm">
                      {{ sample.accession_name || sample.uid }}
                    </div>
                    <div class="text-xs text-gray-500">
                      {{ sample.sample_type || "Research Sample" }}
                    </div>
                    <div
                      class="mt-1 font-mono text-xs bg-gray-100 px-2 py-0.5 rounded inline-block"
                    >
                      {{ sample.uid }}
                    </div>

                    <div
                      v-if="selected[sampleKey(sample)]"
                      class="mt-2 flex items-center gap-2"
                    >
                      <label class="text-xs text-gray-500">Qty:</label>
                      <input
                        type="number"
                        min="1"
                        :value="selected[sampleKey(sample)]?.qty ?? 1"
                        @input="updateQty(sampleKey(sample), $event.target.value)"
                        class="w-16 px-2 py-1 text-sm border border-gray-300 rounded"
                      />
                    </div>

                    <button
                      type="button"
                      class="mt-3 rounded-lg border px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50"
                      @click="lookup(sample.uid)"
                    >
                      Retrieve
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <button
              @click="nextStep"
              :disabled="selectedCount === 0"
              class="w-full py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2"
            >
              Continue
              <LuArrowRight class="w-4 h-4" />
            </button>
          </div>

          <div
            v-if="activeTab === 'settings'"
            class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 space-y-4"
          >
            <div class="space-y-3">
              <label class="text-sm font-medium text-gray-700">Print Mode</label>
              <div class="grid grid-cols-3 gap-2">
                <button
                  v-for="mode in printModeOptions"
                  :key="mode.name"
                  @click="printMode = mode.name"
                  class="flex flex-col items-center gap-1 p-2 border-2 rounded-lg transition-all text-xs"
                  :class="
                    printMode === mode.name
                      ? 'border-blue-600 bg-blue-50 text-blue-700'
                      : 'border-gray-200'
                  "
                >
                  <component :is="mode.icon" class="w-5 h-5" />
                  <span class="font-medium">{{ mode.label }}</span>
                </button>
              </div>
            </div>

            <div class="space-y-3">
              <label class="text-sm font-medium text-gray-700">Label Size</label>
              <select
                v-model="sizeTemplate"
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm"
              >
                <option
                  v-for="template in sizeTemplates"
                  :key="template.key"
                  :value="template.key"
                >
                  {{ template.label }}
                </option>
              </select>
              <div v-if="isCustomSize" class="grid grid-cols-2 gap-2">
                <div>
                  <label class="text-xs text-gray-500">Height (cm)</label>
                  <input
                    v-model.number="customHeightCm"
                    type="number"
                    min="0.5"
                    step="0.1"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                  />
                </div>
                <div>
                  <label class="text-xs text-gray-500">Width (cm)</label>
                  <input
                    v-model.number="customWidthCm"
                    type="number"
                    min="0.5"
                    step="0.1"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                  />
                </div>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <button
                @click="layoutMode = 'single'"
                class="px-4 py-2 border-2 rounded-lg text-sm font-medium transition-colors"
                :class="
                  layoutMode === 'single'
                    ? 'border-blue-600 bg-blue-50 text-blue-700'
                    : 'border-gray-200'
                "
              >
                Single Label
              </button>
              <button
                @click="layoutMode = 'sheet'"
                class="px-4 py-2 border-2 rounded-lg text-sm font-medium transition-colors"
                :class="
                  layoutMode === 'sheet'
                    ? 'border-blue-600 bg-blue-50 text-blue-700'
                    : 'border-gray-200'
                "
              >
                Sheet Layout
              </button>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <button
                @click="orientation = 'portrait'"
                class="px-4 py-2 border-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2"
                :class="
                  orientation === 'portrait'
                    ? 'border-blue-600 bg-blue-50 text-blue-700'
                    : 'border-gray-200'
                "
              >
                <LuSmartphone class="w-4 h-4" />
                Portrait
              </button>
              <button
                @click="orientation = 'landscape'"
                class="px-4 py-2 border-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2"
                :class="
                  orientation === 'landscape'
                    ? 'border-blue-600 bg-blue-50 text-blue-700'
                    : 'border-gray-200'
                "
              >
                <LuSmartphone class="w-4 h-4 rotate-90" />
                Landscape
              </button>
            </div>

            <div class="border-t border-gray-200 pt-4 space-y-3">
              <h4 class="text-sm font-medium text-gray-900 flex items-center gap-2">
                <LuSlidersHorizontal class="w-4 h-4" />
                Advanced
              </h4>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="text-xs text-gray-500 block mb-1">Font Size</label>
                  <input
                    v-model.number="customFontSize"
                    type="number"
                    min="6"
                    max="20"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                  />
                </div>
                <div v-if="hasBarcodeMode">
                  <label class="text-xs text-gray-500 block mb-1">Barcode Height</label>
                  <input
                    v-model.number="customBarcodeHeight"
                    type="number"
                    min="12"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                  />
                </div>
              </div>
            </div>

            <div class="flex gap-3 pt-4">
              <button
                @click="prevStep"
                class="flex-1 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
              >
                Back
              </button>
              <button
                @click="buildLabels"
                class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2"
              >
                <LuSparkles class="w-4 h-4" />
                Preview
              </button>
            </div>
          </div>

          <div
            v-if="activeTab === 'preview'"
            class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 space-y-4"
          >
            <div v-if="!previewReady" class="text-center py-8">
              <LuEyeOff class="w-12 h-12 text-gray-300 mx-auto mb-3" />
              <p class="text-gray-500">Generate a preview first</p>
            </div>

            <template v-else>
              <div class="bg-gray-100 rounded-lg p-4 overflow-x-auto">
                <div class="flex flex-wrap justify-center gap-3">
                  <div
                    v-for="label in labels.slice(0, 6)"
                    :key="label.key"
                    @click="openLabelModal(label)"
                  >
                    <LabelCard
                      :label="label"
                      :print-mode="printMode"
                      :label-font-size="Math.max(8, labelFontSize - 2)"
                      :qr-size="Math.min(60, qrSize)"
                      :barcode-height="Math.max(20, Math.min(barcodeHeight, 30))"
                      :barcode-module-width="barcodeModuleWidth(label.item?.barcode)"
                      :card-style="{
                        width: '140px',
                        height: 'auto',
                        aspectRatio: `${resolvedWidthCm}/${resolvedHeightCm}`,
                      }"
                      :card-inner-style="cardInnerStyle"
                    />
                  </div>
                </div>
                <div
                  v-if="labels.length > 6"
                  class="text-center mt-3 text-xs text-gray-500"
                >
                  Showing 6 of {{ labels.length }} labels
                </div>
              </div>

              <div class="grid grid-cols-2 gap-3">
                <button
                  @click="prevStep"
                  class="px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors text-sm"
                >
                  Back
                </button>
                <button
                  @click="exportPdf"
                  :disabled="exporting"
                  class="px-4 py-3 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors flex items-center justify-center gap-2 disabled:opacity-50 text-sm"
                >
                  <LuFileDown v-if="!exporting" class="w-4 h-4" />
                  <LuLoader2 v-else class="w-4 h-4 animate-spin" />
                  PDF
                </button>
              </div>
              <button
                @click="printLabels"
                class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2"
              >
                <LuPrinter class="w-4 h-4" />
                Print {{ totalLabels }} Labels
              </button>
            </template>
          </div>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <div
        v-if="previewReady && layoutMode === 'single'"
        class="print-area flex flex-wrap justify-center gap-4"
      >
        <LabelCard
          v-for="label in labels"
          :key="label.key"
          :label="label"
          :print-mode="printMode"
          :label-font-size="labelFontSize"
          :qr-size="qrSize"
          :barcode-height="barcodeHeight"
          :barcode-module-width="barcodeModuleWidth(label.item?.barcode)"
          :card-style="cardStyle"
          :card-inner-style="cardInnerStyle"
        />
      </div>

      <div v-if="previewReady && layoutMode === 'sheet'" class="print-area-sheet">
        <div
          v-for="(sheet, sheetIndex) in sheetedLabels"
          :key="`print-sheet-${sheetIndex}`"
          class="sheet-page"
          :style="{
            width: `${sheetDimensions.widthCm}cm`,
            height: `${sheetDimensions.heightCm}cm`,
            padding: `${sheetMarginCm}cm`,
          }"
        >
          <div
            class="sheet-grid"
            :style="{
              display: 'grid',
              gridTemplateColumns: `repeat(${labelsPerRow}, 1fr)`,
              gap: '5px',
            }"
          >
            <LabelCard
              v-for="label in sheet"
              :key="label.key"
              :label="label"
              :print-mode="printMode"
              :label-font-size="labelFontSize"
              :qr-size="qrSize"
              :barcode-height="barcodeHeight"
              :barcode-module-width="barcodeModuleWidth(label.item?.barcode)"
              :card-style="cardStyle"
              :card-inner-style="cardInnerStyle"
            />
          </div>
        </div>
      </div>
    </Teleport>

    <DialogModal :show="showLabelModal" @close="closeLabelModal" max-width="2xl">
      <template #content>
        <div class="flex justify-center items-center py-8" v-if="selectedLabelForModal">
          <LabelCard
            :label="selectedLabelForModal"
            :print-mode="printMode"
            :label-font-size="labelFontSize * 2.4"
            :qr-size="qrSize * 2.5"
            :barcode-height="barcodeHeight * 2.5"
            :barcode-module-width="
              barcodeModuleWidth(selectedLabelForModal.item?.barcode)
            "
            :card-style="modalCardStyle"
            :card-inner-style="cardInnerStyle"
          />
        </div>
      </template>
      <template #footer>
        <button
          @click="closeLabelModal"
          class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
        >
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

.label-qr canvas,
.label-qr svg {
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

.print-area,
.print-area-sheet {
  display: none;
}

@media print {
  html,
  body {
    margin: 0;
    padding: 0;
  }

  body > *:not(.print-area):not(.print-area-sheet) {
    display: none;
  }

  .print-area,
  .print-area-sheet {
    position: absolute;
    left: 0;
    top: 0;
    margin: 0;
    padding: 0;
    display: block;
    visibility: visible;
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
    width: 100vw;
    height: 100vh;
    max-width: 100vw;
    max-height: 100vh;
    border: none;
    border-radius: 0;
    padding: 0.2rem;
    margin: 0;
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
    width: 100%;
    height: 100%;
    page-break-after: always;
    break-after: page;
    page-break-inside: avoid;
    break-inside: avoid-page;
    margin: 0;
    background: #ffffff;
    box-sizing: border-box;
  }

  .sheet-page:last-child {
    page-break-after: auto;
    break-after: auto;
  }

  .sheet-grid {
    width: 100%;
    height: 100%;
    display: grid;
    gap: 0;
  }

  .sheet-page .label-card {
    page-break-inside: avoid;
    break-inside: avoid-page;
    page-break-after: auto;
    break-after: auto;
    border: none;
    border-radius: 0;
    padding: 0;
    margin: 0;
    width: 100%;
    height: 100%;
    box-sizing: border-box;
  }
}
</style>
