<script>
import axios from 'axios'
import JsBarcode from 'jsbarcode'
import QrcodeVue from 'qrcode.vue'
import CameraScanner from '@/Components/CameraScanner.vue'

export default {
    name: 'ResearchSampleInventory',
    components: {
        QrcodeVue,
        CameraScanner,
    },
    data() {
        return {
            loading: false,
            searching: false,
            scannerOpen: false,
            query: '',
            samples: [],
            selectedIds: [],
            selectedSample: null,
            scanMessage: '',
            scanError: '',
        }
    },
    computed: {
        allSelected() {
            return this.samples.length > 0 && this.selectedIds.length === this.samples.length
        },
        selectedSamples() {
            return this.samples.filter((sample) => this.selectedIds.includes(sample.id))
        },
    },
    mounted() {
        this.loadSamples()
    },
    methods: {
        async loadSamples() {
            this.loading = true
            this.scanError = ''

            try {
                const response = await axios.get(route('api.research.samples.inventory.index'), {
                    params: {
                        search: this.query,
                        limit: 200,
                    },
                })

                this.samples = response.data?.data || []
                this.$nextTick(() => this.renderBarcodes())
            } catch (error) {
                this.scanError = error?.response?.data?.message || 'Unable to load sample inventory.'
            } finally {
                this.loading = false
            }
        },
        async lookup(uid, source = 'manual_lookup') {
            this.searching = true
            this.scanError = ''
            this.scanMessage = ''

            try {
                const response = await axios.get(route('api.research.samples.inventory.lookup', uid), {
                    params: {
                        source,
                        qr_payload: `sample:${uid}`,
                    },
                })

                this.selectedSample = response.data?.data || null
                this.scanMessage = `Sample ${uid} loaded for retrieval and monitoring.`
                this.$nextTick(() => this.renderSingleBarcode())
            } catch (error) {
                this.selectedSample = null
                this.scanError = error?.response?.data?.message || 'No sample matched the scanned code.'
            } finally {
                this.searching = false
            }
        },
        async onScan(payload) {
            this.searching = true
            this.scanError = ''
            this.scanMessage = ''

            try {
                const response = await axios.post(route('api.research.samples.inventory.scan'), {
                    payload,
                    source: 'camera_scan',
                })

                this.selectedSample = response.data?.data || null
                this.scanMessage = `Scan matched sample ${this.selectedSample?.uid || ''}.`
                this.$nextTick(() => this.renderSingleBarcode())
            } catch (error) {
                this.selectedSample = null
                this.scanError = error?.response?.data?.message || 'Scanned code is not linked to a research sample.'
            } finally {
                this.searching = false
            }
        },
        toggleSelectAll() {
            if (this.allSelected) {
                this.selectedIds = []
                return
            }

            this.selectedIds = this.samples.map((sample) => sample.id)
        },
        async printSelected() {
            if (!this.selectedIds.length) {
                this.scanError = 'Select at least one sample to print labels.'
                return
            }

            await axios.post(route('api.research.samples.inventory.labels.print'), {
                sample_ids: this.selectedIds,
            })

            window.print()
        },
        qrPayload(sample) {
            return `sample:${sample.uid}|experiment:${sample.experiment_id}|sample_id:${sample.id}`
        },
        renderBarcodes() {
            this.samples.forEach((sample) => {
                const target = document.getElementById(`research-sample-barcode-${sample.id}`)
                if (!target) {
                    return
                }

                JsBarcode(target, sample.uid, {
                    format: 'CODE128',
                    width: 1.6,
                    height: 42,
                    displayValue: false,
                    margin: 0,
                })
            })
        },
        renderSingleBarcode() {
            if (!this.selectedSample) {
                return
            }

            const target = document.getElementById('research-selected-sample-barcode')
            if (!target) {
                return
            }

            JsBarcode(target, this.selectedSample.uid, {
                format: 'CODE128',
                width: 2,
                height: 54,
                displayValue: false,
                margin: 0,
            })
        },
    },
}
</script>

<template>
    <AppLayout title="Research Sample Inventory">
        <template #header>
            <ActionHeaderLayout
                title="Research Sample Inventory"
                subtitle="Barcode-driven audit inventory with QR retrieval for research samples."
                :route-link="route('research.samples.inventory')"
            >
                <Link :href="`${route('manuals.index')}?section=researchMonitoring`" class="rounded-lg border border-white/25 px-4 py-2 text-sm font-medium text-white hover:bg-white/10">
                    Manuals & Guides
                </Link>
                <Link :href="route('research.dashboard')" class="rounded-lg bg-white/15 px-4 py-2 text-sm font-medium text-white hover:bg-white/25">
                    Dashboard
                </Link>
            </ActionHeaderLayout>
        </template>

        <div class="mx-auto max-w-7xl space-y-6 px-4 py-6">
            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="grid gap-4 md:grid-cols-[1fr_auto]">
                    <div class="flex gap-3">
                        <input
                            v-model="query"
                            class="w-full rounded-lg border-gray-300"
                            placeholder="Search by UID, accession name, PR code, or line label"
                            @keyup.enter="loadSamples"
                        />
                        <button type="button" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black" @click="loadSamples">
                            Search
                        </button>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" class="rounded-lg border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="scannerOpen = !scannerOpen">
                            {{ scannerOpen ? 'Hide Scanner' : 'Open Scanner' }}
                        </button>
                        <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700" @click="printSelected">
                            Print Selected Labels
                        </button>
                    </div>
                </div>

                <div v-if="scanError" class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ scanError }}
                </div>
                <div v-if="scanMessage" class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ scanMessage }}
                </div>
            </section>

            <section v-if="scannerOpen" class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-2xl font-semibold text-gray-900">Scan Research Sample Code</h2>
                <p class="mt-2 text-sm text-gray-600">Use barcode for audit/inventory workflows and QR for retrieval, printing, and presentation view.</p>
                <div class="mt-4">
                    <CameraScanner :enabled="true" :model-value="true" border-color="emerald" @decoded="onScan" />
                </div>
            </section>

            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-semibold text-gray-900">Sample Inventory List</h2>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input :checked="allSelected" type="checkbox" @change="toggleSelectAll" />
                        Select all
                    </label>
                </div>

                <div v-if="loading" class="mt-4 space-y-3">
                    <div class="h-16 animate-pulse rounded-xl bg-gray-100" />
                    <div class="h-16 animate-pulse rounded-xl bg-gray-100" />
                    <div class="h-16 animate-pulse rounded-xl bg-gray-100" />
                </div>

                <div v-else-if="!samples.length" class="mt-4 rounded-2xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500">
                    No samples found in inventory yet.
                </div>

                <div v-else class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left font-semibold text-gray-700">Pick</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-700">UID</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-700">Accession</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-700">Barcode</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-700">QR</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <tr v-for="sample in samples" :key="sample.id">
                                <td class="px-3 py-3">
                                    <input v-model="selectedIds" :value="sample.id" type="checkbox" />
                                </td>
                                <td class="px-3 py-3 font-semibold text-gray-900">{{ sample.uid }}</td>
                                <td class="px-3 py-3 text-gray-700">{{ sample.accession_name }}</td>
                                <td class="px-3 py-3">
                                    <svg :id="`research-sample-barcode-${sample.id}`" class="h-12 w-40"></svg>
                                </td>
                                <td class="px-3 py-3">
                                    <QrcodeVue :value="qrPayload(sample)" :size="56" level="M" render-as="canvas" />
                                </td>
                                <td class="px-3 py-3">
                                    <button type="button" class="rounded-lg border px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50" @click="lookup(sample.uid)">
                                        Retrieve
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section v-if="selectedSample" class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-2xl font-semibold text-gray-900">Selected Sample Snapshot</h2>
                <p class="mt-2 text-sm text-gray-600">{{ selectedSample.uid }} • {{ selectedSample.accession_name }}</p>

                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl bg-gray-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">Barcode</p>
                        <svg id="research-selected-sample-barcode" class="mt-3 h-16 w-56"></svg>
                        <p class="mt-2 text-xs font-semibold text-gray-700">{{ selectedSample.uid }}</p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">QR Retrieval Payload</p>
                        <div class="mt-3 inline-block rounded-lg bg-white p-2">
                            <QrcodeVue :value="qrPayload(selectedSample)" :size="92" level="M" render-as="canvas" />
                        </div>
                        <p class="mt-2 text-xs text-gray-700">Scan to retrieve display payload.</p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-4 text-sm text-gray-700">
                        <p><span class="font-semibold text-gray-900">Project:</span> {{ selectedSample.experiment?.study?.project?.title || 'N/A' }}</p>
                        <p class="mt-2"><span class="font-semibold text-gray-900">Study:</span> {{ selectedSample.experiment?.study?.title || 'N/A' }}</p>
                        <p class="mt-2"><span class="font-semibold text-gray-900">Experiment:</span> {{ selectedSample.experiment?.title || 'N/A' }}</p>
                        <p class="mt-2"><span class="font-semibold text-gray-900">Status:</span> {{ selectedSample.current_status || 'Pending' }}</p>
                        <p class="mt-2"><span class="font-semibold text-gray-900">Location:</span> {{ selectedSample.current_location || selectedSample.storage_location || 'N/A' }}</p>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
