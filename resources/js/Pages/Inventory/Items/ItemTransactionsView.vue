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
                :title="`${item.name} ${item?.description} (${item?.brand})`"
                :subtitle="item.supplier?.name ? `Supplier: ${item.supplier.name}` : 'N/A'"
                :route-link="route('items.index')" />
        </template>
        <div class="flex w-full space-x-6 px-3 py-4 sm:px-5 sm:py-6">
            <section class="w-full rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
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
                            class="inline-flex rounded-full px-2.5 py-1 text-[0.65rem] font-bold uppercase"
                            :class="value === 'incoming' ? 'bg-green-200/60 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'">
                            {{ value }}
                        </span>
                    </template>
                    <template #cell-itemWithPrriCode="{ row }">
                        <div class="flex flex-col gap-0.5 leading-tight">
                            <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">
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
                            <span class="text-xs font-semibold uppercase text-slate-700 dark:text-slate-300">
                                {{ row.actor_display_name || "Unknown Actor" }}
                            </span>
                            <span
                                v-if="row.remarks"
                                class="mt-0.5 text-xs text-slate-500">
                                {{ row.remarks }}
                            </span>
                        </div>
                    </template>
                </CRCMDatatable>
            </section>
            <section
                v-if="item.specifications"
                class="w-1/4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="flex-1 space-y-4">
                        <div class="grid gap-3 text-xs text-slate-600 sm:text-sm md:grid-cols-2 dark:text-slate-300">
                            <div class="md:col-span-2">
                                <span class="mb-1 block font-medium text-slate-900 dark:text-slate-100">Specifications:</span>
                                <span class="block whitespace-pre-wrap rounded-xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-800/50">
                                    {{ item.specifications || "N/A" }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="item.image"
                        class="w-full shrink-0 sm:w-[24rem]">
                        <img
                            :src="item.image"
                            alt="Item Image"
                            class="max-h-64 w-full rounded-xl border border-slate-200 object-cover shadow-sm dark:border-slate-700" />
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped></style>
