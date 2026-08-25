<script>
import TopicLayout from "@/Pages/Manuals/Components/TopicLayout.vue";

export default {
    name: "SystemOptionsTopic",
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
        title="System Options Module"
        description="Manage system-wide settings, configuration values, and dropdown options directly from the database instead of hardcoding them in configuration files[cite: 7]. This makes it easy to update settings without redeploying code[cite: 7]."
        icon="LuSettings">
        <!-- Key Features & Access -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <!-- Key Features -->
            <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                <h3 class="mb-3.5 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                    <LuSparkles class="h-3.5 w-3.5" />
                    Key Features
                </h3>
                <ul class="space-y-2.5 text-xs font-medium text-slate-700 dark:text-slate-300">
                    <li class="flex items-start gap-2.5">
                        <LuCheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-indigo-500" />
                        <span>
                            <span class="font-semibold text-slate-900 dark:text-slate-200">Dynamic Configuration</span>
                            - Update settings without code changes[cite: 7].
                        </span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <LuCheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-indigo-500" />
                        <span>
                            <span class="font-semibold text-slate-900 dark:text-slate-200">Multiple Types</span>
                            - Support for text, numbers, booleans, JSON, select options, and more[cite: 7].
                        </span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <LuCheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-indigo-500" />
                        <span>
                            <span class="font-semibold text-slate-900 dark:text-slate-200">Organized Groups</span>
                            - Group related options (system, email, inventory, etc.)[cite: 7].
                        </span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <LuCheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-indigo-500" />
                        <span>
                            <span class="font-semibold text-slate-900 dark:text-slate-200">Full CRUD & API</span>
                            - Manage options via UI or REST API endpoints[cite: 7].
                        </span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <LuCheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-indigo-500" />
                        <span>
                            <span class="font-semibold text-slate-900 dark:text-slate-200">Audit Trail</span>
                            - Tracks creation and modification timestamps[cite: 7].
                        </span>
                    </li>
                </ul>
            </div>

            <!-- Where to Access -->
            <div class="flex flex-col rounded-xl border border-sky-100 bg-sky-50/50 p-5 shadow-sm dark:border-sky-500/20 dark:bg-sky-500/10">
                <h3 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400">
                    <LuDatabase class="h-3.5 w-3.5" />
                    Where to Access
                </h3>
                <p class="mb-4 text-xs font-medium leading-relaxed text-slate-700 dark:text-slate-300">You can manage system options directly from the admin panel interface[cite: 7].</p>
                <div class="mt-auto flex items-center gap-2 rounded-lg border border-sky-100 bg-white/60 p-3 dark:border-sky-500/20 dark:bg-slate-900/50">
                    <LuMonitorSmartphone class="h-4 w-4 shrink-0 text-sky-500" />
                    <code class="font-mono text-xs font-semibold text-sky-700 dark:text-sky-300">/apps/system/options</code>
                </div>
            </div>
        </div>

        <!-- Available Option Groups -->
        <div class="space-y-3">
            <h3 class="ml-1 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                <LuLayoutList class="h-3.5 w-3.5" />
                Available Option Groups
            </h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="flex flex-col gap-1.5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition-colors hover:border-blue-300 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-blue-600">
                    <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400">
                        <LuMonitorSmartphone class="h-4 w-4" />
                        <span class="text-xs font-semibold uppercase tracking-wide">System</span>
                    </div>
                    <p class="text-[0.65rem] font-medium leading-relaxed text-slate-500 dark:text-slate-400">Application name, version, approving officers, center chief[cite: 7].</p>
                </div>
                <div class="flex flex-col gap-1.5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition-colors hover:border-emerald-300 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-emerald-600">
                    <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400">
                        <LuMail class="h-4 w-4" />
                        <span class="text-xs font-semibold uppercase tracking-wide">Email</span>
                    </div>
                    <p class="text-[0.65rem] font-medium leading-relaxed text-slate-500 dark:text-slate-400">Email settings, sender information, notification emails[cite: 7].</p>
                </div>
                <div class="flex flex-col gap-1.5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition-colors hover:border-purple-300 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-purple-600">
                    <div class="flex items-center gap-2 text-purple-600 dark:text-purple-400">
                        <LuPackage class="h-4 w-4" />
                        <span class="text-xs font-semibold uppercase tracking-wide">Inventory</span>
                    </div>
                    <p class="text-[0.65rem] font-medium leading-relaxed text-slate-500 dark:text-slate-400">Stock levels, storage locations, equipment logger mode choices[cite: 7].</p>
                </div>
                <div class="flex flex-col gap-1.5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition-colors hover:border-amber-300 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-amber-600">
                    <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400">
                        <LuFileText class="h-4 w-4" />
                        <span class="text-xs font-semibold uppercase tracking-wide">Forms</span>
                    </div>
                    <p class="text-[0.65rem] font-medium leading-relaxed text-slate-500 dark:text-slate-400">Form submission deadlines, maximum slots[cite: 7].</p>
                </div>
                <div class="flex flex-col gap-1.5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition-colors hover:border-rose-300 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-rose-600">
                    <div class="flex items-center gap-2 text-rose-600 dark:text-rose-400">
                        <LuKey class="h-4 w-4" />
                        <span class="text-xs font-semibold uppercase tracking-wide">Rental</span>
                    </div>
                    <p class="text-[0.65rem] font-medium leading-relaxed text-slate-500 dark:text-slate-400">Vehicles, event halls, cancellation policies[cite: 7].</p>
                </div>
                <div class="flex flex-col gap-1.5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition-colors hover:border-indigo-300 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-indigo-600">
                    <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
                        <LuLayoutList class="h-4 w-4" />
                        <span class="text-xs font-semibold uppercase tracking-wide">Requests</span>
                    </div>
                    <p class="text-[0.65rem] font-medium leading-relaxed text-slate-500 dark:text-slate-400">Supply types, equipment types, materials, space types[cite: 7].</p>
                </div>
                <div class="flex flex-col gap-1.5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition-colors hover:border-pink-300 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-pink-600">
                    <div class="flex items-center gap-2 text-pink-600 dark:text-pink-400">
                        <LuMapPin class="h-4 w-4" />
                        <span class="text-xs font-semibold uppercase tracking-wide">Locations</span>
                    </div>
                    <p class="text-[0.65rem] font-medium leading-relaxed text-slate-500 dark:text-slate-400">Office spaces, screenhouses, laboratories[cite: 7].</p>
                </div>
                <div class="flex flex-col gap-1.5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition-colors hover:border-cyan-300 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-cyan-600">
                    <div class="flex items-center gap-2 text-cyan-600 dark:text-cyan-400">
                        <LuBarChart3 class="h-4 w-4" />
                        <span class="text-xs font-semibold uppercase tracking-wide">Reports</span>
                    </div>
                    <p class="text-[0.65rem] font-medium leading-relaxed text-slate-500 dark:text-slate-400">Report templates with field definitions[cite: 7].</p>
                </div>
            </div>
        </div>

        <!-- CRUD Operations -->
        <div class="space-y-3">
            <h3 class="ml-1 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                <LuSettings class="h-3.5 w-3.5" />
                Managing Options
            </h3>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <!-- Create -->
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/10">
                    <h4 class="mb-3 flex items-center gap-2 text-xs font-semibold text-emerald-800 dark:text-emerald-300">
                        <LuPlusCircle class="h-4 w-4" />
                        Create an Option
                    </h4>
                    <ol class="list-inside list-decimal space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li>
                            Go to
                            <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.65rem] dark:border-slate-700 dark:bg-slate-800">/apps/system/options</code>
                            [cite: 7]
                        </li>
                        <li>
                            Click
                            <strong>+ New Option</strong>
                            [cite: 7]
                        </li>
                        <li>Fill in Key (snake_case), Label, Description, Type, Group, and Value[cite: 7]</li>
                        <li>
                            Click
                            <strong>Create Option</strong>
                            [cite: 7]
                        </li>
                    </ol>
                </div>

                <!-- Edit -->
                <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-5 shadow-sm dark:border-blue-500/20 dark:bg-blue-500/10">
                    <h4 class="mb-3 flex items-center gap-2 text-xs font-semibold text-blue-800 dark:text-blue-300">
                        <LuEdit2 class="h-4 w-4" />
                        Edit an Option
                    </h4>
                    <ol class="list-inside list-decimal space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li>Find the option in the table[cite: 7]</li>
                        <li>
                            Click the
                            <strong>Edit</strong>
                            button[cite: 7]
                        </li>
                        <li>
                            Update fields
                            <span class="italic text-slate-400 dark:text-slate-500">(Key and Type cannot be changed)</span>
                            [cite: 7]
                        </li>
                        <li>
                            Click
                            <strong>Save Changes</strong>
                            [cite: 7]
                        </li>
                    </ol>
                </div>

                <!-- Delete -->
                <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-5 shadow-sm dark:border-rose-500/20 dark:bg-rose-500/10">
                    <h4 class="mb-3 flex items-center gap-2 text-xs font-semibold text-rose-800 dark:text-rose-300">
                        <LuTrash2 class="h-4 w-4" />
                        Delete an Option
                    </h4>
                    <ol class="list-inside list-decimal space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li>Find the option in the table[cite: 7]</li>
                        <li>
                            Click the
                            <strong>Delete</strong>
                            button[cite: 7]
                        </li>
                        <li>Confirm the deletion[cite: 7]</li>
                        <li class="font-semibold text-rose-600 dark:text-rose-400">Warning: This action cannot be undone[cite: 7]</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Option Types Explained -->
        <div class="space-y-3">
            <h3 class="ml-1 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                <LuType class="h-3.5 w-3.5" />
                Option Types Explained
            </h3>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-4 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <LuType class="mb-2 h-4 w-4 text-indigo-500" />
                    <h4 class="mb-1 text-xs font-semibold text-slate-800 dark:text-slate-200">Text</h4>
                    <p class="mb-2 text-[0.65rem] font-medium text-slate-500 dark:text-slate-400">Single-line input for short strings[cite: 7].</p>
                    <code class="font-mono text-[0.6rem] text-slate-400 dark:text-slate-500">app_name</code>
                </div>
                <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-4 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <LuHash class="mb-2 h-4 w-4 text-indigo-500" />
                    <h4 class="mb-1 text-xs font-semibold text-slate-800 dark:text-slate-200">Number</h4>
                    <p class="mb-2 text-[0.65rem] font-medium text-slate-500 dark:text-slate-400">Numeric input for counts/thresholds[cite: 7].</p>
                    <code class="font-mono text-[0.6rem] text-slate-400 dark:text-slate-500">max_slots</code>
                </div>
                <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-4 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <LuAlignLeft class="mb-2 h-4 w-4 text-indigo-500" />
                    <h4 class="mb-1 text-xs font-semibold text-slate-800 dark:text-slate-200">Textarea</h4>
                    <p class="mb-2 text-[0.65rem] font-medium text-slate-500 dark:text-slate-400">Multi-line input for descriptions[cite: 7].</p>
                    <code class="font-mono text-[0.6rem] text-slate-400 dark:text-slate-500">footer_text</code>
                </div>
                <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-4 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <LuToggleLeft class="mb-2 h-4 w-4 text-indigo-500" />
                    <h4 class="mb-1 text-xs font-semibold text-slate-800 dark:text-slate-200">Boolean</h4>
                    <p class="mb-2 text-[0.65rem] font-medium text-slate-500 dark:text-slate-400">True/false toggle for feature flags[cite: 7].</p>
                    <code class="font-mono text-[0.6rem] text-slate-400 dark:text-slate-500">enable_api</code>
                </div>
                <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-4 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <LuLayoutList class="mb-2 h-4 w-4 text-indigo-500" />
                    <h4 class="mb-1 text-xs font-semibold text-slate-800 dark:text-slate-200">Select</h4>
                    <p class="mb-2 text-[0.65rem] font-medium text-slate-500 dark:text-slate-400">Dropdown with predefined choices[cite: 7].</p>
                    <code class="font-mono text-[0.6rem] text-slate-400 dark:text-slate-500">default_theme</code>
                </div>
                <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-4 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <LuCheckSquare class="mb-2 h-4 w-4 text-indigo-500" />
                    <h4 class="mb-1 text-xs font-semibold text-slate-800 dark:text-slate-200">Checkbox</h4>
                    <p class="mb-2 text-[0.65rem] font-medium text-slate-500 dark:text-slate-400">Boolean value displayed as checkbox[cite: 7].</p>
                    <code class="font-mono text-[0.6rem] text-slate-400 dark:text-slate-500">is_active</code>
                </div>
                <div class="flex items-start gap-4 rounded-xl border border-slate-200/60 bg-slate-50/50 p-4 shadow-sm md:col-span-2 dark:border-slate-700/60 dark:bg-slate-800/30">
                    <LuBraces class="h-6 w-6 shrink-0 text-indigo-500" />
                    <div>
                        <h4 class="mb-1 text-xs font-semibold text-slate-800 dark:text-slate-200">JSON</h4>
                        <p class="mb-1.5 text-[0.65rem] font-medium leading-relaxed text-slate-500 dark:text-slate-400">Complex data structure. Use for arrays, objects, and nested data[cite: 7].</p>
                        <code class="font-mono text-[0.6rem] text-slate-400 dark:text-slate-500">storage_locations</code>
                    </div>
                </div>
            </div>
        </div>

        <!-- Using Options in Code (Developer) -->
        <div
            v-if="showDeveloperSections"
            class="space-y-3">
            <h3 class="ml-1 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                <LuCode2 class="h-3.5 w-3.5" />
                Using Options in Code
            </h3>
            <p class="mb-3 ml-1 text-xs font-medium text-slate-600 dark:text-slate-400">Developers can access options programmatically in the application code[cite: 7]:</p>

            <!-- VS Code Theme Block -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-[#1e1e1e] shadow-inner dark:border-[#2d2d2d]">
                <div class="flex items-center border-b border-[#1e1e1e] bg-[#2d2d2d] px-4 py-2.5">
                    <div class="flex gap-1.5">
                        <div class="h-2.5 w-2.5 rounded-full bg-[#ff5f56]"></div>
                        <div class="h-2.5 w-2.5 rounded-full bg-[#ffbd2e]"></div>
                        <div class="h-2.5 w-2.5 rounded-full bg-[#27c93f]"></div>
                    </div>
                    <div class="ml-4 font-mono text-[0.65rem] font-medium tracking-wide text-[#858585]">OptionsExample.php</div>
                </div>
                <pre class="vscode-scrollbar overflow-x-auto p-4 font-mono text-[0.75rem] leading-loose text-[#d4d4d4]"><code class="!bg-transparent !p-0 !border-0 !shadow-none !text-inherit"><span class="text-[#6a9955]">// Get a single option:[cite: 7]</span>
<span class="text-[#9cdcfe]">$appName</span> = <span class="text-[#4ec9b0]">Option</span>::<span class="text-[#dcdcaa]">getByKey</span>(<span class="text-[#ce9178]">'app_name'</span>);

<span class="text-[#6a9955]">// Get all options in a group:[cite: 7]</span>
<span class="text-[#9cdcfe]">$emails</span> = <span class="text-[#4ec9b0]">Option</span>::<span class="text-[#dcdcaa]">getByGroup</span>(<span class="text-[#ce9178]">'email'</span>);

<span class="text-[#6a9955]">// Check a boolean option:[cite: 7]</span>
<span class="text-[#c586c0]">if</span> (<span class="text-[#4ec9b0]">Option</span>::<span class="text-[#dcdcaa]">getByKey</span>(<span class="text-[#ce9178]">'enable_api'</span>) === <span class="text-[#ce9178]">'true'</span>) { 
    <span class="text-[#6a9955]">// ...</span>
}</code></pre>
            </div>
        </div>

        <!-- Tips & Best Practices -->
        <div class="grid grid-cols-1 gap-6 pt-2 md:grid-cols-2">
            <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/5">
                <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                    <LuCheckCircle2 class="h-3.5 w-3.5" />
                    Best Practices
                </h4>
                <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                    <li class="flex items-start gap-2">
                        <div class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-400"></div>
                        Use descriptive labels and keys[cite: 7].
                    </li>
                    <li class="flex items-start gap-2">
                        <div class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-400"></div>
                        Add helpful descriptions for each option[cite: 7].
                    </li>
                    <li class="flex items-start gap-2">
                        <div class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-400"></div>
                        Use snake_case for key names[cite: 7].
                    </li>
                    <li class="flex items-start gap-2">
                        <div class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-400"></div>
                        Backup your database before bulk changes[cite: 7].
                    </li>
                </ul>
            </div>

            <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-5 shadow-sm dark:border-rose-500/20 dark:bg-rose-500/5">
                <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400">
                    <LuXCircle class="h-3.5 w-3.5" />
                    Avoid Doing
                </h4>
                <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                    <li class="flex items-start gap-2">
                        <div class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-rose-400"></div>
                        Store sensitive data (passwords, API keys)[cite: 7].
                    </li>
                    <li class="flex items-start gap-2">
                        <div class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-rose-400"></div>
                        Use special characters in option keys[cite: 7].
                    </li>
                    <li class="flex items-start gap-2">
                        <div class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-rose-400"></div>
                        Create options with identical keys[cite: 7].
                    </li>
                    <li class="flex items-start gap-2">
                        <div class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-rose-400"></div>
                        Store extremely large data in JSON options[cite: 7].
                    </li>
                </ul>
            </div>
        </div>

        <!-- Need Help Footer -->
        <div class="mt-6 flex items-center gap-3 rounded-xl border border-indigo-100 bg-indigo-50/80 p-4 shadow-sm dark:border-indigo-500/30 dark:bg-indigo-500/10">
            <LuBookOpen class="h-5 w-5 shrink-0 text-indigo-500" />
            <p class="text-xs font-medium text-indigo-900 dark:text-indigo-200">
                For detailed technical documentation, visit the
                <span class="font-semibold">System Options Module</span>
                documentation in the docs folder or ask your administrator[cite: 7].
            </p>
        </div>
    </TopicLayout>
</template>

<style scoped>
/* VS Code Scrollbar Replication */
.vscode-scrollbar::-webkit-scrollbar {
    height: 10px;
    width: 10px;
}
.vscode-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.vscode-scrollbar::-webkit-scrollbar-thumb {
    background: #424242;
    border: 2px solid #1e1e1e;
    border-radius: 6px;
}
.vscode-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #4f4f4f;
}

/* Force strip global backgrounds applied to code tags */
pre code {
    background-color: transparent !important;
    padding: 0 !important;
    border: none !important;
    box-shadow: none !important;
    color: inherit !important;
}
</style>
