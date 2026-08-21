<script>
import TopicLayout from '../Components/TopicLayout.vue';

export default {
    name: 'FESRequestFormTopic',
    components: {
        TopicLayout
    },
    props: {
        showDeveloperSections: {
            type: Boolean,
            default: true,
        },
    },
}
</script>

<template>
    <TopicLayout
        title="Facilities, Equipment & Supplies (FES)"
        description="Allows researchers, students, and staff to request access to laboratory facilities, equipment, and supplies."
        icon="LuWrench"
    >
        <!-- For Non-Programmers -->
        <div class="space-y-4">
            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">For Non-Programmers (Requestors & Administrators)</h3>

                    <div class="bg-sky-50/50 dark:bg-sky-500/5 border border-sky-100 dark:border-sky-500/20 rounded-xl p-5 shadow-sm">
                        <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400 mb-4 flex items-center gap-1.5">
                            <LuListPlus class="w-3.5 h-3.5" /> How to Submit a Request
                        </h4>
                        <ol class="space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside ml-1">
                            <li><span class="font-semibold text-slate-800 dark:text-slate-200">Access the form:</span> Navigate to FES Request Form or use the public link.</li>
                            <li><span class="font-semibold text-slate-800 dark:text-slate-200">Fill requestor information:</span> Name, Affiliation, Position, Email, Phone.</li>
                            <li><span class="font-semibold text-slate-800 dark:text-slate-200">Select request type(s):</span> Check all that apply (Supplies, Equipment, Laboratory Access).</li>
                            <li><span class="font-semibold text-slate-800 dark:text-slate-200">Provide request details:</span> Purpose, Project title, Special instructions.</li>
                            <li><span class="font-semibold text-slate-800 dark:text-slate-200">Specify items needed:</span> List supplies, equipment, or facilities required.</li>
                            <li><span class="font-semibold text-slate-800 dark:text-slate-200">Set date and time:</span> Specify when you need the resources.</li>
                            <li><span class="font-semibold text-slate-800 dark:text-slate-200">Accept terms & conditions:</span> Read and agree to all clauses.</li>
                            <li><span class="font-semibold text-slate-800 dark:text-slate-200">Submit:</span> Click submit to send your request.</li>
                        </ol>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Request Status -->
                        <div class="bg-emerald-50/50 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-xl p-5 shadow-sm">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-3 flex items-center gap-1.5">
                                <LuActivity class="w-3.5 h-3.5" /> Request Status & Tracking
                            </h4>
                            <ul class="space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                                <li class="flex items-start gap-2.5">
                                    <LuCheckCircle2 class="w-3.5 h-3.5 text-emerald-400 shrink-0 mt-0.5" /> 
                                    <p class="leading-relaxed"><span class="font-semibold text-slate-800 dark:text-slate-200">Pending:</span> Submitted and awaiting review.</p>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <LuCheckCircle2 class="w-3.5 h-3.5 text-emerald-400 shrink-0 mt-0.5" /> 
                                    <p class="leading-relaxed"><span class="font-semibold text-slate-800 dark:text-slate-200">Approved:</span> Approved. You can now use resources.</p>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <LuCheckCircle2 class="w-3.5 h-3.5 text-emerald-400 shrink-0 mt-0.5" /> 
                                    <p class="leading-relaxed"><span class="font-semibold text-slate-800 dark:text-slate-200">Rejected:</span> Not approved. Contact admin for details.</p>
                                </li>
                            </ul>
                        </div>

                        <!-- Admin Workflow -->
                        <div class="bg-amber-50/50 dark:bg-amber-500/5 border border-amber-100 dark:border-amber-500/20 rounded-xl p-5 shadow-sm">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400 mb-3 flex items-center gap-1.5">
                                <LuShieldCheck class="w-3.5 h-3.5" /> Administrator Workflow
                            </h4>
                            <ol class="space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside ml-1">
                                <li>Navigate to <span class="font-semibold text-slate-800 dark:text-slate-200">FES Request Form</span> section.</li>
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
                <div v-if="showDeveloperSections" class="space-y-6 pt-6 border-t border-slate-100 dark:border-slate-800/60">
                    <div class="mb-4 ml-1">
                        <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1.5">For Programmers (Customization)</h3>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 leading-relaxed">
                            The FES Request Form is built on a flexible form system. Modifications require changes to both the request model/validation and the Vue component.
                        </p>
                    </div>
                    
                    <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                        <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-600 dark:text-slate-300 mb-4 flex items-center gap-1.5">
                            <LuDatabase class="w-3.5 h-3.5" /> Form Structure Overview
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-medium text-slate-600 dark:text-slate-400">
                            <div>
                                <span class="block mb-1.5 text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider text-[0.6rem]">Database Model</span>
                                <code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] shadow-sm text-indigo-600 dark:text-indigo-400">App\Models\RequestFormPivot</code>
                            </div>
                            <div>
                                <span class="block mb-1.5 text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider text-[0.6rem]">Validation Request</span>
                                <code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] shadow-sm text-indigo-600 dark:text-indigo-400">App\Http\Requests\CreateLabRequest</code>
                            </div>
                            <div>
                                <span class="block mb-1.5 text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider text-[0.6rem]">Frontend Component</span>
                                <code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] shadow-sm text-indigo-600 dark:text-indigo-400 break-all">resources/js/Pages/LabRequest/components/RequesterGuestCard.vue</code>
                            </div>
                            <div>
                                <span class="block mb-1.5 text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider text-[0.6rem]">Data Transfer Objects</span>
                                <div class="flex gap-2">
                                    <code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] shadow-sm text-indigo-600 dark:text-indigo-400">RequestFormPivot.ts</code>
                                    <code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] shadow-sm text-indigo-600 dark:text-indigo-400">DtoRequestForm.ts</code>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-purple-50/50 dark:bg-purple-500/5 border border-purple-100 dark:border-purple-500/20 rounded-xl p-5 shadow-sm">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-purple-600 dark:text-purple-400 mb-3 flex items-center gap-1.5">
                                <LuPlusCircle class="w-3.5 h-3.5" /> Step 1: Add a New Field
                            </h4>
                            <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                                <li class="flex items-start gap-2"><div class="w-1.5 h-1.5 rounded-full bg-purple-400 mt-1 shrink-0"></div> <span class="leading-relaxed">Update <code class="font-mono text-[0.6rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">CreateLabRequest.php</code> (validation).</span></li>
                                <li class="flex items-start gap-2"><div class="w-1.5 h-1.5 rounded-full bg-purple-400 mt-1 shrink-0"></div> <span class="leading-relaxed">Update <code class="font-mono text-[0.6rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">DtoRequestForm.ts</code> (defaults).</span></li>
                                <li class="flex items-start gap-2"><div class="w-1.5 h-1.5 rounded-full bg-purple-400 mt-1 shrink-0"></div> <span class="leading-relaxed">Add input to <code class="font-mono text-[0.6rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700 break-all">RequesterGuestCard.vue</code>.</span></li>
                            </ul>
                        </div>

                        <div class="bg-pink-50/50 dark:bg-pink-500/5 border border-pink-100 dark:border-pink-500/20 rounded-xl p-5 shadow-sm">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-pink-600 dark:text-pink-400 mb-3 flex items-center gap-1.5">
                                <LuSettings2 class="w-3.5 h-3.5" /> Step 2: Modify Types
                            </h4>
                            <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                                <li class="flex items-start gap-2"><div class="w-1.5 h-1.5 rounded-full bg-pink-400 mt-1 shrink-0"></div> <span class="leading-relaxed">Update <code class="font-mono text-[0.6rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">requestTypeOptions</code> in Vue card.</span></li>
                                <li class="flex items-start gap-2"><div class="w-1.5 h-1.5 rounded-full bg-pink-400 mt-1 shrink-0"></div> <span class="leading-relaxed">Update validation in request class: <code class="font-mono text-[0.6rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">'string|in:...'</code>.</span></li>
                            </ul>
                        </div>

                        <div class="bg-orange-50/50 dark:bg-orange-500/5 border border-orange-100 dark:border-orange-500/20 rounded-xl p-5 shadow-sm">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-orange-600 dark:text-orange-400 mb-3 flex items-center gap-1.5">
                                <LuGitMerge class="w-3.5 h-3.5" /> Step 3: Workflows
                            </h4>
                            <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                                <li class="flex items-start gap-2"><div class="w-1.5 h-1.5 rounded-full bg-orange-400 mt-1 shrink-0"></div> <span class="leading-relaxed">Update <code class="font-mono text-[0.6rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700 break-all">UpdateRequestFormPivot.php</code>.</span></li>
                                <li class="flex items-start gap-2"><div class="w-1.5 h-1.5 rounded-full bg-orange-400 mt-1 shrink-0"></div> <span class="leading-relaxed">Modify <code class="font-mono text-[0.6rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">UseRequestApprovalBtn.vue</code>.</span></li>
                                <li class="flex items-start gap-2"><div class="w-1.5 h-1.5 rounded-full bg-orange-400 mt-1 shrink-0"></div> <span class="leading-relaxed">Update <code class="font-mono text-[0.6rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">RequestFormPivotController.php</code>.</span></li>
                            </ul>
                        </div>

                        <div class="bg-rose-50/50 dark:bg-rose-500/5 border border-rose-100 dark:border-rose-500/20 rounded-xl p-5 shadow-sm">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400 mb-3 flex items-center gap-1.5">
                                <LuFileText class="w-3.5 h-3.5" /> Step 4: Modify Clauses
                            </h4>
                            <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                                <li class="flex items-start gap-2"><div class="w-1.5 h-1.5 rounded-full bg-rose-400 mt-1 shrink-0"></div> <span class="leading-relaxed">Update labels in <code class="font-mono text-[0.6rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">RequesterGuestCard.vue</code>.</span></li>
                                <li class="flex items-start gap-2"><div class="w-1.5 h-1.5 rounded-full bg-rose-400 mt-1 shrink-0"></div> <span class="leading-relaxed">Add/remove fields in both request classes.</span></li>
                                <li class="flex items-start gap-2"><div class="w-1.5 h-1.5 rounded-full bg-rose-400 mt-1 shrink-0"></div> <span class="leading-relaxed">Update database migrations.</span></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Key Files & Common Tasks -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-4">
                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-4 flex items-center gap-1.5">
                                <LuFolderTree class="w-3.5 h-3.5" /> Quick Reference Files
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-[0.6rem] font-bold uppercase tracking-widest text-slate-400 mb-2">Backend</p>
                                    <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-400 ml-1">
                                        <li><code class="px-1.5 py-0.5 mr-1.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] shadow-sm text-indigo-500">RequestFormPivot.php</code></li>
                                        <li><code class="px-1.5 py-0.5 mr-1.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] shadow-sm text-indigo-500">UseRequestForm.php</code></li>
                                        <li><code class="px-1.5 py-0.5 mr-1.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] shadow-sm text-indigo-500">CreateLabRequest.php</code></li>
                                        <li><code class="px-1.5 py-0.5 mr-1.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] shadow-sm text-indigo-500">LabRequestFormController.php</code></li>
                                    </ul>
                                </div>
                                <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700/60">
                                    <p class="text-[0.6rem] font-bold uppercase tracking-widest text-slate-400 mb-2">Frontend</p>
                                    <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-400 ml-1">
                                        <li><code class="px-1.5 py-0.5 mr-1.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] shadow-sm text-emerald-500 break-all">UseRequestFormGuest.vue</code></li>
                                        <li><code class="px-1.5 py-0.5 mr-1.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] shadow-sm text-emerald-500 break-all">RequesterGuestCard.vue</code></li>
                                        <li><code class="px-1.5 py-0.5 mr-1.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] shadow-sm text-emerald-500 break-all">UseRequestCard.vue</code></li>
                                        <li><code class="px-1.5 py-0.5 mr-1.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.6rem] shadow-sm text-emerald-500">RequestFormPivot.ts</code></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="bg-indigo-50/50 dark:bg-indigo-500/5 border border-indigo-100 dark:border-indigo-500/20 rounded-xl p-5 shadow-sm">
                            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 mb-4 flex items-center gap-1.5">
                                <LuCheckSquare class="w-3.5 h-3.5" /> Common Tasks
                            </h3>
                            <ul class="space-y-4 text-xs font-medium text-slate-700 dark:text-slate-300">
                                <li class="flex items-start gap-2.5">
                                    <LuCheckCircle2 class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5" /> 
                                    <p class="leading-relaxed"><span class="font-semibold text-slate-900 dark:text-slate-200 block mb-0.5">Add a new request type:</span> Update validation and dropdown options in request handling files.</p>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <LuCheckCircle2 class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5" /> 
                                    <p class="leading-relaxed"><span class="font-semibold text-slate-900 dark:text-slate-200 block mb-0.5">Change approval workflow:</span> Modify <code class="font-mono text-[0.6rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded shadow-sm border border-slate-200 dark:border-slate-700">UpdateRequestFormPivot.php</code> and approval component.</p>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <LuCheckCircle2 class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5" /> 
                                    <p class="leading-relaxed"><span class="font-semibold text-slate-900 dark:text-slate-200 block mb-0.5">Export requests as PDF:</span> Located in <code class="font-mono text-[0.6rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded shadow-sm border border-slate-200 dark:border-slate-700 break-all">printable-request-form.blade.php</code>.</p>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <LuCheckCircle2 class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5" /> 
                                    <p class="leading-relaxed"><span class="font-semibold text-slate-900 dark:text-slate-200 block mb-0.5">Modify multi-step layout:</span> Edit the <code class="font-mono text-[0.6rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded shadow-sm border border-slate-200 dark:border-slate-700">steps</code> array in <code class="font-mono text-[0.6rem] bg-white dark:bg-slate-800 px-1 py-0.5 rounded shadow-sm border border-slate-200 dark:border-slate-700">RequesterGuestCard.vue</code>.</p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

    </TopicLayout>
</template>