<script>
import TopicLayout from "@/Pages/Manuals/Components/TopicLayout.vue";

export default {
    name: "CustomFormTopic",
    components: {
        TopicLayout,
    },
    props: {
        showDeveloperSections: {
            type: Boolean,
            default: true,
        },
    },
};
</script>

<template>
    <TopicLayout
        title="Custom Forms Management"
        description="Guidelines for adding forms to specific events and the complete development lifecycle for introducing brand-new custom form types[cite: 10]."
        icon="LuClipboardList">
        <!-- For Non-Programmers -->
        <div class="space-y-4">
            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">For Non-Programmers (Event Administrators)</h3>

            <div class="bg-sky-50/50 dark:bg-sky-500/5 border border-sky-100 dark:border-sky-500/20 rounded-xl p-5 shadow-sm">
                <div class="flex items-start gap-3 mb-4 border-b border-sky-100 dark:border-sky-500/20 pb-4">
                    <LuInfo class="w-4 h-4 text-sky-500 shrink-0 mt-0.5" />
                    <p class="text-xs font-medium text-slate-600 dark:text-slate-300 leading-relaxed">If you only need a new form for a specific event, you do not need code changes[cite: 10]. Use the Requirements manager when creating or editing an event form[cite: 10]. If the dropdown does not have the form type you need, ask a developer to create a new custom form type[cite: 10].</p>
                </div>

                <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400 mb-3 flex items-center gap-1.5">
                    <LuSettings2 class="w-3.5 h-3.5" />
                    Steps to Add an Existing Form
                </h4>
                <ol class="space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside ml-1">
                    <li>Go to the event form create/edit page[cite: 10].</li>
                    <li>
                        In the
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Requirements</span>
                        section, click
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Add form</span>
                        [cite: 10].
                    </li>
                    <li>
                        Choose a
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Form type</span>
                        from the dropdown (e.g., Registration, Feedback)[cite: 10].
                    </li>
                    <li>
                        Configure optional settings:[cite: 10]
                        <ul class="list-none space-y-1.5 ml-5 mt-2">
                            <li class="flex items-center gap-2">
                                <div class="w-1 h-1 rounded-full bg-sky-400"></div>
                                Required vs. optional[cite: 10]
                            </li>
                            <li class="flex items-center gap-2">
                                <div class="w-1 h-1 rounded-full bg-sky-400"></div>
                                Open/close dates[cite: 10]
                            </li>
                            <li class="flex items-center gap-2">
                                <div class="w-1 h-1 rounded-full bg-sky-400"></div>
                                Max slots[cite: 10]
                            </li>
                            <li class="flex items-center gap-2">
                                <div class="w-1 h-1 rounded-full bg-sky-400"></div>
                                Limits (per region, city, organization, etc.)[cite: 10]
                            </li>
                        </ul>
                    </li>
                    <li>Save the event form[cite: 10].</li>
                </ol>
            </div>
        </div>

        <!-- For Programmers -->
        <div
            v-if="showDeveloperSections"
            class="space-y-4 pt-6 border-t border-slate-100 dark:border-slate-800/60">
            <div class="mb-4 ml-1">
                <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1.5">For Programmers (Implementation)</h3>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 leading-relaxed">
                    A custom form type is identified by a unique slug (e.g.,
                    <code class="px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem]">speaker_evaluation</code>
                    )[cite: 10]. Adding a new type requires changes in both backend validation and frontend UI[cite: 10].
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Step 1 & 2 -->
                <div class="space-y-4">
                    <div class="bg-amber-50/50 dark:bg-amber-500/5 border border-amber-100 dark:border-amber-500/20 rounded-xl p-5 shadow-sm">
                        <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400 mb-2.5 flex items-center gap-1.5">
                            <LuCode2 class="w-3.5 h-3.5" />
                            Step 1: Choose Slug
                        </h4>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 leading-relaxed">
                            Pick a short, lowercase, underscore-separated slug, e.g.
                            <code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-amber-600 dark:text-amber-400">speaker_evaluation</code>
                            [cite: 10]. Use this slug in all steps below[cite: 10].
                        </p>
                    </div>

                    <div class="bg-emerald-50/50 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-xl p-5 shadow-sm">
                        <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-3 flex items-center gap-1.5">
                            <LuDatabase class="w-3.5 h-3.5" />
                            Step 2: Backend Registration
                        </h4>
                        <ul class="space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300">
                            <li>
                                <span class="font-semibold text-slate-800 dark:text-slate-200 block mb-1">Add the new enum case:</span>
                                Update
                                <code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] shadow-sm text-emerald-600 dark:text-emerald-400">app/Enums/Subform.php</code>
                                [cite: 10].
                            </li>
                            <li>
                                <span class="font-semibold text-slate-800 dark:text-slate-200 block mb-1">Add validation rules:</span>
                                Update
                                <code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] shadow-sm text-emerald-600 dark:text-emerald-400">config/subformtypes.php</code>
                                [cite: 10].
                            </li>
                            <li>
                                <span class="font-semibold text-slate-800 dark:text-slate-200 block mb-1">Update participant requirements (if needed):</span>
                                Update
                                <code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] shadow-sm text-emerald-600 dark:text-emerald-400 break-all">app/Http/Requests/CreateEventSubformResponseRequest.php</code>
                                [cite: 10].
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Step 3 & 4 -->
                <div class="space-y-4">
                    <div class="bg-purple-50/50 dark:bg-purple-500/5 border border-purple-100 dark:border-purple-500/20 rounded-xl p-5 shadow-sm">
                        <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-purple-600 dark:text-purple-400 mb-2.5 flex items-center gap-1.5">
                            <LuLayoutTemplate class="w-3.5 h-3.5" />
                            Step 3: Default Field Structure
                        </h4>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 leading-relaxed mb-2">Define the default field values used by the UI when the form loads[cite: 10]:</p>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 leading-relaxed mb-2">
                            Update
                            <code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] shadow-sm text-purple-600 dark:text-purple-400 break-all">resources/js/Modules/dto/DtoSubformResponse.ts</code>
                            [cite: 10].
                        </p>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 leading-relaxed">
                            Add a new
                            <code class="px-1 py-0.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-[0.6rem] font-mono">case</code>
                            in
                            <code class="px-1 py-0.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-[0.6rem] font-mono">getSubformFields()</code>
                            that returns your new fields[cite: 10].
                        </p>
                    </div>

                    <div class="bg-pink-50/50 dark:bg-pink-500/5 border border-pink-100 dark:border-pink-500/20 rounded-xl p-5 shadow-sm">
                        <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-pink-600 dark:text-pink-400 mb-2.5 flex items-center gap-1.5">
                            <LuFileCode class="w-3.5 h-3.5" />
                            Step 4: Create Form Component
                        </h4>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 leading-relaxed mb-2">Create a new Vue component that renders the inputs and submission logic[cite: 10].</p>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 leading-relaxed mb-3">
                            Location:
                            <code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] shadow-sm text-pink-600 dark:text-pink-400 break-all">resources/js/Pages/Forms/components</code>
                            [cite: 10].
                        </p>
                        <p class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 mb-2">Examples to follow:</p>
                        <ul class="space-y-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                            <li class="flex items-center gap-2">
                                <div class="w-1 h-1 rounded-full bg-pink-400"></div>
                                <code class="font-mono text-[0.65rem]">RegistrationCard.vue</code>
                                [cite: 10]
                            </li>
                            <li class="flex items-center gap-2">
                                <div class="w-1 h-1 rounded-full bg-pink-400"></div>
                                <code class="font-mono text-[0.65rem]">FeedbackCard.vue</code>
                                [cite: 10]
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Step 5, 6, 7 -->
                <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-indigo-50/50 dark:bg-indigo-500/5 border border-indigo-100 dark:border-indigo-500/20 rounded-xl p-5 shadow-sm">
                        <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 mb-2.5 flex items-center gap-1.5">
                            <LuGitMerge class="w-3.5 h-3.5" />
                            Step 5: Guest Workflow
                        </h4>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 leading-relaxed mb-2">To show the new form in the public/guest flow:[cite: 10]</p>
                        <ul class="space-y-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1 mb-3">
                            <li class="flex items-start gap-2">
                                <div class="w-1 h-1 rounded-full bg-indigo-400 shrink-0 mt-1.5"></div>
                                <span class="leading-relaxed">
                                    Add import/registration in
                                    <code class="font-mono text-[0.6rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">GuestCard.vue</code>
                                    [cite: 10].
                                </span>
                            </li>
                            <li class="flex items-start gap-2">
                                <div class="w-1 h-1 rounded-full bg-indigo-400 shrink-0 mt-1.5"></div>
                                <span class="leading-relaxed">
                                    Add label in
                                    <code class="font-mono text-[0.6rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">labelMap</code>
                                    [cite: 10].
                                </span>
                            </li>
                            <li class="flex items-start gap-2">
                                <div class="w-1 h-1 rounded-full bg-indigo-400 shrink-0 mt-1.5"></div>
                                <span class="leading-relaxed">
                                    Add a
                                    <code class="font-mono text-[0.6rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">v-if</code>
                                    block for content[cite: 10].
                                </span>
                            </li>
                        </ul>
                        <p class="text-[0.65rem] font-medium text-indigo-500 dark:text-indigo-400 italic">If not available in guest UI, skip this[cite: 10].</p>
                    </div>

                    <div class="bg-orange-50/50 dark:bg-orange-500/5 border border-orange-100 dark:border-orange-500/20 rounded-xl p-5 shadow-sm">
                        <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-orange-600 dark:text-orange-400 mb-2.5 flex items-center gap-1.5">
                            <LuListPlus class="w-3.5 h-3.5" />
                            Step 6: Req. Dropdown
                        </h4>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 leading-relaxed mb-2">To make the form type selectable when editing events:[cite: 10]</p>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 leading-relaxed mb-2">
                            Update
                            <code class="px-1 py-0.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] break-all">RequirementsManager.vue</code>
                            [cite: 10].
                        </p>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 leading-relaxed">
                            Add a
                            <code class="font-mono text-[0.6rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">{ value, label }</code>
                            entry to
                            <code class="font-mono text-[0.6rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">formTypeOptions</code>
                            [cite: 10].
                        </p>
                    </div>

                    <div class="bg-rose-50/50 dark:bg-rose-500/5 border border-rose-100 dark:border-rose-500/20 rounded-xl p-5 shadow-sm">
                        <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400 mb-2.5 flex items-center gap-1.5">
                            <LuTestTube class="w-3.5 h-3.5" />
                            Step 7: Tests/Seeders
                        </h4>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-300 leading-relaxed mb-3">If tests rely on a full list of form types, update them:[cite: 10]</p>
                        <ul class="space-y-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                            <li class="flex items-start gap-2">
                                <div class="w-1 h-1 rounded-full bg-rose-400 shrink-0 mt-1.5"></div>
                                <span class="leading-relaxed">
                                    <code class="font-mono text-[0.6rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700 break-all">EventSubformResponseFactory.php</code>
                                    [cite: 10]
                                </span>
                            </li>
                            <li class="flex items-start gap-2">
                                <div class="w-1 h-1 rounded-full bg-rose-400 shrink-0 mt-1.5"></div>
                                <span class="leading-relaxed">Any workflow or feature tests[cite: 10].</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Checklist & Pitfalls -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-4">
                <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                    <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-3.5 flex items-center gap-1.5">
                        <LuCheckSquare class="w-3.5 h-3.5" />
                        Quick Checklist
                    </h3>
                    <ul class="space-y-2.5 text-xs font-medium text-slate-700 dark:text-slate-300">
                        <li class="flex items-center gap-2">
                            <LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0" />
                            New enum value added[cite: 10]
                        </li>
                        <li class="flex items-center gap-2">
                            <LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0" />
                            New config validation rules added[cite: 10]
                        </li>
                        <li class="flex items-center gap-2">
                            <LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0" />
                            Participant requirement updated (if needed)[cite: 10]
                        </li>
                        <li class="flex items-center gap-2">
                            <LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0" />
                            New UI defaults added in
                            <code class="font-mono text-[0.65rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">getSubformFields()</code>
                            [cite: 10]
                        </li>
                        <li class="flex items-center gap-2">
                            <LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0" />
                            New form component created[cite: 10]
                        </li>
                        <li class="flex items-center gap-2">
                            <LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0" />
                            Guest UI updated (if applicable)[cite: 10]
                        </li>
                        <li class="flex items-center gap-2">
                            <LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0" />
                            Requirements dropdown updated[cite: 10]
                        </li>
                        <li class="flex items-center gap-2">
                            <LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0" />
                            Tests/factories updated[cite: 10]
                        </li>
                    </ul>
                </div>

                <div class="bg-rose-50/50 dark:bg-rose-500/5 border border-rose-100 dark:border-rose-500/20 rounded-xl p-5 shadow-sm">
                    <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400 mb-3.5 flex items-center gap-1.5">
                        <LuAlertTriangle class="w-3.5 h-3.5" />
                        Common Pitfalls
                    </h3>
                    <ul class="space-y-4 text-xs font-medium text-slate-700 dark:text-slate-300">
                        <li class="flex items-start gap-2.5">
                            <LuXCircle class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" />
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-900 dark:text-slate-200">Form type not in dropdown:</span>
                                Add it to
                                <code class="font-mono text-[0.65rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700 shadow-sm text-rose-600 dark:text-rose-400">formTypeOptions</code>
                                [cite: 10].
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuXCircle class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" />
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-900 dark:text-slate-200">Validation fails on submit:</span>
                                Ensure your field names match in both frontend defaults and backend config[cite: 10].
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuXCircle class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" />
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-900 dark:text-slate-200">Guest UI shows "not yet supported":</span>
                                Add the new tab mapping and component in the guest workflow[cite: 10].
                            </p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </TopicLayout>
</template>
