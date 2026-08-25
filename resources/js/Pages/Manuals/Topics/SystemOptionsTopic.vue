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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Key Features -->
            <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-3.5 flex items-center gap-1.5">
                    <LuSparkles class="w-3.5 h-3.5" />
                    Key Features
                </h3>
                <ul class="space-y-2.5 text-xs font-medium text-slate-700 dark:text-slate-300">
                    <li class="flex items-start gap-2.5">
                        <LuCheckCircle2 class="w-4 h-4 text-indigo-500 shrink-0 mt-0.5" />
                        <span>
                            <span class="font-semibold text-slate-900 dark:text-slate-200">Dynamic Configuration</span>
                            - Update settings without code changes[cite: 7].
                        </span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <LuCheckCircle2 class="w-4 h-4 text-indigo-500 shrink-0 mt-0.5" />
                        <span>
                            <span class="font-semibold text-slate-900 dark:text-slate-200">Multiple Types</span>
                            - Support for text, numbers, booleans, JSON, select options, and more[cite: 7].
                        </span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <LuCheckCircle2 class="w-4 h-4 text-indigo-500 shrink-0 mt-0.5" />
                        <span>
                            <span class="font-semibold text-slate-900 dark:text-slate-200">Organized Groups</span>
                            - Group related options (system, email, inventory, etc.)[cite: 7].
                        </span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <LuCheckCircle2 class="w-4 h-4 text-indigo-500 shrink-0 mt-0.5" />
                        <span>
                            <span class="font-semibold text-slate-900 dark:text-slate-200">Full CRUD & API</span>
                            - Manage options via UI or REST API endpoints[cite: 7].
                        </span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <LuCheckCircle2 class="w-4 h-4 text-indigo-500 shrink-0 mt-0.5" />
                        <span>
                            <span class="font-semibold text-slate-900 dark:text-slate-200">Audit Trail</span>
                            - Tracks creation and modification timestamps[cite: 7].
                        </span>
                    </li>
                </ul>
            </div>

            <!-- Where to Access -->
            <div class="bg-sky-50/50 dark:bg-sky-500/10 border border-sky-100 dark:border-sky-500/20 rounded-xl p-5 shadow-sm flex flex-col">
                <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400 mb-3 flex items-center gap-1.5">
                    <LuDatabase class="w-3.5 h-3.5" />
                    Where to Access
                </h3>
                <p class="text-xs font-medium text-slate-700 dark:text-slate-300 mb-4 leading-relaxed">You can manage system options directly from the admin panel interface[cite: 7].</p>
                <div class="mt-auto p-3 bg-white/60 dark:bg-slate-900/50 rounded-lg border border-sky-100 dark:border-sky-500/20 flex items-center gap-2">
                    <LuMonitorSmartphone class="w-4 h-4 text-sky-500 shrink-0" />
                    <code class="font-mono text-xs font-semibold text-sky-700 dark:text-sky-300">/apps/system/options</code>
                </div>
            </div>
        </div>

        <!-- Available Option Groups -->
        <div class="space-y-3">
            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1 flex items-center gap-1.5">
                <LuLayoutList class="w-3.5 h-3.5" />
                Available Option Groups
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm flex flex-col gap-1.5 hover:border-blue-300 dark:hover:border-blue-600 transition-colors">
                    <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400">
                        <LuMonitorSmartphone class="w-4 h-4" />
                        <span class="text-xs font-semibold uppercase tracking-wide">System</span>
                    </div>
                    <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400 leading-relaxed">Application name, version, approving officers, center chief[cite: 7].</p>
                </div>
                <div class="bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm flex flex-col gap-1.5 hover:border-emerald-300 dark:hover:border-emerald-600 transition-colors">
                    <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400">
                        <LuMail class="w-4 h-4" />
                        <span class="text-xs font-semibold uppercase tracking-wide">Email</span>
                    </div>
                    <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400 leading-relaxed">Email settings, sender information, notification emails[cite: 7].</p>
                </div>
                <div class="bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm flex flex-col gap-1.5 hover:border-purple-300 dark:hover:border-purple-600 transition-colors">
                    <div class="flex items-center gap-2 text-purple-600 dark:text-purple-400">
                        <LuPackage class="w-4 h-4" />
                        <span class="text-xs font-semibold uppercase tracking-wide">Inventory</span>
                    </div>
                    <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400 leading-relaxed">Stock levels, storage locations, equipment logger mode choices[cite: 7].</p>
                </div>
                <div class="bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm flex flex-col gap-1.5 hover:border-amber-300 dark:hover:border-amber-600 transition-colors">
                    <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400">
                        <LuFileText class="w-4 h-4" />
                        <span class="text-xs font-semibold uppercase tracking-wide">Forms</span>
                    </div>
                    <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400 leading-relaxed">Form submission deadlines, maximum slots[cite: 7].</p>
                </div>
                <div class="bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm flex flex-col gap-1.5 hover:border-rose-300 dark:hover:border-rose-600 transition-colors">
                    <div class="flex items-center gap-2 text-rose-600 dark:text-rose-400">
                        <LuKey class="w-4 h-4" />
                        <span class="text-xs font-semibold uppercase tracking-wide">Rental</span>
                    </div>
                    <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400 leading-relaxed">Vehicles, event halls, cancellation policies[cite: 7].</p>
                </div>
                <div class="bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm flex flex-col gap-1.5 hover:border-indigo-300 dark:hover:border-indigo-600 transition-colors">
                    <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
                        <LuLayoutList class="w-4 h-4" />
                        <span class="text-xs font-semibold uppercase tracking-wide">Requests</span>
                    </div>
                    <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400 leading-relaxed">Supply types, equipment types, materials, space types[cite: 7].</p>
                </div>
                <div class="bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm flex flex-col gap-1.5 hover:border-pink-300 dark:hover:border-pink-600 transition-colors">
                    <div class="flex items-center gap-2 text-pink-600 dark:text-pink-400">
                        <LuMapPin class="w-4 h-4" />
                        <span class="text-xs font-semibold uppercase tracking-wide">Locations</span>
                    </div>
                    <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400 leading-relaxed">Office spaces, screenhouses, laboratories[cite: 7].</p>
                </div>
                <div class="bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm flex flex-col gap-1.5 hover:border-cyan-300 dark:hover:border-cyan-600 transition-colors">
                    <div class="flex items-center gap-2 text-cyan-600 dark:text-cyan-400">
                        <LuBarChart3 class="w-4 h-4" />
                        <span class="text-xs font-semibold uppercase tracking-wide">Reports</span>
                    </div>
                    <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400 leading-relaxed">Report templates with field definitions[cite: 7].</p>
                </div>
            </div>
        </div>

        <!-- CRUD Operations -->
        <div class="space-y-3">
            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1 flex items-center gap-1.5">
                <LuSettings class="w-3.5 h-3.5" />
                Managing Options
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <!-- Create -->
                <div class="bg-emerald-50/50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-xs font-semibold text-emerald-800 dark:text-emerald-300 mb-3 flex items-center gap-2">
                        <LuPlusCircle class="w-4 h-4" />
                        Create an Option
                    </h4>
                    <ol class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside">
                        <li>
                            Go to
                            <code class="px-1 py-0.5 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem]">/apps/system/options</code>
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
                <div class="bg-blue-50/50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-3 flex items-center gap-2">
                        <LuEdit2 class="w-4 h-4" />
                        Edit an Option
                    </h4>
                    <ol class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside">
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
                <div class="bg-rose-50/50 dark:bg-rose-500/10 border border-rose-100 dark:border-rose-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-xs font-semibold text-rose-800 dark:text-rose-300 mb-3 flex items-center gap-2">
                        <LuTrash2 class="w-4 h-4" />
                        Delete an Option
                    </h4>
                    <ol class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside">
                        <li>Find the option in the table[cite: 7]</li>
                        <li>
                            Click the
                            <strong>Delete</strong>
                            button[cite: 7]
                        </li>
                        <li>Confirm the deletion[cite: 7]</li>
                        <li class="text-rose-600 dark:text-rose-400 font-semibold">Warning: This action cannot be undone[cite: 7]</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Option Types Explained -->
        <div class="space-y-3">
            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1 flex items-center gap-1.5">
                <LuType class="w-3.5 h-3.5" />
                Option Types Explained
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-4 shadow-sm">
                    <LuType class="w-4 h-4 text-indigo-500 mb-2" />
                    <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-1">Text</h4>
                    <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400 mb-2">Single-line input for short strings[cite: 7].</p>
                    <code class="text-[0.6rem] font-mono text-slate-400 dark:text-slate-500">app_name</code>
                </div>
                <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-4 shadow-sm">
                    <LuHash class="w-4 h-4 text-indigo-500 mb-2" />
                    <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-1">Number</h4>
                    <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400 mb-2">Numeric input for counts/thresholds[cite: 7].</p>
                    <code class="text-[0.6rem] font-mono text-slate-400 dark:text-slate-500">max_slots</code>
                </div>
                <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-4 shadow-sm">
                    <LuAlignLeft class="w-4 h-4 text-indigo-500 mb-2" />
                    <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-1">Textarea</h4>
                    <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400 mb-2">Multi-line input for descriptions[cite: 7].</p>
                    <code class="text-[0.6rem] font-mono text-slate-400 dark:text-slate-500">footer_text</code>
                </div>
                <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-4 shadow-sm">
                    <LuToggleLeft class="w-4 h-4 text-indigo-500 mb-2" />
                    <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-1">Boolean</h4>
                    <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400 mb-2">True/false toggle for feature flags[cite: 7].</p>
                    <code class="text-[0.6rem] font-mono text-slate-400 dark:text-slate-500">enable_api</code>
                </div>
                <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-4 shadow-sm">
                    <LuLayoutList class="w-4 h-4 text-indigo-500 mb-2" />
                    <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-1">Select</h4>
                    <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400 mb-2">Dropdown with predefined choices[cite: 7].</p>
                    <code class="text-[0.6rem] font-mono text-slate-400 dark:text-slate-500">default_theme</code>
                </div>
                <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-4 shadow-sm">
                    <LuCheckSquare class="w-4 h-4 text-indigo-500 mb-2" />
                    <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-1">Checkbox</h4>
                    <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400 mb-2">Boolean value displayed as checkbox[cite: 7].</p>
                    <code class="text-[0.6rem] font-mono text-slate-400 dark:text-slate-500">is_active</code>
                </div>
                <div class="md:col-span-2 bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-4 shadow-sm flex items-start gap-4">
                    <LuBraces class="w-6 h-6 text-indigo-500 shrink-0" />
                    <div>
                        <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-1">JSON</h4>
                        <p class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400 mb-1.5 leading-relaxed">Complex data structure. Use for arrays, objects, and nested data[cite: 7].</p>
                        <code class="text-[0.6rem] font-mono text-slate-400 dark:text-slate-500">storage_locations</code>
                    </div>
                </div>
            </div>
        </div>

        <!-- Using Options in Code (Developer) -->
        <div
            v-if="showDeveloperSections"
            class="space-y-3">
            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1 flex items-center gap-1.5">
                <LuCode2 class="w-3.5 h-3.5" />
                Using Options in Code
            </h3>
            <p class="text-xs font-medium text-slate-600 dark:text-slate-400 mb-3 ml-1">Developers can access options programmatically in the application code[cite: 7]:</p>

            <!-- VS Code Theme Block -->
            <div class="rounded-xl overflow-hidden shadow-inner border border-slate-200 dark:border-[#2d2d2d] bg-[#1e1e1e]">
                <div class="flex items-center px-4 py-2.5 bg-[#2d2d2d] border-b border-[#1e1e1e]">
                    <div class="flex gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-[#ff5f56]"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-[#ffbd2e]"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-[#27c93f]"></div>
                    </div>
                    <div class="ml-4 text-[0.65rem] font-medium text-[#858585] font-mono tracking-wide">OptionsExample.php</div>
                </div>
                <pre class="p-4 text-[0.75rem] font-mono overflow-x-auto leading-loose vscode-scrollbar text-[#d4d4d4]"><code class="!bg-transparent !p-0 !border-0 !shadow-none !text-inherit"><span class="text-[#6a9955]">// Get a single option:[cite: 7]</span>
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
            <div class="bg-emerald-50/50 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-xl p-5 shadow-sm">
                <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-3 flex items-center gap-1.5">
                    <LuCheckCircle2 class="w-3.5 h-3.5" />
                    Best Practices
                </h4>
                <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                    <li class="flex items-start gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 mt-1.5 shrink-0"></div>
                        Use descriptive labels and keys[cite: 7].
                    </li>
                    <li class="flex items-start gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 mt-1.5 shrink-0"></div>
                        Add helpful descriptions for each option[cite: 7].
                    </li>
                    <li class="flex items-start gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 mt-1.5 shrink-0"></div>
                        Use snake_case for key names[cite: 7].
                    </li>
                    <li class="flex items-start gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 mt-1.5 shrink-0"></div>
                        Backup your database before bulk changes[cite: 7].
                    </li>
                </ul>
            </div>

            <div class="bg-rose-50/50 dark:bg-rose-500/5 border border-rose-100 dark:border-rose-500/20 rounded-xl p-5 shadow-sm">
                <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400 mb-3 flex items-center gap-1.5">
                    <LuXCircle class="w-3.5 h-3.5" />
                    Avoid Doing
                </h4>
                <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                    <li class="flex items-start gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-rose-400 mt-1.5 shrink-0"></div>
                        Store sensitive data (passwords, API keys)[cite: 7].
                    </li>
                    <li class="flex items-start gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-rose-400 mt-1.5 shrink-0"></div>
                        Use special characters in option keys[cite: 7].
                    </li>
                    <li class="flex items-start gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-rose-400 mt-1.5 shrink-0"></div>
                        Create options with identical keys[cite: 7].
                    </li>
                    <li class="flex items-start gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-rose-400 mt-1.5 shrink-0"></div>
                        Store extremely large data in JSON options[cite: 7].
                    </li>
                </ul>
            </div>
        </div>

        <!-- Need Help Footer -->
        <div class="mt-6 p-4 bg-indigo-50/80 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/30 rounded-xl shadow-sm flex items-center gap-3">
            <LuBookOpen class="w-5 h-5 text-indigo-500 shrink-0" />
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
