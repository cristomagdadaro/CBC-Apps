<script>
import {
    Package,
    Link2,
    Hash,
    Calendar,
    User,
    FileText,
    AlertCircle,
    ChevronRight,
    Box
} from 'lucide-vue-next';

export default {
    name: 'TransactionComponentAccordion',
    components: {
        Package,
        Link2,
        Hash,
        Calendar,
        User,
        FileText,
        AlertCircle,
        ChevronRight,
        Box,
    },
    props: {
        components: {
            type: Array,
            default: () => [],
        },
        title: {
            type: String,
            default: 'Attached Components',
        },
        emptyMessage: {
            type: String,
            default: 'No components linked to this transaction yet.',
        },
    },
    computed: {
        hasComponents() {
            return Array.isArray(this.components) && this.components.length > 0;
        },
        totalQuantity() {
            if (!this.hasComponents) return 0;
            return this.components.reduce((sum, c) => sum + (parseFloat(c.quantity) || 0), 0);
        },
    },
};
</script>

<template>
    <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden bg-white dark:bg-slate-900 shadow-xs">
        <!-- Header -->
        <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-800/80 px-4 py-3 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <Box class="w-4 h-4 text-slate-500 dark:text-slate-400" />
                <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">{{ title }}</h3>
            </div>
            <div class="flex items-center gap-2">
                <span v-if="hasComponents" class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                    {{ components.length }} items · {{ totalQuantity }} total qty
                </span>
                <span v-else class="text-xs font-semibold text-slate-500 dark:text-slate-400">0 items</span>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!hasComponents" class="p-6 text-center">
            <Package class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600 mb-2" />
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">{{ emptyMessage }}</p>
        </div>

        <!-- Component List -->
        <div v-else class="divide-y divide-slate-100 dark:divide-slate-800">
            <div
                v-for="(component, index) in components"
                :key="component.id ?? index"
                class="p-4 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors duration-150"
            >
                <!-- Header Row -->
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <Package class="w-4 h-4 text-lime-600 dark:text-lime-400 flex-shrink-0" />
                            <span class="font-bold text-xs sm:text-sm text-slate-900 dark:text-slate-100 truncate">
                                {{ component?.item?.brand }} {{ component?.item?.description }}
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 ml-6">
                            <span v-if="component?.item?.name" class="flex items-center gap-1 px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded-lg text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-medium">
                                {{ component.item.name }}
                            </span>
                            <span v-if="component?.barcode" class="flex items-center gap-1 px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded-lg text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-mono">
                                <Hash class="w-3 h-3 text-slate-400" />
                                {{ component.barcode }}
                            </span>
                            <span v-if="component?.barcode_prri" class="flex items-center gap-1 px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded-lg text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-mono">
                                <Hash class="w-3 h-3 text-slate-400" />
                                {{ component.barcode_prri }}
                            </span>
                            <span v-if="component?.quantity" class="flex items-center gap-1 px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded-lg text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-bold">
                                <span class="text-slate-400 font-normal">Qty:</span>
                                {{ component.quantity }}
                            </span>
                        </div>
                    </div>
                    <Link
                        :href="route('transactions.show', component.id)"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 transition-colors shrink-0"
                    >
                        <Link2 class="w-3 h-3" />
                        View
                        <ChevronRight class="w-3 h-3" />
                    </Link>
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-xs ml-6">
                    <!-- Expiry -->
                    <div v-if="component.expiration" class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                        <Calendar class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" />
                        <span class="text-slate-400">Exp:</span>
                        <span :class="{
                            'text-rose-600 dark:text-rose-400 font-bold': component.expiration && new Date(component.expiration) < new Date(),
                            'text-slate-700 dark:text-slate-200 font-medium': !component.expiration || new Date(component.expiration) >= new Date()
                        }">
                            {{ component.expiration ?? '—' }}
                        </span>
                    </div>

                    <!-- Project Code -->
                    <div v-if="component.project_code" class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                        <FileText class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" />
                        <span class="text-slate-400">Charge:</span>
                        <span class="font-mono font-medium">{{ component.project_code ?? '—' }}</span>
                    </div>
                </div>

                <!-- Remarks -->
                <div v-if="component.remarks" class="ml-6 mt-2 p-2.5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 rounded-xl">
                    <div class="flex items-start gap-1.5">
                        <AlertCircle class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" />
                        <div class="text-xs text-amber-900 dark:text-amber-200">
                            <span class="font-bold">Remarks:</span> {{ component.remarks }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
</style>
