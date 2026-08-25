<script>
import { Package, Link2, Hash, Calendar, User, FileText, AlertCircle, ChevronRight, Box } from "lucide-vue-next";

export default {
    name: "TransactionComponentAccordion",
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
            default: "Attached Components",
        },
        emptyMessage: {
            type: String,
            default: "No components linked to this transaction yet.",
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
    <div class="shadow-xs overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-800/80">
            <div class="flex items-center gap-2">
                <Box class="h-4 w-4 text-slate-500 dark:text-slate-400" />
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 sm:text-sm dark:text-slate-200">
                    {{ title }}
                </h3>
            </div>
            <div class="flex items-center gap-2">
                <span
                    v-if="hasComponents"
                    class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                    {{ components.length }} items · {{ totalQuantity }} total qty
                </span>
                <span
                    v-else
                    class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                    0 items
                </span>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-if="!hasComponents"
            class="p-6 text-center">
            <Package class="mx-auto mb-2 h-8 w-8 text-slate-300 dark:text-slate-600" />
            <p class="text-xs text-slate-500 sm:text-sm dark:text-slate-400">{{ emptyMessage }}</p>
        </div>

        <!-- Component List -->
        <div
            v-else
            class="divide-y divide-slate-100 dark:divide-slate-800">
            <div
                v-for="(component, index) in components"
                :key="component.id ?? index"
                class="p-4 transition-colors duration-150 hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                <!-- Header Row -->
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="mb-1 flex items-center gap-2">
                            <Package class="h-4 w-4 flex-shrink-0 text-lime-600 dark:text-lime-400" />
                            <span class="truncate text-xs font-bold text-slate-900 sm:text-sm dark:text-slate-100">{{ component?.item?.brand }} {{ component?.item?.description }}</span>
                        </div>
                        <div class="ml-6 flex flex-wrap items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                            <span
                                v-if="component?.item?.name"
                                class="flex items-center gap-1 rounded-lg border border-slate-200 bg-slate-100 px-2 py-0.5 font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                {{ component.item.name }}
                            </span>
                            <span
                                v-if="component?.barcode"
                                class="flex items-center gap-1 rounded-lg border border-slate-200 bg-slate-100 px-2 py-0.5 font-mono text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                <Hash class="h-3 w-3 text-slate-400" />
                                {{ component.barcode }}
                            </span>
                            <span
                                v-if="component?.barcode_prri"
                                class="flex items-center gap-1 rounded-lg border border-slate-200 bg-slate-100 px-2 py-0.5 font-mono text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                <Hash class="h-3 w-3 text-slate-400" />
                                {{ component.barcode_prri }}
                            </span>
                            <span
                                v-if="component?.quantity"
                                class="flex items-center gap-1 rounded-lg border border-slate-200 bg-slate-100 px-2 py-0.5 font-bold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                <span class="font-normal text-slate-400">Qty:</span>
                                {{ component.quantity }}
                            </span>
                        </div>
                    </div>
                    <Link
                        :href="route('transactions.show', component.id)"
                        class="inline-flex shrink-0 items-center gap-1 rounded-lg border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                        <Link2 class="h-3 w-3" />
                        View
                        <ChevronRight class="h-3 w-3" />
                    </Link>
                </div>

                <!-- Details Grid -->
                <div class="ml-6 grid grid-cols-2 gap-3 text-xs md:grid-cols-3">
                    <!-- Expiry -->
                    <div
                        v-if="component.expiration"
                        class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                        <Calendar class="h-3.5 w-3.5 text-slate-400 dark:text-slate-500" />
                        <span class="text-slate-400">Exp:</span>
                        <span
                            :class="{
                                'font-bold text-rose-600 dark:text-rose-400': component.expiration && new Date(component.expiration) < new Date(),
                                'font-medium text-slate-700 dark:text-slate-200': !component.expiration || new Date(component.expiration) >= new Date(),
                            }">
                            {{ component.expiration ?? "—" }}
                        </span>
                    </div>

                    <!-- Project Code -->
                    <div
                        v-if="component.project_code"
                        class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                        <FileText class="h-3.5 w-3.5 text-slate-400 dark:text-slate-500" />
                        <span class="text-slate-400">Charge:</span>
                        <span class="font-mono font-medium">
                            {{ component.project_code ?? "—" }}
                        </span>
                    </div>
                </div>

                <!-- Remarks -->
                <div
                    v-if="component.remarks"
                    class="ml-6 mt-2 rounded-xl border border-amber-200 bg-amber-50 p-2.5 dark:border-amber-900/60 dark:bg-amber-950/40">
                    <div class="flex items-start gap-1.5">
                        <AlertCircle class="mt-0.5 h-3.5 w-3.5 flex-shrink-0 text-amber-600 dark:text-amber-400" />
                        <div class="text-xs text-amber-900 dark:text-amber-200">
                            <span class="font-bold">Remarks:</span>
                            {{ component.remarks }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
