<script>
import { router } from '@inertiajs/vue3'
import ApiMixin from '@/Modules/mixins/ApiMixin'
import DtoResponse from '@/Modules/dto/DtoResponse'
import ResearchExperiment from '@/Modules/domain/ResearchExperiment'

export default {
    name: 'ResearchExperimentForm',
    mixins: [ApiMixin],
    props: {
        data: {
            type: Object,
            default: null,
        },
        study: {
            type: Object,
            default: null,
        },
        catalog: {
            type: Object,
            default: () => ({}),
        },
        showCancelButton: {
            type: Boolean,
            default: false,
        },
        showDeleteButton: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['cancel'],
    computed: {
        isEdit() {
            return !!this.data
        },
        parentStudyId() {
            return this.study?.id ?? this.data?.study_id ?? this.data?.study?.id ?? null
        },
    },
    beforeMount() {
        this.model = new ResearchExperiment(this.data ?? {})
        this.initializeForm()
    },
    methods: {
        initializeForm() {
            const action = this.isEdit ? 'update' : 'create'
            const payload = this.isEdit
                ? this.model.updateFields(this.data)
                : {
                    ...this.model.createFields(),
                    study_id: this.parentStudyId,
                    commodity: this.study?.project?.commodity || 'Rice',
                }

            this.form = this.createFormWithRemember(payload, action)
        },
        async submitProxy() {
            const response = this.isEdit
                ? await this.submitUpdate()
                : await this.submitCreate()

            if (response instanceof DtoResponse) {
                const experimentId = response?.data?.data?.id ?? this.data?.id
                router.visit(route('research.experiments.show', experimentId))
            }
        },
        async deleteProxy() {
            if (!this.isEdit) {
                return
            }

            if (!window.confirm('Delete this experiment and all linked samples?')) {
                return
            }

            this.toDelete = { id: this.data?.id }
            const response = await this.submitDelete()

            if (response instanceof DtoResponse) {
                router.visit(route('research.studies.show', this.parentStudyId))
            }
        },
        fieldError(field) {
            return this.form?.errors?.[field] || ''
        },
    },
}
</script>

<template>
    <form v-if="form" class="space-y-6 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm" @submit.prevent="submitProxy">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-gray-100 pb-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">{{ isEdit ? 'Update Experiment' : 'New Experiment' }}</p>
                <h2 class="mt-2 text-2xl font-semibold text-gray-900">{{ isEdit ? 'Experiment update form' : 'Create study experiment' }}</h2>
                <p class="mt-2 text-sm leading-6 text-gray-600">Create the experiment profile separately, then move to sample registration and monitoring records after saving.</p>
            </div>
            <div class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                {{ isEdit ? 'Editing active experiment' : 'Create mode' }}
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-700">Experiment title</label>
                <input v-model="form.title" class="w-full rounded-xl border-gray-300" placeholder="Experiment title" />
                <p v-if="fieldError('title')" class="mt-1 text-xs text-red-600">{{ fieldError('title') }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Geographic location</label>
                <input v-model="form.geographic_location" class="w-full rounded-xl border-gray-300" placeholder="Field site or station" />
                <p v-if="fieldError('geographic_location')" class="mt-1 text-xs text-red-600">{{ fieldError('geographic_location') }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Season</label>
                <select v-model="form.season" class="w-full rounded-xl border-gray-300">
                    <option v-for="season in catalog.seasons || []" :key="season.value" :value="season.value">
                        {{ season.label }}
                    </option>
                </select>
                <p v-if="fieldError('season')" class="mt-1 text-xs text-red-600">{{ fieldError('season') }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Commodity</label>
                <input v-model="form.commodity" list="research-experiment-form-commodity-options" class="w-full rounded-xl border-gray-300" />
                <p v-if="fieldError('commodity')" class="mt-1 text-xs text-red-600">{{ fieldError('commodity') }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Sample type</label>
                <input v-model="form.sample_type" list="research-experiment-form-sample-options" class="w-full rounded-xl border-gray-300" />
                <p v-if="fieldError('sample_type')" class="mt-1 text-xs text-red-600">{{ fieldError('sample_type') }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Sample descriptor</label>
                <input v-model="form.sample_descriptor" class="w-full rounded-xl border-gray-300" placeholder="Ex. NSIC Rc222 / WEG1" />
                <p v-if="fieldError('sample_descriptor')" class="mt-1 text-xs text-red-600">{{ fieldError('sample_descriptor') }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">PR code</label>
                <input v-model="form.pr_code" class="w-full rounded-xl border-gray-300" placeholder="Optional" />
                <p v-if="fieldError('pr_code')" class="mt-1 text-xs text-red-600">{{ fieldError('pr_code') }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Generation</label>
                <input v-model="form.generation" class="w-full rounded-xl border-gray-300" placeholder="Ex. BC1F1" />
                <p v-if="fieldError('generation')" class="mt-1 text-xs text-red-600">{{ fieldError('generation') }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Filial generation</label>
                <input v-model="form.filial_generation" class="w-full rounded-xl border-gray-300" placeholder="Ex. F2" />
                <p v-if="fieldError('filial_generation')" class="mt-1 text-xs text-red-600">{{ fieldError('filial_generation') }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Plot number</label>
                <input v-model="form.plot_number" class="w-full rounded-xl border-gray-300" />
                <p v-if="fieldError('plot_number')" class="mt-1 text-xs text-red-600">{{ fieldError('plot_number') }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Field number</label>
                <input v-model="form.field_number" class="w-full rounded-xl border-gray-300" />
                <p v-if="fieldError('field_number')" class="mt-1 text-xs text-red-600">{{ fieldError('field_number') }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Replication number</label>
                <input v-model="form.replication_number" type="number" min="1" class="w-full rounded-xl border-gray-300" />
                <p v-if="fieldError('replication_number')" class="mt-1 text-xs text-red-600">{{ fieldError('replication_number') }}</p>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Planned plant count</label>
                <input v-model="form.planned_plant_count" type="number" min="0" class="w-full rounded-xl border-gray-300" />
                <p v-if="fieldError('planned_plant_count')" class="mt-1 text-xs text-red-600">{{ fieldError('planned_plant_count') }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-700">Cross combination</label>
                <input v-model="form.cross_combination" class="w-full rounded-xl border-gray-300" placeholder="Ex. WEG1 x NSIC Rc160" />
                <p v-if="fieldError('cross_combination')" class="mt-1 text-xs text-red-600">{{ fieldError('cross_combination') }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-700">Parental background</label>
                <textarea v-model="form.parental_background" rows="3" class="w-full rounded-xl border-gray-300" placeholder="Background, lineage, or source notes" />
                <p v-if="fieldError('parental_background')" class="mt-1 text-xs text-red-600">{{ fieldError('parental_background') }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-700">Additional notes</label>
                <textarea v-model="form.background_notes" rows="3" class="w-full rounded-xl border-gray-300" placeholder="Add new variety, trait, or trial-specific notes" />
                <p v-if="fieldError('background_notes')" class="mt-1 text-xs text-red-600">{{ fieldError('background_notes') }}</p>
            </div>
        </div>

        <div class="flex flex-wrap justify-end gap-3">
            <button v-if="showCancelButton" type="button" class="rounded-xl border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="$emit('cancel')">
                Cancel
            </button>
            <button v-if="isEdit && showDeleteButton" type="button" :disabled="processing" class="rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50" @click="deleteProxy">
                Delete Experiment
            </button>
            <button :disabled="processing" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                {{ processing ? 'Saving...' : (isEdit ? 'Save Experiment' : 'Create Experiment') }}
            </button>
        </div>

        <datalist id="research-experiment-form-commodity-options">
            <option v-for="commodity in catalog.commodities || []" :key="commodity" :value="commodity" />
        </datalist>
        <datalist id="research-experiment-form-sample-options">
            <option v-for="sampleType in catalog.sample_types || []" :key="sampleType" :value="sampleType" />
        </datalist>
    </form>
</template>