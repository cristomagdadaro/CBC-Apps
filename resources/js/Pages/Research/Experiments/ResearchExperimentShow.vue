<script>
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import KeyValueEditor from '@/Pages/Research/components/KeyValueEditor.vue'

export default {
    name: 'ResearchExperimentShow',
    components: {
        KeyValueEditor,
    },
    props: {
        experiment: {
            type: Object,
            required: true,
        },
        catalog: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            experimentForm: this.buildExperimentForm(this.experiment),
            newSampleForm: this.defaultSampleForm(this.experiment.id),
            sampleForms: this.buildSampleForms(this.experiment.samples || []),
            recordForms: this.buildRecordForms(this.experiment.samples || []),
            experimentErrors: {},
            sampleCreateErrors: {},
            sampleErrors: {},
            recordErrors: {},
            savingExperiment: false,
            deletingExperiment: false,
            creatingSample: false,
            savingSamples: {},
            deletingSamples: {},
            creatingRecords: {},
            deletingRecords: {},
        }
    },
    computed: {
        permissions() {
            return this.$page.props.auth?.permissions || []
        },
        canExport() {
            return this.permissions.includes('*') || this.permissions.includes('research.exports.manage')
        },
    },
    methods: {
        buildExperimentForm(experiment) {
            return {
                title: experiment?.title || '',
                geographic_location: experiment?.geographic_location || '',
                season: experiment?.season || 'wet',
                commodity: experiment?.commodity || 'Rice',
                sample_type: experiment?.sample_type || 'Seeds',
                sample_descriptor: experiment?.sample_descriptor || '',
                pr_code: experiment?.pr_code || '',
                cross_combination: experiment?.cross_combination || '',
                parental_background: experiment?.parental_background || '',
                filial_generation: experiment?.filial_generation || '',
                generation: experiment?.generation || '',
                plot_number: experiment?.plot_number || '',
                field_number: experiment?.field_number || '',
                replication_number: experiment?.replication_number || '',
                planned_plant_count: experiment?.planned_plant_count || '',
                background_notes: experiment?.background_notes || '',
            }
        },
        defaultSampleForm(experimentId) {
            return {
                experiment_id: experimentId,
                commodity: this.experiment?.commodity || 'Rice',
                sample_type: this.experiment?.sample_type || 'Seeds',
                accession_name: '',
                pr_code: '',
                field_label: '',
                line_label: '',
                plant_label: '',
                generation: this.experiment?.generation || '',
                plot_number: this.experiment?.plot_number || '',
                field_number: this.experiment?.field_number || '',
                replication_number: this.experiment?.replication_number || '',
                current_status: 'Field',
                current_location: '',
                storage_location: '',
                germination_date: '',
                sowing_date: '',
                harvest_date: '',
                is_priority: false,
                legacy_reference: '',
            }
        },
        buildSampleForms(samples) {
            return samples.reduce((forms, sample) => {
                forms[sample.id] = {
                    commodity: sample.commodity || '',
                    sample_type: sample.sample_type || '',
                    accession_name: sample.accession_name || '',
                    pr_code: sample.pr_code || '',
                    field_label: sample.field_label || '',
                    line_label: sample.line_label || '',
                    plant_label: sample.plant_label || '',
                    generation: sample.generation || '',
                    plot_number: sample.plot_number || '',
                    field_number: sample.field_number || '',
                    replication_number: sample.replication_number || '',
                    current_status: sample.current_status || '',
                    current_location: sample.current_location || '',
                    storage_location: sample.storage_location || '',
                    germination_date: sample.germination_date || '',
                    sowing_date: sample.sowing_date || '',
                    harvest_date: sample.harvest_date || '',
                    is_priority: !!sample.is_priority,
                    legacy_reference: sample.legacy_reference || '',
                }
                return forms
            }, {})
        },
        buildRecordForms(samples) {
            return samples.reduce((forms, sample) => {
                forms[sample.id] = this.defaultRecordForm(sample.id)
                return forms
            }, {})
        },
        defaultRecordForm(sampleId) {
            return {
                sample_id: sampleId,
                stage: 'germination',
                recorded_on: new Date().toISOString().slice(0, 10),
                parameter_entries: this.stageSuggestions('germination'),
                notes: '',
                selected_for_export: false,
            }
        },
        stageSuggestions(stage) {
            return ((this.catalog.stages || {})[stage]?.suggested_parameters || []).map((parameter) => ({
                key: parameter,
                value: '',
            }))
        },
        stageLabel(stage) {
            return (this.catalog.stages || {})[stage]?.label || stage
        },
        sanitizeExperimentPayload() {
            return {
                ...this.experimentForm,
                replication_number: this.experimentForm.replication_number || null,
                planned_plant_count: this.experimentForm.planned_plant_count || null,
            }
        },
        sanitizeSamplePayload(form) {
            return {
                ...form,
                replication_number: form.replication_number || null,
            }
        },
        sanitizeRecordPayload(form) {
            const parameterSet = (form.parameter_entries || []).reduce((payload, row) => {
                if (row?.key) {
                    payload[row.key] = row?.value || ''
                }
                return payload
            }, {})

            return {
                sample_id: form.sample_id,
                stage: form.stage,
                recorded_on: form.recorded_on,
                parameter_set: parameterSet,
                notes: form.notes,
                selected_for_export: !!form.selected_for_export,
            }
        },
        fieldError(errors, field) {
            return errors?.[field]?.[0] || ''
        },
        resetRecordForm(sampleId, stage = 'germination') {
            this.recordForms = {
                ...this.recordForms,
                [sampleId]: {
                    sample_id: sampleId,
                    stage,
                    recorded_on: new Date().toISOString().slice(0, 10),
                    parameter_entries: this.stageSuggestions(stage),
                    notes: '',
                    selected_for_export: false,
                },
            }
        },
        handleRecordStageChange(sampleId) {
            const stage = this.recordForms[sampleId].stage
            this.recordForms = {
                ...this.recordForms,
                [sampleId]: {
                    ...this.recordForms[sampleId],
                    parameter_entries: this.stageSuggestions(stage),
                },
            }
        },
        async saveExperiment() {
            this.savingExperiment = true
            this.experimentErrors = {}

            try {
                await axios.put(route('api.research.experiments.update', this.experiment.id), this.sanitizeExperimentPayload())
                router.visit(route('research.experiments.show', this.experiment.id))
            } catch (error) {
                this.experimentErrors = error?.response?.data?.errors || {}
            } finally {
                this.savingExperiment = false
            }
        },
        async deleteExperiment() {
            if (!confirm('Delete this experiment and all linked samples and monitoring records?')) {
                return
            }

            this.deletingExperiment = true

            try {
                await axios.delete(route('api.research.experiments.destroy', this.experiment.id))
                router.visit(route('research.projects.show', this.experiment.study?.project?.id))
            } finally {
                this.deletingExperiment = false
            }
        },
        async createSample() {
            this.creatingSample = true
            this.sampleCreateErrors = {}

            try {
                await axios.post(route('api.research.samples.store'), this.sanitizeSamplePayload(this.newSampleForm))
                router.visit(route('research.experiments.show', this.experiment.id))
            } catch (error) {
                this.sampleCreateErrors = error?.response?.data?.errors || {}
            } finally {
                this.creatingSample = false
            }
        },
        async saveSample(sampleId) {
            this.savingSamples = { ...this.savingSamples, [sampleId]: true }
            this.sampleErrors = { ...this.sampleErrors, [sampleId]: {} }

            try {
                await axios.put(route('api.research.samples.update', sampleId), this.sanitizeSamplePayload(this.sampleForms[sampleId]))
                router.visit(route('research.experiments.show', this.experiment.id))
            } catch (error) {
                this.sampleErrors = {
                    ...this.sampleErrors,
                    [sampleId]: error?.response?.data?.errors || {},
                }
            } finally {
                this.savingSamples = { ...this.savingSamples, [sampleId]: false }
            }
        },
        async deleteSample(sampleId) {
            if (!confirm('Delete this sample and its monitoring records?')) {
                return
            }

            this.deletingSamples = { ...this.deletingSamples, [sampleId]: true }

            try {
                await axios.delete(route('api.research.samples.destroy', sampleId))
                router.visit(route('research.experiments.show', this.experiment.id))
            } finally {
                this.deletingSamples = { ...this.deletingSamples, [sampleId]: false }
            }
        },
        async createRecord(sampleId) {
            this.creatingRecords = { ...this.creatingRecords, [sampleId]: true }
            this.recordErrors = { ...this.recordErrors, [sampleId]: {} }

            try {
                await axios.post(route('api.research.records.store'), this.sanitizeRecordPayload(this.recordForms[sampleId]))
                router.visit(route('research.experiments.show', this.experiment.id))
            } catch (error) {
                this.recordErrors = {
                    ...this.recordErrors,
                    [sampleId]: error?.response?.data?.errors || {},
                }
            } finally {
                this.creatingRecords = { ...this.creatingRecords, [sampleId]: false }
            }
        },
        async deleteRecord(recordId, sampleId) {
            if (!confirm('Delete this monitoring record?')) {
                return
            }

            this.deletingRecords = { ...this.deletingRecords, [recordId]: true }

            try {
                await axios.delete(route('api.research.records.destroy', recordId))
                this.resetRecordForm(sampleId)
                router.visit(route('research.experiments.show', this.experiment.id))
            } finally {
                this.deletingRecords = { ...this.deletingRecords, [recordId]: false }
            }
        },
    },
}
</script>

<template>
    <AppLayout :title="experiment.title">
        <template #header>
            <ActionHeaderLayout
                :title="experiment.title"
                subtitle="Manage experiment setup, samples, and stage-based monitoring records."
                :route-link="route('research.experiments.show', experiment.id)"
            >
                <Link :href="`${route('manuals.index')}?section=researchMonitoring`" class="rounded-lg border border-white/25 px-4 py-2 text-sm font-medium text-white hover:bg-white/10">
                    Manuals & Guides
                </Link>
                <Link :href="route('research.samples.inventory')" class="rounded-lg border border-white/25 px-4 py-2 text-sm font-medium text-white hover:bg-white/10">
                    Sample Inventory
                </Link>
                <Link :href="route('research.projects.show', experiment.study?.project?.id)" class="rounded-lg border border-white/25 px-4 py-2 text-sm font-medium text-white hover:bg-white/10">
                    Back to Project
                </Link>
                <a v-if="canExport" :href="route('api.research.experiments.export.samples', experiment.id)" class="rounded-lg bg-white/15 px-4 py-2 text-sm font-medium text-white hover:bg-white/25">
                    Export CSV
                </a>
            </ActionHeaderLayout>
        </template>

        <div class="mx-auto max-w-7xl space-y-6 px-4 py-6">
            <nav class="flex items-center gap-2 text-sm text-gray-500">
                <Link :href="route('research.dashboard')" class="hover:text-gray-700">Dashboard</Link>
                <span>/</span>
                <Link :href="route('research.projects.index')" class="hover:text-gray-700">Projects</Link>
                <span>/</span>
                <Link :href="route('research.projects.show', experiment.study?.project?.id)" class="hover:text-gray-700">{{ experiment.study?.project?.code || 'Project' }}</Link>
                <span>/</span>
                <span class="font-medium text-gray-700">{{ experiment.code }}</span>
            </nav>

            <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">{{ experiment.code }}</p>
                            <h2 class="mt-2 text-2xl font-semibold text-gray-900">Experiment profile</h2>
                            <p class="mt-2 text-sm text-gray-600">{{ experiment.study?.title }} / {{ experiment.study?.project?.title }}</p>
                        </div>
                        <div class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                            {{ (experiment.samples || []).length }} samples
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Experiment title</label>
                            <input v-model="experimentForm.title" class="w-full rounded-xl border-gray-300" />
                            <p v-if="fieldError(experimentErrors, 'title')" class="mt-1 text-xs text-red-600">{{ fieldError(experimentErrors, 'title') }}</p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Geographic location</label>
                            <input v-model="experimentForm.geographic_location" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Season</label>
                            <select v-model="experimentForm.season" class="w-full rounded-xl border-gray-300">
                                <option v-for="season in catalog.seasons || []" :key="season.value" :value="season.value">
                                    {{ season.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Commodity</label>
                            <input v-model="experimentForm.commodity" list="research-experiment-commodity-options" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Sample type</label>
                            <input v-model="experimentForm.sample_type" list="research-experiment-sample-options" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Sample descriptor</label>
                            <input v-model="experimentForm.sample_descriptor" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">PR code</label>
                            <input v-model="experimentForm.pr_code" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Generation</label>
                            <input v-model="experimentForm.generation" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Filial generation</label>
                            <input v-model="experimentForm.filial_generation" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Plot number</label>
                            <input v-model="experimentForm.plot_number" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Field number</label>
                            <input v-model="experimentForm.field_number" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Replication number</label>
                            <input v-model="experimentForm.replication_number" type="number" min="1" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Planned plant count</label>
                            <input v-model="experimentForm.planned_plant_count" type="number" min="0" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Cross combination</label>
                            <input v-model="experimentForm.cross_combination" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Parental background</label>
                            <textarea v-model="experimentForm.parental_background" rows="3" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Additional notes</label>
                            <textarea v-model="experimentForm.background_notes" rows="3" class="w-full rounded-xl border-gray-300" />
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap justify-end gap-3">
                        <button type="button" :disabled="deletingExperiment" class="rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50" @click="deleteExperiment">
                            {{ deletingExperiment ? 'Deleting...' : 'Delete Experiment' }}
                        </button>
                        <button type="button" :disabled="savingExperiment" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700" @click="saveExperiment">
                            {{ savingExperiment ? 'Saving...' : 'Save Experiment' }}
                        </button>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-widest text-emerald-700">Guides</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900">Stage instructions and barcode strategy</h2>
                        <p class="mt-3 text-sm leading-6 text-gray-600">Detailed stage descriptions and usage notes are now centralized in Manuals & Guides.</p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <Link :href="`${route('manuals.index')}?section=researchMonitoring`" class="rounded-lg border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Open Research Guide
                            </Link>
                            <Link :href="route('research.samples.inventory')" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">
                                Open Sample Inventory
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">New Sample</p>
                    <h2 class="mt-2 text-xl font-semibold text-gray-900">Register a new field or lab sample</h2>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div class="xl:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700">Sample / accession name</label>
                        <input v-model="newSampleForm.accession_name" class="w-full rounded-xl border-gray-300" placeholder="Ex. NSIC Rc222" />
                        <p v-if="fieldError(sampleCreateErrors, 'accession_name')" class="mt-1 text-xs text-red-600">{{ fieldError(sampleCreateErrors, 'accession_name') }}</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Sample type</label>
                        <input v-model="newSampleForm.sample_type" list="research-experiment-sample-options" class="w-full rounded-xl border-gray-300" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Commodity</label>
                        <input v-model="newSampleForm.commodity" list="research-experiment-commodity-options" class="w-full rounded-xl border-gray-300" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">PR code</label>
                        <input v-model="newSampleForm.pr_code" class="w-full rounded-xl border-gray-300" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Field label</label>
                        <input v-model="newSampleForm.field_label" class="w-full rounded-xl border-gray-300" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Line label</label>
                        <input v-model="newSampleForm.line_label" class="w-full rounded-xl border-gray-300" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Plant label</label>
                        <input v-model="newSampleForm.plant_label" class="w-full rounded-xl border-gray-300" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Generation</label>
                        <input v-model="newSampleForm.generation" class="w-full rounded-xl border-gray-300" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Replication number</label>
                        <input v-model="newSampleForm.replication_number" type="number" min="0" class="w-full rounded-xl border-gray-300" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Current status</label>
                        <input v-model="newSampleForm.current_status" class="w-full rounded-xl border-gray-300" placeholder="Field / Lab / Storage" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Current location</label>
                        <input v-model="newSampleForm.current_location" class="w-full rounded-xl border-gray-300" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Storage location</label>
                        <input v-model="newSampleForm.storage_location" class="w-full rounded-xl border-gray-300" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Germination date</label>
                        <input v-model="newSampleForm.germination_date" type="date" class="w-full rounded-xl border-gray-300" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Sowing date</label>
                        <input v-model="newSampleForm.sowing_date" type="date" class="w-full rounded-xl border-gray-300" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Harvest date</label>
                        <input v-model="newSampleForm.harvest_date" type="date" class="w-full rounded-xl border-gray-300" />
                    </div>

                    <div class="xl:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700">Legacy reference</label>
                        <input v-model="newSampleForm.legacy_reference" class="w-full rounded-xl border-gray-300" placeholder="Readable field code if you still need it" />
                    </div>

                    <label class="flex items-center gap-2 rounded-2xl bg-gray-50 px-4 py-3 text-sm font-medium text-gray-700">
                        <input v-model="newSampleForm.is_priority" type="checkbox" />
                        Priority sample
                    </label>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button" :disabled="creatingSample" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700" @click="createSample">
                        {{ creatingSample ? 'Creating...' : 'Create Sample' }}
                    </button>
                </div>
            </section>

            <section class="space-y-6">
                <div class="flex items-center justify-between gap-4">
                    <div class="sticky top-0 z-10 rounded-xl bg-white/95 px-2 py-2 backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Samples</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900">Tracked samples and monitoring records</h2>
                    </div>
                </div>

                <div v-if="!(experiment.samples || []).length" class="rounded-3xl border border-dashed border-gray-300 bg-white px-6 py-10 text-center text-sm text-gray-500">
                    No samples yet. Register the first sample above to begin monitoring.
                </div>

                <div v-for="sample in experiment.samples || []" :key="sample.id" class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">{{ sample.uid }}</p>
                            <h3 class="mt-2 text-xl font-semibold text-gray-900">{{ sample.accession_name }}</h3>
                            <p class="mt-2 text-sm text-gray-600">{{ sample.sample_type || 'Sample type pending' }} / {{ sample.current_status || 'Status pending' }}</p>
                        </div>
                        <div class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                            {{ (sample.monitoring_records || sample.monitoringRecords || []).length }} records
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <div class="xl:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Sample / accession name</label>
                            <input v-model="sampleForms[sample.id].accession_name" class="w-full rounded-xl border-gray-300" />
                            <p v-if="fieldError(sampleErrors[sample.id], 'accession_name')" class="mt-1 text-xs text-red-600">{{ fieldError(sampleErrors[sample.id], 'accession_name') }}</p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Sample type</label>
                            <input v-model="sampleForms[sample.id].sample_type" list="research-experiment-sample-options" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Commodity</label>
                            <input v-model="sampleForms[sample.id].commodity" list="research-experiment-commodity-options" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">PR code</label>
                            <input v-model="sampleForms[sample.id].pr_code" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Field label</label>
                            <input v-model="sampleForms[sample.id].field_label" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Line label</label>
                            <input v-model="sampleForms[sample.id].line_label" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Plant label</label>
                            <input v-model="sampleForms[sample.id].plant_label" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Generation</label>
                            <input v-model="sampleForms[sample.id].generation" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Replication number</label>
                            <input v-model="sampleForms[sample.id].replication_number" type="number" min="0" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Current status</label>
                            <input v-model="sampleForms[sample.id].current_status" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Current location</label>
                            <input v-model="sampleForms[sample.id].current_location" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Storage location</label>
                            <input v-model="sampleForms[sample.id].storage_location" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Germination date</label>
                            <input v-model="sampleForms[sample.id].germination_date" type="date" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Sowing date</label>
                            <input v-model="sampleForms[sample.id].sowing_date" type="date" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Harvest date</label>
                            <input v-model="sampleForms[sample.id].harvest_date" type="date" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div class="xl:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Legacy reference</label>
                            <input v-model="sampleForms[sample.id].legacy_reference" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <label class="flex items-center gap-2 rounded-2xl bg-gray-50 px-4 py-3 text-sm font-medium text-gray-700">
                            <input v-model="sampleForms[sample.id].is_priority" type="checkbox" />
                            Priority sample
                        </label>
                    </div>

                    <div class="mt-6 flex flex-wrap justify-end gap-3">
                        <button type="button" :disabled="deletingSamples[sample.id]" class="rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50" @click="deleteSample(sample.id)">
                            {{ deletingSamples[sample.id] ? 'Deleting...' : 'Delete Sample' }}
                        </button>
                        <button type="button" :disabled="savingSamples[sample.id]" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700" @click="saveSample(sample.id)">
                            {{ savingSamples[sample.id] ? 'Saving...' : 'Save Sample' }}
                        </button>
                    </div>

                    <div class="mt-8 rounded-3xl border border-gray-200 bg-gray-50 p-5">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">New Monitoring Record</p>
                            <h4 class="mt-2 text-lg font-semibold text-gray-900">Add a stage record for {{ sample.uid }}</h4>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Stage</label>
                                <select v-model="recordForms[sample.id].stage" class="w-full rounded-xl border-gray-300" @change="handleRecordStageChange(sample.id)">
                                    <option v-for="(stage, key) in catalog.stages || {}" :key="key" :value="key">
                                        {{ stage.label }}
                                    </option>
                                </select>
                                <p v-if="fieldError(recordErrors[sample.id], 'stage')" class="mt-1 text-xs text-red-600">{{ fieldError(recordErrors[sample.id], 'stage') }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Recorded on</label>
                                <input v-model="recordForms[sample.id].recorded_on" type="date" class="w-full rounded-xl border-gray-300" />
                            </div>

                            <div class="md:col-span-2">
                                <KeyValueEditor v-model="recordForms[sample.id].parameter_entries" :title="stageLabel(recordForms[sample.id].stage)" />
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-gray-700">Notes</label>
                                <textarea v-model="recordForms[sample.id].notes" rows="3" class="w-full rounded-xl border-gray-300" placeholder="Add handling notes, observations, or special conditions." />
                            </div>

                            <label class="flex items-center gap-2 rounded-2xl bg-white px-4 py-3 text-sm font-medium text-gray-700">
                                <input v-model="recordForms[sample.id].selected_for_export" type="checkbox" />
                                Mark for export and reporting
                            </label>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="button" :disabled="creatingRecords[sample.id]" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700" @click="createRecord(sample.id)">
                                {{ creatingRecords[sample.id] ? 'Saving...' : 'Save Record' }}
                            </button>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="flex items-center justify-between gap-4">
                            <div class="sticky top-0 z-10 rounded-xl bg-white/95 px-2 py-2 backdrop-blur">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Monitoring History</p>
                                <h4 class="mt-2 text-lg font-semibold text-gray-900">Existing records</h4>
                            </div>
                        </div>

                        <div class="mt-4 space-y-4">
                            <div v-if="!(sample.monitoring_records || sample.monitoringRecords || []).length" class="rounded-2xl border border-dashed border-gray-300 bg-white px-4 py-5 text-sm text-gray-500">
                                No monitoring records yet for this sample.
                            </div>

                            <div v-for="record in sample.monitoring_records || sample.monitoringRecords || []" :key="record.id" class="rounded-2xl border border-gray-200 bg-white p-4">
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">{{ stageLabel(record.stage) }}</p>
                                        <p class="mt-1 text-sm text-gray-600">{{ record.recorded_on }}</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span v-if="record.selected_for_export" class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                                            Export-ready
                                        </span>
                                        <button type="button" :disabled="deletingRecords[record.id]" class="rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50" @click="deleteRecord(record.id, sample.id)">
                                            {{ deletingRecords[record.id] ? 'Deleting...' : 'Delete' }}
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-4 grid gap-2 md:grid-cols-2">
                                    <div v-for="(value, key) in record.parameter_set || record.parameterSet || {}" :key="`${record.id}-${key}`" class="rounded-2xl bg-gray-50 px-4 py-3">
                                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-gray-500">{{ key }}</p>
                                        <p class="mt-1 text-sm text-gray-800">{{ value || 'N/A' }}</p>
                                    </div>
                                </div>

                                <div v-if="record.notes" class="mt-4 rounded-2xl bg-gray-50 px-4 py-3 text-sm text-gray-700">
                                    {{ record.notes }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <datalist id="research-experiment-commodity-options">
                <option v-for="commodity in catalog.commodities || []" :key="commodity" :value="commodity" />
            </datalist>
            <datalist id="research-experiment-sample-options">
                <option v-for="sampleType in catalog.sample_types || []" :key="sampleType" :value="sampleType" />
            </datalist>
        </div>
    </AppLayout>
</template>
