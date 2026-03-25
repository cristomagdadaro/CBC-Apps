<script>
import axios from 'axios'
import { router } from '@inertiajs/vue3'

export default {
    name: 'ResearchProjectCreate',
    props: {
        catalog: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            form: {
                title: '',
                commodity: 'Rice',
                duration_start: '',
                duration_end: '',
                overall_budget: '',
                objective: '',
                funding_agency: '',
                funding_code: '',
                project_leader: {
                    name: '',
                    position: '',
                },
            },
            errors: {},
            submitting: false,
        }
    },
    methods: {
        async submit() {
            this.submitting = true
            this.errors = {}

            try {
                const response = await axios.post(route('api.research.projects.store'), this.form)
                router.visit(route('research.projects.show', response.data.data.id))
            } catch (error) {
                this.errors = error?.response?.data?.errors || {}
            } finally {
                this.submitting = false
            }
        },
    },
}
</script>

<template>
    <AppLayout title="Create Research Project">
        <template #header>
            <ActionHeaderLayout
                title="Create Research Project"
                subtitle="Start the top-level profile before adding studies, experiments, and sample flows."
                :route-link="route('research.projects.index')"
            />
        </template>

        <div class="mx-auto max-w-5xl space-y-6 px-4 py-6">
            <form class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]" @submit.prevent="submit">
                <section class="space-y-6 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Project Profile</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900">Research project details</h2>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Project title</label>
                            <input v-model="form.title" class="w-full rounded-xl border-gray-300" placeholder="Ex. Marker-assisted drought tolerance breeding" />
                            <p v-if="errors.title" class="mt-1 text-xs text-red-600">{{ errors.title[0] }}</p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Primary commodity</label>
                            <input v-model="form.commodity" list="commodity-options" class="w-full rounded-xl border-gray-300" placeholder="Rice" />
                            <datalist id="commodity-options">
                                <option v-for="commodity in catalog.commodities || []" :key="commodity" :value="commodity" />
                            </datalist>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Overall budget</label>
                            <input v-model="form.overall_budget" type="number" min="0" step="0.01" class="w-full rounded-xl border-gray-300" placeholder="0.00" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Duration start</label>
                            <input v-model="form.duration_start" type="date" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Duration end</label>
                            <input v-model="form.duration_end" type="date" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Funding agency</label>
                            <input v-model="form.funding_agency" class="w-full rounded-xl border-gray-300" placeholder="Agency or donor" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Funding code</label>
                            <input v-model="form.funding_code" class="w-full rounded-xl border-gray-300" placeholder="Code or grant number" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Objective</label>
                            <textarea v-model="form.objective" rows="5" class="w-full rounded-xl border-gray-300" placeholder="Summarize the major objective, breeding goal, or experimental direction." />
                        </div>
                    </div>
                </section>

                <section class="space-y-6 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Leadership</p>
                        <h2 class="mt-2 text-xl font-semibold text-gray-900">Project leader</h2>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Leader name</label>
                            <input v-model="form.project_leader.name" class="w-full rounded-xl border-gray-300" placeholder="Name" />
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Leader position</label>
                            <input v-model="form.project_leader.position" class="w-full rounded-xl border-gray-300" placeholder="Position" />
                        </div>
                    </div>

                    <div class="rounded-2xl bg-gray-50 p-4 text-sm leading-6 text-gray-600">
                        After saving, you can add multiple studies under this project, assign supervisors and staff, and create repeated experiments with their own samples and monitoring records.
                    </div>

                    <div class="flex justify-end gap-3">
                        <Link :href="route('research.projects.index')" class="rounded-xl border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </Link>
                        <button :disabled="submitting" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                            {{ submitting ? 'Saving...' : 'Create Project' }}
                        </button>
                    </div>
                </section>
            </form>
        </div>
    </AppLayout>
</template>
