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
            <h3 class="ml-1 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">For Non-Programmers (Event Administrators)</h3>

            <div class="rounded-xl border border-sky-100 bg-sky-50/50 p-5 shadow-sm dark:border-sky-500/20 dark:bg-sky-500/5">
                <div class="mb-4 flex items-start gap-3 border-b border-sky-100 pb-4 dark:border-sky-500/20">
                    <LuInfo class="mt-0.5 h-4 w-4 shrink-0 text-sky-500" />
                    <p class="text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-300">If you only need a new form for a specific event, you do not need code changes[cite: 10]. Use the Requirements manager when creating or editing an event form[cite: 10]. If the dropdown does not have the form type you need, ask a developer to create a new custom form type[cite: 10].</p>
                </div>

                <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400">
                    <LuSettings2 class="h-3.5 w-3.5" />
                    Steps to Add an Existing Form
                </h4>
                <ol class="ml-1 list-inside list-decimal space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300">
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
                        <ul class="ml-5 mt-2 list-none space-y-1.5">
                            <li class="flex items-center gap-2">
                                <div class="h-1 w-1 rounded-full bg-sky-400"></div>
                                Required vs. optional[cite: 10]
                            </li>
                            <li class="flex items-center gap-2">
                                <div class="h-1 w-1 rounded-full bg-sky-400"></div>
                                Open/close dates[cite: 10]
                            </li>
                            <li class="flex items-center gap-2">
                                <div class="h-1 w-1 rounded-full bg-sky-400"></div>
                                Max slots[cite: 10]
                            </li>
                            <li class="flex items-center gap-2">
                                <div class="h-1 w-1 rounded-full bg-sky-400"></div>
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
            class="space-y-4 border-t border-slate-100 pt-6 dark:border-slate-800/60">
            <div class="mb-4 ml-1">
                <h3 class="mb-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">For Programmers (Implementation)</h3>
                <p class="text-xs font-medium leading-relaxed text-slate-500 dark:text-slate-400">
                    A custom form type is identified by a unique slug (e.g.,
                    <code class="rounded-md border border-slate-200 bg-slate-100 px-1.5 py-0.5 font-mono text-[0.65rem] dark:border-slate-700 dark:bg-slate-800">speaker_evaluation</code>
                    )[cite: 10]. Adding a new type requires changes in both backend validation and frontend UI[cite: 10].
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <!-- Step 1 & 2 -->
                <div class="space-y-4">
                    <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-5 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/5">
                        <h4 class="mb-2.5 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400">
                            <LuCode2 class="h-3.5 w-3.5" />
                            Step 1: Choose Slug
                        </h4>
                        <p class="text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-300">
                            Pick a short, lowercase, underscore-separated slug, e.g.
                            <code class="rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-amber-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-amber-400">speaker_evaluation</code>
                            [cite: 10]. Use this slug in all steps below[cite: 10].
                        </p>
                    </div>

                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/5">
                        <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                            <LuDatabase class="h-3.5 w-3.5" />
                            Step 2: Backend Registration
                        </h4>
                        <ul class="space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300">
                            <li>
                                <span class="mb-1 block font-semibold text-slate-800 dark:text-slate-200">Add the new enum case:</span>
                                Update
                                <code class="rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] text-emerald-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-400">app/Enums/Subform.php</code>
                                [cite: 10].
                            </li>
                            <li>
                                <span class="mb-1 block font-semibold text-slate-800 dark:text-slate-200">Add validation rules:</span>
                                Update
                                <code class="rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] text-emerald-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-400">config/subformtypes.php</code>
                                [cite: 10].
                            </li>
                            <li>
                                <span class="mb-1 block font-semibold text-slate-800 dark:text-slate-200">Update participant requirements (if needed):</span>
                                Update
                                <code class="break-all rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] text-emerald-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-emerald-400">app/Http/Requests/CreateEventSubformResponseRequest.php</code>
                                [cite: 10].
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Step 3 & 4 -->
                <div class="space-y-4">
                    <div class="rounded-xl border border-purple-100 bg-purple-50/50 p-5 shadow-sm dark:border-purple-500/20 dark:bg-purple-500/5">
                        <h4 class="mb-2.5 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-purple-600 dark:text-purple-400">
                            <LuLayoutTemplate class="h-3.5 w-3.5" />
                            Step 3: Default Field Structure
                        </h4>
                        <p class="mb-2 text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-300">Define the default field values used by the UI when the form loads[cite: 10]:</p>
                        <p class="mb-2 text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-300">
                            Update
                            <code class="break-all rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] text-purple-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-purple-400">resources/js/Modules/dto/DtoSubformResponse.ts</code>
                            [cite: 10].
                        </p>
                        <p class="text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-300">
                            Add a new
                            <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">case</code>
                            in
                            <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">getSubformFields()</code>
                            that returns your new fields[cite: 10].
                        </p>
                    </div>

                    <div class="rounded-xl border border-pink-100 bg-pink-50/50 p-5 shadow-sm dark:border-pink-500/20 dark:bg-pink-500/5">
                        <h4 class="mb-2.5 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-pink-600 dark:text-pink-400">
                            <LuFileCode class="h-3.5 w-3.5" />
                            Step 4: Create Form Component
                        </h4>
                        <p class="mb-2 text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-300">Create a new Vue component that renders the inputs and submission logic[cite: 10].</p>
                        <p class="mb-3 text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-300">
                            Location:
                            <code class="break-all rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] text-pink-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-pink-400">resources/js/Pages/Forms/components</code>
                            [cite: 10].
                        </p>
                        <p class="mb-2 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500">Examples to follow:</p>
                        <ul class="ml-1 space-y-1.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                            <li class="flex items-center gap-2">
                                <div class="h-1 w-1 rounded-full bg-pink-400"></div>
                                <code class="font-mono text-[0.65rem]">RegistrationCard.vue</code>
                                [cite: 10]
                            </li>
                            <li class="flex items-center gap-2">
                                <div class="h-1 w-1 rounded-full bg-pink-400"></div>
                                <code class="font-mono text-[0.65rem]">FeedbackCard.vue</code>
                                [cite: 10]
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Step 5, 6, 7 -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:col-span-2">
                    <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-5 shadow-sm dark:border-indigo-500/20 dark:bg-indigo-500/5">
                        <h4 class="mb-2.5 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">
                            <LuGitMerge class="h-3.5 w-3.5" />
                            Step 5: Guest Workflow
                        </h4>
                        <p class="mb-2 text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-300">To show the new form in the public/guest flow:[cite: 10]</p>
                        <ul class="mb-3 ml-1 space-y-1.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                            <li class="flex items-start gap-2">
                                <div class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-indigo-400"></div>
                                <span class="leading-relaxed">
                                    Add import/registration in
                                    <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">GuestCard.vue</code>
                                    [cite: 10].
                                </span>
                            </li>
                            <li class="flex items-start gap-2">
                                <div class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-indigo-400"></div>
                                <span class="leading-relaxed">
                                    Add label in
                                    <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">labelMap</code>
                                    [cite: 10].
                                </span>
                            </li>
                            <li class="flex items-start gap-2">
                                <div class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-indigo-400"></div>
                                <span class="leading-relaxed">
                                    Add a
                                    <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">v-if</code>
                                    block for content[cite: 10].
                                </span>
                            </li>
                        </ul>
                        <p class="text-[0.65rem] font-medium italic text-indigo-500 dark:text-indigo-400">If not available in guest UI, skip this[cite: 10].</p>
                    </div>

                    <div class="rounded-xl border border-orange-100 bg-orange-50/50 p-5 shadow-sm dark:border-orange-500/20 dark:bg-orange-500/5">
                        <h4 class="mb-2.5 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-orange-600 dark:text-orange-400">
                            <LuListPlus class="h-3.5 w-3.5" />
                            Step 6: Req. Dropdown
                        </h4>
                        <p class="mb-2 text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-300">To make the form type selectable when editing events:[cite: 10]</p>
                        <p class="mb-2 text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-300">
                            Update
                            <code class="break-all rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">RequirementsManager.vue</code>
                            [cite: 10].
                        </p>
                        <p class="text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-300">
                            Add a
                            <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">{ value, label }</code>
                            entry to
                            <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">formTypeOptions</code>
                            [cite: 10].
                        </p>
                    </div>

                    <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-5 shadow-sm dark:border-rose-500/20 dark:bg-rose-500/5">
                        <h4 class="mb-2.5 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400">
                            <LuTestTube class="h-3.5 w-3.5" />
                            Step 7: Tests/Seeders
                        </h4>
                        <p class="mb-3 text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-300">If tests rely on a full list of form types, update them:[cite: 10]</p>
                        <ul class="ml-1 space-y-1.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                            <li class="flex items-start gap-2">
                                <div class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-rose-400"></div>
                                <span class="leading-relaxed">
                                    <code class="break-all rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">EventSubformResponseFactory.php</code>
                                    [cite: 10]
                                </span>
                            </li>
                            <li class="flex items-start gap-2">
                                <div class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-rose-400"></div>
                                <span class="leading-relaxed">Any workflow or feature tests[cite: 10].</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Checklist & Pitfalls -->
            <div class="grid grid-cols-1 gap-6 pt-4 lg:grid-cols-2">
                <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <h3 class="mb-3.5 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                        <LuCheckSquare class="h-3.5 w-3.5" />
                        Quick Checklist
                    </h3>
                    <ul class="space-y-2.5 text-xs font-medium text-slate-700 dark:text-slate-300">
                        <li class="flex items-center gap-2">
                            <LuCheckCircle2 class="h-4 w-4 shrink-0 text-emerald-500" />
                            New enum value added[cite: 10]
                        </li>
                        <li class="flex items-center gap-2">
                            <LuCheckCircle2 class="h-4 w-4 shrink-0 text-emerald-500" />
                            New config validation rules added[cite: 10]
                        </li>
                        <li class="flex items-center gap-2">
                            <LuCheckCircle2 class="h-4 w-4 shrink-0 text-emerald-500" />
                            Participant requirement updated (if needed)[cite: 10]
                        </li>
                        <li class="flex items-center gap-2">
                            <LuCheckCircle2 class="h-4 w-4 shrink-0 text-emerald-500" />
                            New UI defaults added in
                            <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.65rem] dark:border-slate-700 dark:bg-slate-800">getSubformFields()</code>
                            [cite: 10]
                        </li>
                        <li class="flex items-center gap-2">
                            <LuCheckCircle2 class="h-4 w-4 shrink-0 text-emerald-500" />
                            New form component created[cite: 10]
                        </li>
                        <li class="flex items-center gap-2">
                            <LuCheckCircle2 class="h-4 w-4 shrink-0 text-emerald-500" />
                            Guest UI updated (if applicable)[cite: 10]
                        </li>
                        <li class="flex items-center gap-2">
                            <LuCheckCircle2 class="h-4 w-4 shrink-0 text-emerald-500" />
                            Requirements dropdown updated[cite: 10]
                        </li>
                        <li class="flex items-center gap-2">
                            <LuCheckCircle2 class="h-4 w-4 shrink-0 text-emerald-500" />
                            Tests/factories updated[cite: 10]
                        </li>
                    </ul>
                </div>

                <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-5 shadow-sm dark:border-rose-500/20 dark:bg-rose-500/5">
                    <h3 class="mb-3.5 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400">
                        <LuAlertTriangle class="h-3.5 w-3.5" />
                        Common Pitfalls
                    </h3>
                    <ul class="space-y-4 text-xs font-medium text-slate-700 dark:text-slate-300">
                        <li class="flex items-start gap-2.5">
                            <LuXCircle class="mt-0.5 h-4 w-4 shrink-0 text-rose-500" />
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-900 dark:text-slate-200">Form type not in dropdown:</span>
                                Add it to
                                <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.65rem] text-rose-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-rose-400">formTypeOptions</code>
                                [cite: 10].
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuXCircle class="mt-0.5 h-4 w-4 shrink-0 text-rose-500" />
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-900 dark:text-slate-200">Validation fails on submit:</span>
                                Ensure your field names match in both frontend defaults and backend config[cite: 10].
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuXCircle class="mt-0.5 h-4 w-4 shrink-0 text-rose-500" />
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
