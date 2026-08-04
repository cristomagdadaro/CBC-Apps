<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CRCMDatatable from '@/Components/CRCMDatatable/CRCMDatatable.vue';
import Transaction from '@/Modules/domain/Transaction';

const props = defineProps({
    data: Object,
});

const item = props.data;
</script>

<template>
    <Head :title="`Transactions - ${item.name}`" />
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <div class="flex gap-2 items-center">
                    <Link :href="route('inventory.items.index')" class="p-2 -ml-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Transactions for {{ item.name }}
                    </h2>
                </div>
            </div>
        </template>
        <div class="py-12 max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <div class="p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg flex flex-col sm:flex-row gap-6">
                <div class="w-full sm:w-1/3" v-if="item.image">
                    <img :src="item.image" alt="Item Image" class="rounded object-cover w-full max-h-64">
                </div>
                <div class="w-full" :class="{ 'sm:w-2/3': item.image }">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ item.name }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700 dark:text-gray-300">
                        <div>
                            <p class="font-semibold text-gray-500 dark:text-gray-400">Brand</p>
                            <p>{{ item.brand || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-500 dark:text-gray-400">Model / Description</p>
                            <p>{{ item.description || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-500 dark:text-gray-400">Category</p>
                            <p>{{ item.category?.name || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-500 dark:text-gray-400">Supplier</p>
                            <p>{{ item.supplier?.name || 'N/A' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="font-semibold text-gray-500 dark:text-gray-400">Specifications</p>
                            <p class="whitespace-pre-wrap">{{ item.specifications || 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
                <CRCMDatatable
                    :base-model="Transaction"
                    :params="{ filter_by_parent_column: 'item_id', filter_by_parent_id: item.id }"
                    :can-view="true"
                    :can-create="false"
                    :can-update="true"
                    :can-delete="true"
                />
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
</style>
