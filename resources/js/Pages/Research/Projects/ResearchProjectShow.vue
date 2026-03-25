<script>
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import PersonListEditor from '@/Pages/Research/components/PersonListEditor.vue'

export default {
    name: 'ResearchProjectShow',
    components: {
        PersonListEditor,
    },
    props: {
        project: {
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
            projectForm: this.buildProjectForm(this.project),
            newStudyForm: this.defaultStudyForm(this.project.id),
            studyForms: this.buildStudyForms(this.project.studies || []),
            newExperimentForms: this.buildExperimentForms(this.project.studies || []),
            projectErrors: {},
            studyCreateErrors: {},
            studyErrors: {},
            experimentErrors: {},
            savingProject: false,
            deletingProject: false,
            creatingStudy: false,
            savingStudies: {},
            deletingStudies: {},
            creatingExperiments: {},
            deletingExperiments: {},
        }
    },
    computed: {
        permissions() {
            return this.$page.props.auth?.permissions || []
        },
        canDeleteProject() {
            return this.permissions.includes('*') || this.permissions.includes('research.projects.delete')
        },
    },
    methods: {
        buildProjectForm(project) {
            return {
                title: project?.title || '',
                commodity: project?.commodity || 'Rice',
                duration_start: project?.duration_start || '',
                duration_end: project?.duration_end || '',
                overall_budget: project?.overall_budget || '',
                objective: project?.objective || '',
                funding_agency: project?.funding_agency || '',
                funding_code: project?.funding_code || '',
                project_leader: this.normalizePerson(project?.project_leader),
            }
        },
        defaultStudyForm(projectId) {
            return {
                project_id: projectId,
                title: '',
                objective: '',
                budget: '',
                study_leader: this.normalizePerson(),
                supervisor: this.normalizePerson(),
                staff_members: [],
            }
        },
        buildStudyForms(studies) {
            return studies.reduce((forms, study) => {
                forms[study.id] = {
                    title: study.title || '',
                    objective: study.objective || '',
                    budget: study.budget || '',
                    study_leader: this.normalizePerson(study.study_leader),
                    supervisor: this.normalizePerson(study.supervisor),
                    staff_members: this.normalizePeople(study.staff_members),
                }

                return forms
            }, {})
        },
        defaultExperimentForm(studyId) {
            return {
                study_id: studyId,
                title: '',
                geographic_location: '',
                season: 'wet',
                commodity: this.project?.commodity || 'Rice',
                sample_type: 'Seeds',
                sample_descriptor: '',
                pr_code: '',
                cross_combination: '',
                parental_background: '',
                filial_generation: '',
                generation: '',
                plot_number: '',
                field_number: '',
                replication_number: '',
                planned_plant_count: '',
                background_notes: '',
            }
        },
        buildExperimentForms(studies) {
            return studies.reduce((forms, study) => {
                forms[study.id] = this.defaultExperimentForm(study.id)
                return forms
            }, {})
        },
        normalizePerson(person = null) {
            return {
                name: person?.name || '',
                position: person?.position || '',
            }
        },
        normalizePeople(people = []) {
            return Array.isArray(people)
                ? people.map((person) => this.normalizePerson(person))
                : []
        },
        sanitizePeople(people = []) {
            return this.normalizePeople(people).filter((person) => person.name || person.position)
        },
        sanitizeProjectPayload() {
            return {
                ...this.projectForm,
                project_leader: this.normalizePerson(this.projectForm.project_leader),
            }
        },
        sanitizeStudyPayload(form) {
            return {
                ...form,
                study_leader: this.normalizePerson(form.study_leader),
                supervisor: this.normalizePerson(form.supervisor),
                staff_members: this.sanitizePeople(form.staff_members),
            }
        },
        sanitizeExperimentPayload(form) {
            return {
                ...form,
                replication_number: form.replication_number || null,
                planned_plant_count: form.planned_plant_count || null,
            }
        },
        fieldError(errors, field) {
            return errors?.[field]?.[0] || ''
        },
        async saveProject() {
            this.savingProject = true
            this.projectErrors = {}

            try {
                await axios.put(route('api.research.projects.update', this.project.id), this.sanitizeProjectPayload())
                router.visit(route('research.projects.show', this.project.id))
            } catch (error) {
                this.projectErrors = error?.response?.data?.errors || {}
            } finally {
                this.savingProject = false
            }
        },
        async deleteProject() {
            if (!confirm('Delete this project and all of its studies, experiments, samples, and records?')) {
                return
            }

            this.deletingProject = true

            try {
                await axios.delete(route('api.research.projects.destroy', this.project.id))
                router.visit(route('research.projects.index'))
            } finally {
                this.deletingProject = false
            }
        },
        async createStudy() {
            this.creatingStudy = true
            this.studyCreateErrors = {}

            try {
                await axios.post(route('api.research.studies.store'), this.sanitizeStudyPayload(this.newStudyForm))
                router.visit(route('research.projects.show', this.project.id))
            } catch (error) {
                this.studyCreateErrors = error?.response?.data?.errors || {}
            } finally {
                this.creatingStudy = false
            }
        },
        async saveStudy(studyId) {
            this.savingStudies = { ...this.savingStudies, [studyId]: true }
            this.studyErrors = { ...this.studyErrors, [studyId]: {} }

            try {
                await axios.put(route('api.research.studies.update', studyId), this.sanitizeStudyPayload(this.studyForms[studyId]))
                router.visit(route('research.projects.show', this.project.id))
            } catch (error) {
                this.studyErrors = {
                    ...this.studyErrors,
                    [studyId]: error?.response?.data?.errors || {},
                }
            } finally {
                this.savingStudies = { ...this.savingStudies, [studyId]: false }
            }
        },
        async deleteStudy(studyId) {
            if (!confirm('Delete this study and all linked experiments?')) {
                return
            }

            this.deletingStudies = { ...this.deletingStudies, [studyId]: true }

            try {
                await axios.delete(route('api.research.studies.destroy', studyId))
                router.visit(route('research.projects.show', this.project.id))
            } finally {
                this.deletingStudies = { ...this.deletingStudies, [studyId]: false }
            }
        },
        async createExperiment(studyId) {
            this.creatingExperiments = { ...this.creatingExperiments, [studyId]: true }
            this.experimentErrors = { ...this.experimentErrors, [studyId]: {} }

            try {
                const response = await axios.post(
                    route('api.research.experiments.store'),
                    this.sanitizeExperimentPayload(this.newExperimentForms[studyId]),
                )

                router.visit(route('research.experiments.show', response.data.data.id))
            } catch (error) {
                this.experimentErrors = {
                    ...this.experimentErrors,
                    [studyId]: error?.response?.data?.errors || {},
                }
            } finally {
                this.creatingExperiments = { ...this.creatingExperiments, [studyId]: false }
            }
        },
        async deleteExperiment(experimentId) {
            if (!confirm('Delete this experiment and all linked samples?')) {
                return
            }

            this.deletingExperiments = { ...this.deletingExperiments, [experimentId]: true }

            try {
                await axios.delete(route('api.research.experiments.destroy', experimentId))
                router.visit(route('research.projects.show', this.project.id))
            } finally {
                this.deletingExperiments = { ...this.deletingExperiments, [experimentId]: false }
            }
        },
    },
}
</script>

<template>
    <AppLayout :title="project.title">
        <template #header>
            <ActionHeaderLayout
                :title="project.title"
                subtitle="Manage the project profile, create studies, and launch experiment monitoring."
                :route-link="route('research.projects.show', project.id)"
            >
                <Link :href="route('research.projects.index')" class="rounded-lg border border-white/25 px-4 py-2 text-sm font-medium text-white hover:bg-white/10">
                    All Projects
                </Link>
                <Link :href="route('research.dashboard')" class="rounded-lg bg-white/15 px-4 py-2 text-sm font-medium text-white hover:bg-white/25">
                    Dashboard
                </Link>
            </ActionHeaderLayout>
        </template>

        <div class="mx-auto max-w-7xl space-y-6 px-4 py-6">
            <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">{{ project.code }}</p>
                            <h2 class="mt-2 text-2xl font-semibold text-gray-900">Project profile</h2>
                        </div>
                        <div class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                            {{ (project.studies || []).length }} studies
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Project title</label>
                            <input v-model="projectForm.title" class="w-full rounded-xl border-gray-300" />
                            <p v-if="fieldError(projectErrors, 'title')" class="mt-1 text-xs text-red-600">{{ fieldError(projectErrors, 'title') }}</p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Primary commodity</label>
                            <input v-model="projectForm.commodity" list="research-project-commodity-options" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Overall budget</label>
                            <input v-model="projectForm.overall_budget" type="number" min="0" step="0.01" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Duration start</label>
                            <input v-model="projectForm.duration_start" type="date" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Duration end</label>
                            <input v-model="projectForm.duration_end" type="date" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Funding agency</label>
                            <input v-model="projectForm.funding_agency" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Funding code</label>
                            <input v-model="projectForm.funding_code" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Project leader name</label>
                            <input v-model="projectForm.project_leader.name" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Project leader position</label>
                            <input v-model="projectForm.project_leader.position" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Objective</label>
                            <textarea v-model="projectForm.objective" rows="5" class="w-full rounded-xl border-gray-300" />
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap justify-end gap-3">
                        <button v-if="canDeleteProject" type="button" :disabled="deletingProject" class="rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50" @click="deleteProject">
                            {{ deletingProject ? 'Deleting...' : 'Delete Project' }}
                        </button>
                        <button type="button" :disabled="savingProject" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700" @click="saveProject">
                            {{ savingProject ? 'Saving...' : 'Save Project' }}
                        </button>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Workflow</p>
                        <h2 class="mt-2 text-xl font-semibold text-gray-900">Project to monitoring chain</h2>
                        <div class="mt-4 space-y-3 text-sm text-gray-600">
                            <div class="rounded-2xl bg-gray-50 p-4">
                                <p class="font-medium text-gray-900">1. Project</p>
                                <p class="mt-1">Capture funding, goals, duration, and project leadership.</p>
                            </div>
                            <div class="rounded-2xl bg-gray-50 p-4">
                                <p class="font-medium text-gray-900">2. Study</p>
                                <p class="mt-1">Define study objectives, staff, and supervising structure.</p>
                            </div>
                            <div class="rounded-2xl bg-gray-50 p-4">
                                <p class="font-medium text-gray-900">3. Experiment</p>
                                <p class="mt-1">Describe season, line background, generation, field setup, and sample context.</p>
                            </div>
                            <div class="rounded-2xl bg-gray-50 p-4">
                                <p class="font-medium text-gray-900">4. Sample and records</p>
                                <p class="mt-1">Track individual samples with short IDs, field references, and stage-based monitoring logs.</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Quick Tips</p>
                        <ul class="mt-4 space-y-3 text-sm text-gray-600">
                            <li class="flex gap-2">
                                <LuCheckCircle2 class="mt-0.5 h-4 w-4 flex-none text-emerald-600" />
                                <span>Keep the project commodity broad if the study portfolio spans more than one crop or sample family.</span>
                            </li>
                            <li class="flex gap-2">
                                <LuCheckCircle2 class="mt-0.5 h-4 w-4 flex-none text-emerald-600" />
                                <span>Use studies for separate breeding objectives, trials, or collaborating teams within the same project.</span>
                            </li>
                            <li class="flex gap-2">
                                <LuCheckCircle2 class="mt-0.5 h-4 w-4 flex-none text-emerald-600" />
                                <span>Launch experiments from the study card below, then manage samples and monitoring in the experiment detail page.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">New Study</p>
                        <h2 class="mt-2 text-xl font-semibold text-gray-900">Add a study under this project</h2>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700">Study title</label>
                        <input v-model="newStudyForm.title" class="w-full rounded-xl border-gray-300" placeholder="Study title" />
                        <p v-if="fieldError(studyCreateErrors, 'title')" class="mt-1 text-xs text-red-600">{{ fieldError(studyCreateErrors, 'title') }}</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Study budget</label>
                        <input v-model="newStudyForm.budget" type="number" min="0" step="0.01" class="w-full rounded-xl border-gray-300" placeholder="0.00" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Study leader name</label>
                        <input v-model="newStudyForm.study_leader.name" class="w-full rounded-xl border-gray-300" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Study leader position</label>
                        <input v-model="newStudyForm.study_leader.position" class="w-full rounded-xl border-gray-300" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Supervisor name</label>
                        <input v-model="newStudyForm.supervisor.name" class="w-full rounded-xl border-gray-300" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Supervisor position</label>
                        <input v-model="newStudyForm.supervisor.position" class="w-full rounded-xl border-gray-300" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700">Study objective</label>
                        <textarea v-model="newStudyForm.objective" rows="4" class="w-full rounded-xl border-gray-300" />
                    </div>

                    <div class="md:col-span-2">
                        <PersonListEditor v-model="newStudyForm.staff_members" title="Study Staff" empty-label="No staff added yet." />
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button" :disabled="creatingStudy" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700" @click="createStudy">
                        {{ creatingStudy ? 'Creating...' : 'Create Study' }}
                    </button>
                </div>
            </section>

            <section class="space-y-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Studies</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900">Project studies and experiments</h2>
                    </div>
                </div>

                <div v-if="!(project.studies || []).length" class="rounded-3xl border border-dashed border-gray-300 bg-white px-6 py-10 text-center text-sm text-gray-500">
                    No studies yet. Create the first study above to organize experiment monitoring.
                </div>

                <div v-for="study in project.studies || []" :key="study.id" class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">{{ study.code }}</p>
                            <h3 class="mt-2 text-xl font-semibold text-gray-900">{{ study.title }}</h3>
                        </div>
                        <div class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                            {{ (study.experiments || []).length }} experiments
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Study title</label>
                            <input v-model="studyForms[study.id].title" class="w-full rounded-xl border-gray-300" />
                            <p v-if="fieldError(studyErrors[study.id], 'title')" class="mt-1 text-xs text-red-600">{{ fieldError(studyErrors[study.id], 'title') }}</p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Study budget</label>
                            <input v-model="studyForms[study.id].budget" type="number" min="0" step="0.01" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Study leader name</label>
                            <input v-model="studyForms[study.id].study_leader.name" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Study leader position</label>
                            <input v-model="studyForms[study.id].study_leader.position" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Supervisor name</label>
                            <input v-model="studyForms[study.id].supervisor.name" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Supervisor position</label>
                            <input v-model="studyForms[study.id].supervisor.position" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Study objective</label>
                            <textarea v-model="studyForms[study.id].objective" rows="4" class="w-full rounded-xl border-gray-300" />
                        </div>

                        <div class="md:col-span-2">
                            <PersonListEditor v-model="studyForms[study.id].staff_members" title="Study Staff" empty-label="No staff added yet." />
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap justify-end gap-3">
                        <button type="button" :disabled="deletingStudies[study.id]" class="rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50" @click="deleteStudy(study.id)">
                            {{ deletingStudies[study.id] ? 'Deleting...' : 'Delete Study' }}
                        </button>
                        <button type="button" :disabled="savingStudies[study.id]" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700" @click="saveStudy(study.id)">
                            {{ savingStudies[study.id] ? 'Saving...' : 'Save Study' }}
                        </button>
                    </div>

                    <div class="mt-8 rounded-3xl border border-gray-200 bg-gray-50 p-5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">New Experiment</p>
                                <h4 class="mt-2 text-lg font-semibold text-gray-900">Launch an experiment under this study</h4>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-gray-700">Experiment title</label>
                                <input v-model="newExperimentForms[study.id].title" class="w-full rounded-xl border-gray-300" placeholder="Experiment title" />
                                <p v-if="fieldError(experimentErrors[study.id], 'title')" class="mt-1 text-xs text-red-600">{{ fieldError(experimentErrors[study.id], 'title') }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Geographic location</label>
                                <input v-model="newExperimentForms[study.id].geographic_location" class="w-full rounded-xl border-gray-300" placeholder="Field site or station" />
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Season</label>
                                <select v-model="newExperimentForms[study.id].season" class="w-full rounded-xl border-gray-300">
                                    <option v-for="season in catalog.seasons || []" :key="season.value" :value="season.value">
                                        {{ season.label }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Commodity</label>
                                <input v-model="newExperimentForms[study.id].commodity" list="research-project-show-commodity-options" class="w-full rounded-xl border-gray-300" />
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Sample type</label>
                                <input v-model="newExperimentForms[study.id].sample_type" list="research-project-show-sample-options" class="w-full rounded-xl border-gray-300" />
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Sample descriptor</label>
                                <input v-model="newExperimentForms[study.id].sample_descriptor" class="w-full rounded-xl border-gray-300" placeholder="Ex. NSIC Rc222 / WEG1" />
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">PR code</label>
                                <input v-model="newExperimentForms[study.id].pr_code" class="w-full rounded-xl border-gray-300" placeholder="Optional" />
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Generation</label>
                                <input v-model="newExperimentForms[study.id].generation" class="w-full rounded-xl border-gray-300" placeholder="Ex. BC1F1" />
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Filial generation</label>
                                <input v-model="newExperimentForms[study.id].filial_generation" class="w-full rounded-xl border-gray-300" placeholder="Ex. F2" />
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Plot number</label>
                                <input v-model="newExperimentForms[study.id].plot_number" class="w-full rounded-xl border-gray-300" />
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Field number</label>
                                <input v-model="newExperimentForms[study.id].field_number" class="w-full rounded-xl border-gray-300" />
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Replication number</label>
                                <input v-model="newExperimentForms[study.id].replication_number" type="number" min="1" class="w-full rounded-xl border-gray-300" />
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Planned plant count</label>
                                <input v-model="newExperimentForms[study.id].planned_plant_count" type="number" min="0" class="w-full rounded-xl border-gray-300" />
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-gray-700">Cross combination</label>
                                <input v-model="newExperimentForms[study.id].cross_combination" class="w-full rounded-xl border-gray-300" placeholder="Ex. WEG1 x NSIC Rc160" />
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-gray-700">Parental background</label>
                                <textarea v-model="newExperimentForms[study.id].parental_background" rows="3" class="w-full rounded-xl border-gray-300" placeholder="Background, lineage, or source notes" />
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-gray-700">Additional notes</label>
                                <textarea v-model="newExperimentForms[study.id].background_notes" rows="3" class="w-full rounded-xl border-gray-300" placeholder="Add new variety, trait, or trial-specific notes" />
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="button" :disabled="creatingExperiments[study.id]" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700" @click="createExperiment(study.id)">
                                {{ creatingExperiments[study.id] ? 'Creating...' : 'Create Experiment' }}
                            </button>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Experiments</p>
                                <h4 class="mt-2 text-lg font-semibold text-gray-900">Existing experiments in this study</h4>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 xl:grid-cols-2">
                            <div v-if="!(study.experiments || []).length" class="rounded-2xl border border-dashed border-gray-300 bg-white px-4 py-5 text-sm text-gray-500 xl:col-span-2">
                                No experiments yet for this study.
                            </div>

                            <div v-for="experiment in study.experiments || []" :key="experiment.id" class="rounded-2xl border border-gray-200 bg-white p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">{{ experiment.code }}</p>
                                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ experiment.title }}</p>
                                        <p class="mt-2 text-sm text-gray-600">{{ experiment.geographic_location || 'Location pending' }}</p>
                                    </div>
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                        {{ experiment.samples_count }} samples
                                    </span>
                                </div>

                                <div class="mt-4 grid gap-2 text-sm text-gray-600">
                                    <p><span class="font-medium text-gray-900">Season:</span> {{ experiment.season || 'N/A' }}</p>
                                    <p><span class="font-medium text-gray-900">Commodity:</span> {{ experiment.commodity || 'Generic' }}</p>
                                    <p><span class="font-medium text-gray-900">Generation:</span> {{ experiment.generation || experiment.filial_generation || 'N/A' }}</p>
                                </div>

                                <div class="mt-5 flex flex-wrap justify-end gap-3">
                                    <button type="button" :disabled="deletingExperiments[experiment.id]" class="rounded-xl border border-red-200 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50" @click="deleteExperiment(experiment.id)">
                                        {{ deletingExperiments[experiment.id] ? 'Deleting...' : 'Delete' }}
                                    </button>
                                    <Link :href="route('research.experiments.show', experiment.id)" class="rounded-xl bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-black">
                                        Open Experiment
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <datalist id="research-project-commodity-options">
                <option v-for="commodity in catalog.commodities || []" :key="commodity" :value="commodity" />
            </datalist>
            <datalist id="research-project-show-commodity-options">
                <option v-for="commodity in catalog.commodities || []" :key="commodity" :value="commodity" />
            </datalist>
            <datalist id="research-project-show-sample-options">
                <option v-for="sampleType in catalog.sample_types || []" :key="sampleType" :value="sampleType" />
            </datalist>
        </div>
    </AppLayout>
</template>
