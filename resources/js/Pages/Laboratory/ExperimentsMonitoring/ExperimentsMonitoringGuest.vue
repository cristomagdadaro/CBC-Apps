<template>
    <Head title="Experiment Monitoring" />
    <guest-form-page
        title="Experiment Monitoring"
        subtitle="Monitor and track status of experiments in the laboratory"
        guide-key="experiment-monitoring-guest"
        :max-width="'max-w-2xl'">
        <div
            data-guide="experiment-monitoring-content"
            class="flex flex-col gap-6 sm:gap-8">
            <div class="flex flex-col gap-4 sm:gap-5">
                <div class="flex flex-col gap-1.5 px-1">
                    <h2 class="text-base font-bold text-slate-800 sm:text-lg dark:text-slate-100">Ongoing Experiments</h2>
                    <p class="text-xs font-medium leading-relaxed text-slate-600 sm:text-sm dark:text-slate-400">View and monitor the real-time status of ongoing experiments in the laboratory.</p>
                </div>

                <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50/50 p-8 text-center shadow-sm transition-colors duration-300 sm:p-12 dark:border-slate-700 dark:bg-slate-800/30">
                    <div class="mb-4 rounded-full border border-gray-200 bg-white p-3.5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                        <svg
                            class="h-6 w-6 text-gray-400 sm:h-8 sm:w-8 dark:text-slate-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <h3 class="mb-1.5 text-sm font-bold text-slate-700 sm:text-base dark:text-slate-200">Module Under Development</h3>
                    <p class="mx-auto max-w-sm text-xs leading-relaxed text-slate-500 sm:text-sm dark:text-slate-400">The real-time experiment monitoring dashboard is currently being built. Please check back soon for updates.</p>
                </div>
            </div>
        </div>
    </guest-form-page>
</template>
