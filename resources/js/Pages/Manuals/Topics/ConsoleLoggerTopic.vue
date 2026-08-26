<script>
import TopicLayout from "../Components/TopicLayout.vue";

export default {
    name: "ConsoleLoggerTopic",
    components: {
        TopicLayout,
    },
};
</script>

<template>
    <TopicLayout
        title="Unified Console Logger Service"
        description="CBC-Apps includes a centralized console logging service that automatically enables/disables based on the application environment. This ensures debug logs appear in development but are completely hidden in production."
        icon="LuTerminal">
        <h3 class="ml-1 text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">For Developers</h3>

        <!-- How It Works -->
        <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-5 shadow-sm dark:border-indigo-500/20 dark:bg-indigo-500/10">
            <h4 class="mb-3.5 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-indigo-600 dark:text-indigo-400">
                <LuSettings2 class="h-3.5 w-3.5" />
                How It Works
            </h4>
            <ul class="space-y-2.5 text-sm font-medium text-slate-700 dark:text-slate-300">
                <li class="flex items-start gap-2.5">
                    <LuCheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-indigo-500" />
                    <span>
                        <span class="font-semibold text-slate-900 dark:text-slate-200">Local/Development:</span>
                        All logs are displayed in the browser console.
                    </span>
                </li>
                <li class="flex items-start gap-2.5">
                    <LuCheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-indigo-500" />
                    <span>
                        <span class="font-semibold text-slate-900 dark:text-slate-200">Production:</span>
                        All logs are silently ignored (zero performance impact).
                    </span>
                </li>
                <li class="flex items-start gap-2.5">
                    <LuCheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-indigo-500" />
                    <span class="leading-relaxed">
                        <span class="font-semibold text-slate-900 dark:text-slate-200">Environment Detection:</span>
                        Automatically reads from
                        <code class="mx-0.5 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.7rem] font-semibold text-indigo-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-400">__APP_ENV__</code>
                        or
                        <code class="mx-0.5 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.7rem] font-semibold text-indigo-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-400">VITE_APP_ENV</code>
                        .
                    </span>
                </li>
                <li class="flex items-start gap-2.5">
                    <LuCheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-indigo-500" />
                    <span>
                        <span class="font-semibold text-slate-900 dark:text-slate-200">Manual Control:</span>
                        Can be toggled programmatically if needed.
                    </span>
                </li>
            </ul>
        </div>

        <!-- Basic Usage -->
        <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/10">
            <h4 class="mb-2 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-emerald-600 dark:text-emerald-400">
                <LuCode2 class="h-3.5 w-3.5" />
                Basic Usage
            </h4>
            <p class="mb-3 text-xs font-medium text-emerald-800 dark:text-emerald-200/80">Import and use the logger in any Vue component or JavaScript file:</p>

            <!-- VS Code Theme Block -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-[#1e1e1e] shadow-inner dark:border-[#2d2d2d]">
                <div class="flex items-center border-b border-[#1e1e1e] bg-[#2d2d2d] px-4 py-2.5">
                    <div class="flex gap-1.5">
                        <div class="h-2.5 w-2.5 rounded-full bg-[#ff5f56]"></div>
                        <div class="h-2.5 w-2.5 rounded-full bg-[#ffbd2e]"></div>
                        <div class="h-2.5 w-2.5 rounded-full bg-[#27c93f]"></div>
                    </div>
                    <div class="ml-4 font-mono text-[0.65rem] font-medium tracking-wide text-[#858585]">logger.js</div>
                </div>
                <pre class="vscode-scrollbar overflow-x-auto p-4 font-mono text-[0.75rem] leading-loose text-[#d4d4d4]"><code class="!bg-transparent !p-0 !border-0 !shadow-none !text-inherit"><span class="text-[#c586c0]">import</span> <span class="text-[#9cdcfe]">ConsoleLogger</span> <span class="text-[#c586c0]">from</span> <span class="text-[#ce9178]">'@/Modules/shared/infrastructure/ConsoleLogger'</span>;

<span class="text-[#6a9955]">// Standard logging</span>
<span class="text-[#4ec9b0]">ConsoleLogger</span>.<span class="text-[#dcdcaa]">log</span>(<span class="text-[#ce9178]">'User data loaded'</span>);
<span class="text-[#4ec9b0]">ConsoleLogger</span>.<span class="text-[#dcdcaa]">info</span>(<span class="text-[#ce9178]">'Processing started'</span>);
<span class="text-[#4ec9b0]">ConsoleLogger</span>.<span class="text-[#dcdcaa]">warn</span>(<span class="text-[#ce9178]">'Deprecated API endpoint'</span>);
<span class="text-[#4ec9b0]">ConsoleLogger</span>.<span class="text-[#dcdcaa]">error</span>(<span class="text-[#ce9178]">'Failed to fetch data'</span>);
<span class="text-[#4ec9b0]">ConsoleLogger</span>.<span class="text-[#dcdcaa]">debug</span>(<span class="text-[#ce9178]">'Variable value:'</span>, <span class="text-[#9cdcfe]">someVar</span>);</code></pre>
            </div>
        </div>

        <!-- Available Methods -->
        <div class="rounded-xl border border-purple-100 bg-purple-50/50 p-5 shadow-sm dark:border-purple-500/20 dark:bg-purple-500/10">
            <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-purple-600 dark:text-purple-400">
                <LuLayoutList class="h-3.5 w-3.5" />
                Available Methods
            </h4>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 md:grid-cols-3">
                <!-- Basic Logging -->
                <div class="space-y-2">
                    <p class="text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">Basic Logging</p>
                    <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">log(...args)</code>
                            Standard log
                        </li>
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-blue-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">info(...args)</code>
                            Info level
                        </li>
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-amber-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">warn(...args)</code>
                            Warning level
                        </li>
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-rose-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">error(...args)</code>
                            Error level
                        </li>
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-emerald-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">debug(...args)</code>
                            Debug level
                        </li>
                    </ul>
                </div>

                <!-- Advanced Methods -->
                <div class="space-y-2">
                    <p class="text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">Advanced Methods</p>
                    <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">table(data)</code>
                            Display data as table
                        </li>
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">group(label)</code>
                            Start collapsible group
                        </li>
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">groupEnd()</code>
                            End group
                        </li>
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">time(label)</code>
                            Start performance timer
                        </li>
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">timeEnd(label)</code>
                            Log duration
                        </li>
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">assert(cond)</code>
                            Conditional logging
                        </li>
                    </ul>
                </div>

                <!-- Utility Methods -->
                <div class="space-y-2">
                    <p class="text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">Utility Methods</p>
                    <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">isLoggingEnabled()</code>
                            Check active status
                        </li>
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">getEnvironment()</code>
                            Get current APP_ENV
                        </li>
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">setEnabled(bool)</code>
                            Toggle manually
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Practical Examples -->
        <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-5 shadow-sm dark:border-rose-500/20 dark:bg-rose-500/10">
            <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-rose-600 dark:text-rose-400">
                <LuSparkles class="h-3.5 w-3.5" />
                Practical Examples
            </h4>

            <!-- VS Code Theme Block -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-[#1e1e1e] shadow-inner dark:border-[#2d2d2d]">
                <div class="flex items-center border-b border-[#1e1e1e] bg-[#2d2d2d] px-4 py-2.5">
                    <div class="flex gap-1.5">
                        <div class="h-2.5 w-2.5 rounded-full bg-[#ff5f56]"></div>
                        <div class="h-2.5 w-2.5 rounded-full bg-[#ffbd2e]"></div>
                        <div class="h-2.5 w-2.5 rounded-full bg-[#27c93f]"></div>
                    </div>
                    <div class="ml-4 font-mono text-[0.65rem] font-medium tracking-wide text-[#858585]">examples.js</div>
                </div>
                <pre class="vscode-scrollbar overflow-x-auto p-4 font-mono text-[0.75rem] leading-loose text-[#d4d4d4]">
                            <code class="!bg-transparent !p-0 !border-0 !shadow-none !text-inherit">
                                <span class="text-[#c586c0]">import</span> <span class="text-[#9cdcfe]">ConsoleLogger</span> <span class="text-[#c586c0]">from</span> <span class="text-[#ce9178]">'@/Modules/shared/infrastructure/ConsoleLogger'</span>;
                                
                                <span class="text-[#6a9955]">// Example 1: API Response Logging</span>
                                <span class="text-[#569cd6]">async function</span> <span class="text-[#dcdcaa]">fetchUserData</span>(<span class="text-[#9cdcfe]">userId</span>) {
                                    <span class="text-[#c586c0]">try</span> {
                                        <span class="text-[#4ec9b0]">ConsoleLogger</span>.<span class="text-[#dcdcaa]">log</span>(<span class="text-[#ce9178]">'Fetching user:'</span>, <span class="text-[#9cdcfe]">userId</span>);
                                        <span class="text-[#569cd6]">const</span> <span class="text-[#9cdcfe]">response</span> = <span class="text-[#c586c0]">await</span> <span class="text-[#9cdcfe]">api</span>.<span class="text-[#dcdcaa]">get</span>(<span class="text-[#ce9178]">`/users/${</span><span class="text-[#9cdcfe]">userId</span><span class="text-[#ce9178]">}`</span>);
                                        <span class="text-[#4ec9b0]">ConsoleLogger</span>.<span class="text-[#dcdcaa]">info</span>(<span class="text-[#ce9178]">'User data received:'</span>, <span class="text-[#9cdcfe]">response</span>);
                                        <span class="text-[#c586c0]">return</span> <span class="text-[#9cdcfe]">response</span>;
                                    } <span class="text-[#c586c0]">catch</span> (<span class="text-[#9cdcfe]">error</span>) {
                                        <span class="text-[#4ec9b0]">ConsoleLogger</span>.<span class="text-[#dcdcaa]">error</span>(<span class="text-[#ce9178]">'Failed to fetch user:'</span>, <span class="text-[#9cdcfe]">error</span>);
                                        <span class="text-[#c586c0]">throw</span> <span class="text-[#9cdcfe]">error</span>;
                                    }
                                }

                                <span class="text-[#6a9955]">// Example 2: Performance Measurement</span>
                                <span class="text-[#569cd6]">function</span> <span class="text-[#dcdcaa]">processLargeDataset</span>(<span class="text-[#9cdcfe]">data</span>) {
                                    <span class="text-[#4ec9b0]">ConsoleLogger</span>.<span class="text-[#dcdcaa]">time</span>(<span class="text-[#ce9178]">'data-processing'</span>);
                                    <span class="text-[#6a9955]">// ... do work</span>
                                    <span class="text-[#4ec9b0]">ConsoleLogger</span>.<span class="text-[#dcdcaa]">timeEnd</span>(<span class="text-[#ce9178]">'data-processing'</span>);
                                }

                                <span class="text-[#6a9955]">// Example 3: Grouped Logs</span>
                                <span class="text-[#569cd6]">function</span> <span class="text-[#dcdcaa]">handleFormSubmission</span>(<span class="text-[#9cdcfe]">formData</span>) {
                                    <span class="text-[#4ec9b0]">ConsoleLogger</span>.<span class="text-[#dcdcaa]">group</span>(<span class="text-[#ce9178]">'Form Submission'</span>);
                                    <span class="text-[#4ec9b0]">ConsoleLogger</span>.<span class="text-[#dcdcaa]">log</span>(<span class="text-[#ce9178]">'Form values:'</span>, <span class="text-[#9cdcfe]">formData</span>);
                                    <span class="text-[#4ec9b0]">ConsoleLogger</span>.<span class="text-[#dcdcaa]">log</span>(<span class="text-[#ce9178]">'Validation passed'</span>);
                                    <span class="text-[#4ec9b0]">ConsoleLogger</span>.<span class="text-[#dcdcaa]">log</span>(<span class="text-[#ce9178]">'Sending to server...'</span>);
                                    <span class="text-[#4ec9b0]">ConsoleLogger</span>.<span class="text-[#dcdcaa]">groupEnd</span>();
                                }
                            </code>
                        </pre>
            </div>
        </div>

        <!-- Environment & Integration (Side-by-side on lg) -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Configuration -->
            <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-5 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/10">
                <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-amber-600 dark:text-amber-400">
                    <LuFingerprint class="h-3.5 w-3.5" />
                    Environment Detection
                </h4>
                <div class="space-y-3.5 text-xs font-medium text-slate-700 dark:text-slate-300">
                    <p>The logger automatically detects these environments:</p>
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span class="rounded-md border border-emerald-200 bg-emerald-100 px-2 py-0.5 text-[0.65rem] font-bold uppercase text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/20 dark:text-emerald-400">Enabled</span>
                            <span class="text-slate-600 dark:text-slate-400">'local', 'development'</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-md border border-slate-300 bg-slate-200 px-2 py-0.5 text-[0.65rem] font-bold uppercase text-slate-600 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300">Disabled</span>
                            <span class="text-slate-600 dark:text-slate-400">'staging', 'production', etc.</span>
                        </div>
                    </div>
                    <div class="border-t border-amber-200/50 pt-3 dark:border-amber-500/20">
                        <p class="mb-2">Environment is read from (in order):</p>
                        <ol class="ml-1 list-inside list-decimal space-y-1.5 text-slate-600 dark:text-slate-400">
                            <li>
                                <code class="mx-0.5 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">window.__APP_ENV__</code>
                            </li>
                            <li>
                                <code class="mx-0.5 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">VITE_APP_ENV</code>
                            </li>
                            <li>
                                <code class="mx-0.5 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">import.meta.env.MODE</code>
                            </li>
                            <li>
                                Defaults to:
                                <code class="mx-0.5 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-amber-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-amber-400">'production'</code>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- ApiService Integration -->
            <div class="flex flex-col rounded-xl border border-sky-100 bg-sky-50/50 p-5 shadow-sm dark:border-sky-500/20 dark:bg-sky-500/10">
                <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-sky-600 dark:text-sky-400">
                    <LuShieldCheck class="h-3.5 w-3.5" />
                    ApiService Integration
                </h4>
                <div class="flex-1 space-y-3 text-xs font-medium text-slate-700 dark:text-slate-300">
                    <p class="leading-relaxed">
                        The ConsoleLogger is already integrated into
                        <code class="mx-0.5 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-sky-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-sky-400">ApiService.ts</code>
                        for automatic API debugging.
                    </p>

                    <ul class="mt-2 space-y-2">
                        <li class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <LuCheckCircle2 class="h-3.5 w-3.5 shrink-0 text-sky-500" />
                            Requests log automatically
                        </li>
                        <li class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <LuCheckCircle2 class="h-3.5 w-3.5 shrink-0 text-sky-500" />
                            Request parameters are tracked
                        </li>
                        <li class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <LuCheckCircle2 class="h-3.5 w-3.5 shrink-0 text-sky-500" />
                            Response payload data is logged
                        </li>
                        <li class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <LuCheckCircle2 class="h-3.5 w-3.5 shrink-0 text-sky-500" />
                            Errors are caught with details
                        </li>
                    </ul>
                </div>
                <div class="mt-4 rounded-lg border border-sky-100 bg-white/60 p-3 text-center text-xs font-semibold text-sky-700 dark:border-sky-500/20 dark:bg-slate-900/50 dark:text-sky-300">No configuration needed—just use ApiService as normal!</div>
            </div>
        </div>

        <!-- File Locations & Benefits (Bottom Grid) -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- File Locations -->
            <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm md:col-span-1 dark:border-slate-700/60 dark:bg-slate-800/30">
                <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">
                    <LuFileCode2 class="h-3.5 w-3.5" />
                    Core Files
                </h4>
                <div class="space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                    <div>
                        <span class="mb-1.5 block text-slate-400 dark:text-slate-500">Implementation:</span>
                        <code class="block w-full break-all rounded-md border border-slate-200 bg-white px-2 py-1.5 font-mono text-[0.65rem] font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">resources/js/Modules/shared/infrastructure/ConsoleLogger.ts</code>
                    </div>
                    <div>
                        <span class="mb-1.5 block text-slate-400 dark:text-slate-500">Integrated in:</span>
                        <code class="block w-full break-all rounded-md border border-slate-200 bg-white px-2 py-1.5 font-mono text-[0.65rem] font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">resources/js/Modules/infrastructure/ApiService.ts</code>
                    </div>
                </div>
            </div>

            <!-- Benefits -->
            <div class="md:col-span-2">
                <h4 class="mb-3 ml-1 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase text-slate-500 dark:text-slate-400">
                    <LuZap class="h-3.5 w-3.5" />
                    Key Benefits
                </h4>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm transition-colors hover:border-indigo-300 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-indigo-600">
                        <LuZap class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" />
                        <div>
                            <span class="mb-0.5 block text-xs font-semibold text-slate-800 dark:text-slate-200">Zero Performance Impact</span>
                            <span class="text-[0.65rem] font-medium leading-relaxed text-slate-500 dark:text-slate-400">In production, logging calls are completely bypassed.</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm transition-colors hover:border-indigo-300 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-indigo-600">
                        <LuAlignLeft class="mt-0.5 h-4 w-4 shrink-0 text-indigo-500" />
                        <div>
                            <span class="mb-0.5 block text-xs font-semibold text-slate-800 dark:text-slate-200">Consistent Formatting</span>
                            <span class="text-[0.65rem] font-medium leading-relaxed text-slate-500 dark:text-slate-400">All logs include timestamps and level indicators.</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm transition-colors hover:border-indigo-300 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-indigo-600">
                        <LuMonitorSmartphone class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" />
                        <div>
                            <span class="mb-0.5 block text-xs font-semibold text-slate-800 dark:text-slate-200">Easy to Use</span>
                            <span class="text-[0.65rem] font-medium leading-relaxed text-slate-500 dark:text-slate-400">Simple import and method calls throughout the app.</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm transition-colors hover:border-indigo-300 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-indigo-600">
                        <LuSlidersHorizontal class="mt-0.5 h-4 w-4 shrink-0 text-purple-500" />
                        <div>
                            <span class="mb-0.5 block text-xs font-semibold text-slate-800 dark:text-slate-200">Flexible Control</span>
                            <span class="text-[0.65rem] font-medium leading-relaxed text-slate-500 dark:text-slate-400">Can manually enable/disable logging at runtime if needed.</span>
                        </div>
                    </div>
                </div>
            </div>
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
