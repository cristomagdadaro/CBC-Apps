<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import CRCMDatatable from "@/Components/CRCMDatatable/CRCMDatatable.vue";
import Transaction from "@/Modules/domain/Transaction";
import ActionHeaderLayout from "@/Layouts/ActionHeaderLayout.vue";

const props = defineProps({
    data: Object,
});

const item = props.data;
</script>

<template>
    <Head :title="`Transactions - ${item.name}`" />
    <AppLayout>
        <template #header>
            <ActionHeaderLayout
                :title="`Transactions for ${item.name}`"
                subtitle="View and track inventory movements specific to this item."
                :route-link="route('items.index')" />
        </template>
        <div class="space-y-6 px-3 sm:px-5 py-4 sm:py-6 max-w-7xl mx-auto">
            <section class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 sm:p-6 shadow-sm">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="space-y-4 flex-1">
                        <div>
                            <p class="text-[0.65rem] sm:text-xs font-semibold uppercase tracking-[0.2em] text-blue-700 dark:text-blue-400">Item Overview</p>
                            <h1 class="mt-2 text-xl sm:text-2xl font-semibold text-slate-900 dark:text-slate-100">
                                {{ item.name }}
                            </h1>
                            <p class="mt-1 text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                                {{ item.category?.name || "Uncategorized" }}
                            </p>
                        </div>

                        <div class="grid gap-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 md:grid-cols-2">
                            <div>
                                <span class="font-medium text-slate-900 dark:text-slate-100">Brand:</span>
                                {{ item.brand || "N/A" }}
                            </div>
                            <div>
                                <span class="font-medium text-slate-900 dark:text-slate-100">Supplier:</span>
                                {{ item.supplier?.name || "N/A" }}
                            </div>
                            <div class="md:col-span-2">
                                <span class="font-medium text-slate-900 dark:text-slate-100">Model / Description:</span>
                                <span
                                    class="ml-1 line-clamp-2"
                                    :title="item.description">
                                    {{ item.description || "N/A" }}
                                </span>
                            </div>
                            <div class="md:col-span-2">
                                <span class="font-medium text-slate-900 dark:text-slate-100 block mb-1">Specifications:</span>
                                <span class="whitespace-pre-wrap block p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
                                    {{ item.specifications || "N/A" }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="item.image"
                        class="w-full sm:w-[24rem] shrink-0">
                        <img
                            :src="item.image"
                            alt="Item Image"
                            class="rounded-xl object-cover w-full max-h-64 border border-slate-200 dark:border-slate-700 shadow-sm" />
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Transactions History</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">All incoming and outgoing transactions related to this item.</p>
                </div>
                <CRCMDatatable
                    :base-model="Transaction"
                    :params="{ filter_by_parent_column: 'item_id', filter_by_parent_id: item.id }"
                    :can-view="true"
                    :can-create="false"
                    :can-update="true"
                    :can-delete="true">
                    <template #cell-transac_type="{ value }">
                        <span
                            class="inline-flex rounded-full px-2.5 py-1 text-[0.65rem] font-bold uppercase tracking-wider"
                            :class="value === 'incoming' ? 'bg-green-200/60 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'">
                            {{ value }}
                        </span>
                    </template>
                    <template #cell-itemWithPrriCode="{ row }">
                        <div class="flex flex-col leading-tight gap-0.5">
                            <span class="font-semibold text-sm text-slate-800 dark:text-slate-100">
                                {{ row.item?.name || "Unknown Item" }}
                            </span>
                            <span
                                v-if="row.item?.brand"
                                class="text-[0.70rem] text-slate-500 dark:text-slate-400">
                                {{ row.item.brand }}
                            </span>
                            <span
                                v-if="row.barcode"
                                class="text-[0.70rem] text-slate-500 dark:text-slate-400">
                                {{ row.barcode }}
                            </span>
                        </div>
                    </template>
                    <template #cell-actorWithRemarks="{ row }">
                        <div class="flex flex-col">
                            <span class="font-semibold text-xs text-slate-700 dark:text-slate-300 uppercase tracking-wide">
                                {{ row.actor_display_name || "Unknown Actor" }}
                            </span>
                            <span
                                v-if="row.remarks"
                                class="text-xs text-slate-500 mt-0.5">
                                {{ row.remarks }}
                            </span>
                        </div>
                    </template>
                </CRCMDatatable>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped></style>
