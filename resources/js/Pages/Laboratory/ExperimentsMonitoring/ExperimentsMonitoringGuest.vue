<template>
    <Head title="Experiment Monitoring" />
    <guest-form-page
        title="Experiment Monitoring"
        subtitle="Monitor and track status of experiments in the laboratory"
        guide-key="experiment-monitoring-guest"
        :max-width="'max-w-2xl'"
    >
        <div data-guide="experiment-monitoring-content" class="flex flex-col gap-6 sm:gap-8">
            <div class="flex flex-col gap-4 sm:gap-5">
                
                <div class="flex flex-col gap-1.5 px-1">
                    <h2 class="text-base sm:text-lg font-bold text-slate-800 dark:text-slate-100">
                        Ongoing Experiments
                    </h2>
                    <p class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-400 leading-relaxed">
                        View and monitor the real-time status of ongoing experiments in the laboratory.
                    </p>
                </div>
                
                <div class="flex flex-col items-center justify-center p-8 sm:p-12 text-center border-2 border-dashed border-gray-300 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-800/30 rounded-2xl shadow-sm transition-colors duration-300">
                    <div class="p-3.5 bg-white dark:bg-slate-800 rounded-full mb-4 shadow-sm border border-gray-200 dark:border-slate-700">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-gray-400 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                        Module Under Development
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto leading-relaxed">
                        The real-time experiment monitoring dashboard is currently being built. Please check back soon for updates.
                    </p>
                </div>
                
            </div>
        </div>
    </guest-form-page>
</template>