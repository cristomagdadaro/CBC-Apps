<script>
import TopicLayout from "../Components/TopicLayout.vue";

export default {
    name: "FESRequestFormTopic",
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
        title="Facilities, Equipment & Supplies (FES)"
        description="Allows researchers, students, and staff to request access to laboratory facilities, equipment, and supplies."
        icon="LuWrench">
        <!-- For Non-Programmers -->
        <div class="space-y-4">
            <h3 class="ml-1 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">For Non-Programmers (Requestors & Administrators)</h3>

            <div class="rounded-xl border border-sky-100 bg-sky-50/50 p-5 shadow-sm dark:border-sky-500/20 dark:bg-sky-500/5">
                <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400">
                    <LuListPlus class="h-3.5 w-3.5" />
                    How to Submit a Request
                </h4>
                <ol class="ml-1 list-inside list-decimal space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                    <li>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Access the form:</span>
                        Navigate to FES Request Form or use the public link.
                    </li>
                    <li>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Fill requestor information:</span>
                        Name, Affiliation, Position, Email, Phone.
                    </li>
                    <li>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Select request type(s):</span>
                        Check all that apply (Supplies, Equipment, Laboratory Access).
                    </li>
                    <li>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Provide request details:</span>
                        Purpose, Project title, Special instructions.
                    </li>
                    <li>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Specify items needed:</span>
                        List supplies, equipment, or facilities required.
                    </li>
                    <li>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Set date and time:</span>
                        Specify when you need the resources.
                    </li>
                    <li>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Accept terms & conditions:</span>
                        Read and agree to all clauses.
                    </li>
                    <li>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Submit:</span>
                        Click submit to send your request.
                    </li>
                </ol>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Request Status -->
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/5">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                        <LuActivity class="h-3.5 w-3.5" />
                        Request Status & Tracking
                    </h4>
                    <ul class="ml-1 space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-400" />
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Pending:</span>
                                Submitted and awaiting review.
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-400" />
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Approved:</span>
                                Approved. You can now use resources.
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-400" />
                            <p class="leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Rejected:</span>
                                Not approved. Contact admin for details.
                            </p>
                        </li>
                    </ul>
                </div>

                <!-- Admin Workflow -->
                <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-5 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/5">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400">
                        <LuShieldCheck class="h-3.5 w-3.5" />
                        Administrator Workflow
                    </h4>
                    <ol class="ml-1 list-inside list-decimal space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li>
                            Navigate to
                            <span class="font-semibold text-slate-800 dark:text-slate-200">FES Request Form</span>
                            section.
                        </li>
                        <li>View all pending requests.</li>
                        <li>Click on a request to review details.</li>
                        <li>Approve or reject the request.</li>
                        <li>If approved, add any notes/instructions.</li>
                        <li>Requestor will receive confirmation.</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- For Programmers -->
        <div
            v-if="showDeveloperSections"
            class="space-y-6 border-t border-slate-100 pt-6 dark:border-slate-800/60">
            <div class="mb-4 ml-1">
                <h3 class="mb-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">For Programmers (Customization)</h3>
                <p class="text-xs font-medium leading-relaxed text-slate-500 dark:text-slate-400">The FES Request Form is built on a flexible form system. Modifications require changes to both the request model/validation and the Vue component.</p>
            </div>

            <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-600 dark:text-slate-300">
                    <LuDatabase class="h-3.5 w-3.5" />
                    Form Structure Overview
                </h4>
                <div class="grid grid-cols-1 gap-4 text-xs font-medium text-slate-600 md:grid-cols-2 dark:text-slate-400">
                    <div>
                        <span class="mb-1.5 block text-[0.6rem] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Database Model</span>
                        <code class="rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] text-indigo-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-400">App\Models\RequestFormPivot</code>
                    </div>
                    <div>
                        <span class="mb-1.5 block text-[0.6rem] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Validation Request</span>
                        <code class="rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] text-indigo-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-400">App\Http\Requests\CreateLabRequest</code>
                    </div>
                    <div>
                        <span class="mb-1.5 block text-[0.6rem] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Frontend Component</span>
                        <code class="break-all rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] text-indigo-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-400">resources/js/Pages/LabRequest/components/RequesterGuestCard.vue</code>
                    </div>
                    <div>
                        <span class="mb-1.5 block text-[0.6rem] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Data Transfer Objects</span>
                        <div class="flex gap-2">
                            <code class="rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] text-indigo-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-400">RequestFormPivot.ts</code>
                            <code class="rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] text-indigo-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-400">DtoRequestForm.ts</code>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-purple-100 bg-purple-50/50 p-5 shadow-sm dark:border-purple-500/20 dark:bg-purple-500/5">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-purple-600 dark:text-purple-400">
                        <LuPlusCircle class="h-3.5 w-3.5" />
                        Step 1: Add a New Field
                    </h4>
                    <ul class="ml-1 space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li class="flex items-start gap-2">
                            <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-purple-400"></div>
                            <span class="leading-relaxed">
                                Update
                                <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">CreateLabRequest.php</code>
                                (validation).
                            </span>
                        </li>
                        <li class="flex items-start gap-2">
                            <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-purple-400"></div>
                            <span class="leading-relaxed">
                                Update
                                <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">DtoRequestForm.ts</code>
                                (defaults).
                            </span>
                        </li>
                        <li class="flex items-start gap-2">
                            <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-purple-400"></div>
                            <span class="leading-relaxed">
                                Add input to
                                <code class="break-all rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">RequesterGuestCard.vue</code>
                                .
                            </span>
                        </li>
                    </ul>
                </div>

                <div class="rounded-xl border border-pink-100 bg-pink-50/50 p-5 shadow-sm dark:border-pink-500/20 dark:bg-pink-500/5">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-pink-600 dark:text-pink-400">
                        <LuSettings2 class="h-3.5 w-3.5" />
                        Step 2: Modify Types
                    </h4>
                    <ul class="ml-1 space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li class="flex items-start gap-2">
                            <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-pink-400"></div>
                            <span class="leading-relaxed">
                                Update
                                <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">requestTypeOptions</code>
                                in Vue card.
                            </span>
                        </li>
                        <li class="flex items-start gap-2">
                            <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-pink-400"></div>
                            <span class="leading-relaxed">
                                Update validation in request class:
                                <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">'string|in:...'</code>
                                .
                            </span>
                        </li>
                    </ul>
                </div>

                <div class="rounded-xl border border-orange-100 bg-orange-50/50 p-5 shadow-sm dark:border-orange-500/20 dark:bg-orange-500/5">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-orange-600 dark:text-orange-400">
                        <LuGitMerge class="h-3.5 w-3.5" />
                        Step 3: Workflows
                    </h4>
                    <ul class="ml-1 space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li class="flex items-start gap-2">
                            <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-orange-400"></div>
                            <span class="leading-relaxed">
                                Update
                                <code class="break-all rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">UpdateRequestFormPivot.php</code>
                                .
                            </span>
                        </li>
                        <li class="flex items-start gap-2">
                            <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-orange-400"></div>
                            <span class="leading-relaxed">
                                Modify
                                <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">UseRequestApprovalBtn.vue</code>
                                .
                            </span>
                        </li>
                        <li class="flex items-start gap-2">
                            <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-orange-400"></div>
                            <span class="leading-relaxed">
                                Update
                                <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">RequestFormPivotController.php</code>
                                .
                            </span>
                        </li>
                    </ul>
                </div>

                <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-5 shadow-sm dark:border-rose-500/20 dark:bg-rose-500/5">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400">
                        <LuFileText class="h-3.5 w-3.5" />
                        Step 4: Modify Clauses
                    </h4>
                    <ul class="ml-1 space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li class="flex items-start gap-2">
                            <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-rose-400"></div>
                            <span class="leading-relaxed">
                                Update labels in
                                <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">RequesterGuestCard.vue</code>
                                .
                            </span>
                        </li>
                        <li class="flex items-start gap-2">
                            <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-rose-400"></div>
                            <span class="leading-relaxed">Add/remove fields in both request classes.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-rose-400"></div>
                            <span class="leading-relaxed">Update database migrations.</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Key Files & Common Tasks -->
            <div class="grid grid-cols-1 gap-6 pt-4 lg:grid-cols-2">
                <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <h3 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                        <LuFolderTree class="h-3.5 w-3.5" />
                        Quick Reference Files
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <p class="mb-2 text-[0.6rem] font-bold uppercase tracking-widest text-slate-400">Backend</p>
                            <ul class="ml-1 space-y-2 text-xs font-medium text-slate-600 dark:text-slate-400">
                                <li>
                                    <code class="mr-1.5 rounded border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.6rem] text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">RequestFormPivot.php</code>
                                </li>
                                <li>
                                    <code class="mr-1.5 rounded border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.6rem] text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">UseRequestForm.php</code>
                                </li>
                                <li>
                                    <code class="mr-1.5 rounded border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.6rem] text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">CreateLabRequest.php</code>
                                </li>
                                <li>
                                    <code class="mr-1.5 rounded border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.6rem] text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">LabRequestFormController.php</code>
                                </li>
                            </ul>
                        </div>
                        <div class="border-t border-slate-200/60 pt-2 dark:border-slate-700/60">
                            <p class="mb-2 text-[0.6rem] font-bold uppercase tracking-widest text-slate-400">Frontend</p>
                            <ul class="ml-1 space-y-2 text-xs font-medium text-slate-600 dark:text-slate-400">
                                <li>
                                    <code class="mr-1.5 break-all rounded border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.6rem] text-emerald-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">UseRequestFormGuest.vue</code>
                                </li>
                                <li>
                                    <code class="mr-1.5 break-all rounded border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.6rem] text-emerald-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">RequesterGuestCard.vue</code>
                                </li>
                                <li>
                                    <code class="mr-1.5 break-all rounded border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.6rem] text-emerald-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">UseRequestCard.vue</code>
                                </li>
                                <li>
                                    <code class="mr-1.5 rounded border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.6rem] text-emerald-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">RequestFormPivot.ts</code>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-5 shadow-sm dark:border-indigo-500/20 dark:bg-indigo-500/5">
                    <h3 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">
                        <LuCheckSquare class="h-3.5 w-3.5" />
                        Common Tasks
                    </h3>
                    <ul class="space-y-4 text-xs font-medium text-slate-700 dark:text-slate-300">
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-indigo-400" />
                            <p class="leading-relaxed">
                                <span class="mb-0.5 block font-semibold text-slate-900 dark:text-slate-200">Add a new request type:</span>
                                Update validation and dropdown options in request handling files.
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-indigo-400" />
                            <p class="leading-relaxed">
                                <span class="mb-0.5 block font-semibold text-slate-900 dark:text-slate-200">Change approval workflow:</span>
                                Modify
                                <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] shadow-sm dark:border-slate-700 dark:bg-slate-800">UpdateRequestFormPivot.php</code>
                                and approval component.
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-indigo-400" />
                            <p class="leading-relaxed">
                                <span class="mb-0.5 block font-semibold text-slate-900 dark:text-slate-200">Export requests as PDF:</span>
                                Located in
                                <code class="break-all rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] shadow-sm dark:border-slate-700 dark:bg-slate-800">printable-request-form.blade.php</code>
                                .
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <LuCheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-indigo-400" />
                            <p class="leading-relaxed">
                                <span class="mb-0.5 block font-semibold text-slate-900 dark:text-slate-200">Modify multi-step layout:</span>
                                Edit the
                                <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] shadow-sm dark:border-slate-700 dark:bg-slate-800">steps</code>
                                array in
                                <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] shadow-sm dark:border-slate-700 dark:bg-slate-800">RequesterGuestCard.vue</code>
                                .
                            </p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </TopicLayout>
</template>
