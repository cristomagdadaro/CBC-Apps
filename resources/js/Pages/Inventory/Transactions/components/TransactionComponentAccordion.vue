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
    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden bg-white dark:bg-gray-800 shadow-sm">
        <!-- Header -->
        <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <Box class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ title }}</h3>
            </div>
            <div class="flex items-center gap-2">
                <span v-if="hasComponents" class="text-xs text-gray-500 dark:text-gray-400">
                    {{ components.length }} items · {{ totalQuantity }} total qty
                </span>
                <span v-else class="text-xs text-gray-500 dark:text-gray-400">0 items</span>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!hasComponents" class="p-6 text-center">
            <Package class="w-8 h-8 mx-auto text-gray-300 dark:text-gray-600 mb-2" />
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ emptyMessage }}</p>
        </div>

        <!-- Component List -->
        <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
            <div
                v-for="(component, index) in components"
                :key="component.id ?? index"
                class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150"
            >
                <!-- Header Row -->
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <Package class="w-4 h-4 text-AA flex-shrink-0" />
                            <span class="font-medium text-gray-900 dark:text-gray-100 truncate">
                                {{ component?.item?.brand }} {{ component?.item?.description }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400 ml-6">
                            <span v-if="component?.item?.name" class="flex items-center gap-1 px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-gray-600 dark:text-gray-300">
                                {{ component.item.name }}
                            </span>
                            <span v-if="component?.barcode" class="flex items-center gap-1 px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-gray-600 dark:text-gray-300">
                                <Hash class="w-3 h-3 text-gray-400 dark:text-gray-500" />
                                {{ component.barcode }}
                            </span>
                            <span v-if="component?.barcode_prri" class="flex items-center gap-1 px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-gray-600 dark:text-gray-300">
                                <Hash class="w-3 h-3 text-gray-400 dark:text-gray-500" />
                                {{ component.barcode_prri }}
                            </span>
                            <span v-if="component?.quantity" class="flex items-center gap-1 px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-gray-600 dark:text-gray-300">
                                <span class="text-gray-500 dark:text-gray-400">Qty:</span>
                                {{ component.quantity }}
                            </span>
                        </div>
                    </div>
                    <Link
                        :href="route('transactions.show', component.id)"
                        class="flex items-center gap-1 text-xs text-AA hover:text-AA-dark underline-offset-2 hover:underline whitespace-nowrap flex-shrink-0 transition-colors"
                    >
                        <Link2 class="w-3 h-3" />
                        View
                        <ChevronRight class="w-3 h-3" />
                    </Link>
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-xs ml-6">
                    <!-- Quantity -->
                    <div v-if="component.quantity" class="hidden flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                        <span class="text-gray-500 dark:text-gray-400">Qty:</span>
                        <span class="font-medium">{{ component.quantity }}</span>
                        <span v-if="component.unit" class="text-gray-500">{{ component.unit }}</span>
                    </div>

                    <!-- Accountable -->
                    <div v-if="component.actor_display_name" class="hidden flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                        <User class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500" />:
                        <span class="truncate">{{ component.actor_display_name ?? '—' }}</span>
                    </div>

                    <!-- Expiry -->
                    <div v-if="component.expiration" class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                        <Calendar class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500" />
                        <span class="text-gray-500 dark:text-gray-400">Exp:</span>
                        <span :class="{
                            'text-red-600 dark:text-red-400 font-medium': component.expiration && new Date(component.expiration) < new Date(),
                            'text-gray-700 dark:text-gray-200': !component.expiration || new Date(component.expiration) >= new Date()
                        }">
                            {{ component.expiration ?? '—' }}
                        </span>
                    </div>

                    <!-- Project Code -->
                    <div v-if="component.project_code" class="hidden flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                        <FileText class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500" />
                        <span class="text-gray-500 dark:text-gray-400">Charge:</span>
                        <span class="font-mono">{{ component.project_code ?? '—' }}</span>
                    </div>
                </div>

                <!-- Remarks -->
                <div v-if="component.remarks" class="ml-6 p-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 rounded-md">
                    <div class="flex items-start gap-1.5">
                        <AlertCircle class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" />
                        <div class="text-xs text-amber-800 dark:text-amber-200">
                            <span class="font-medium">Remarks:</span> {{ component.remarks }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
</style>
