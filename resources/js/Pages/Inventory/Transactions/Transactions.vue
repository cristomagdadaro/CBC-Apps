<script setup>
import Transaction from '@/Modules/domain/Transaction';
import TransactionHeaderAction from '@/Pages/Inventory/Transactions/components/TransactionHeaderAction.vue';

const transactionTypeOptions = [
    { name: 'incoming', label: 'Incoming' },
    { name: 'outgoing', label: 'Outgoing' },
];
</script>

<template>

    <Head title="Transactions" />
    <AppLayout>
        <template #header>
            <TransactionHeaderAction />
        </template>
        <CRCMDatatable :base-model="Transaction" :can-view="true" :can-create="false" :can-update="true"
            :can-delete="true">
            <!-- Custom Filters -->
            <template #custom-filters="{ datatable, refresh }">
                <custom-dropdown :options="transactionTypeOptions" label="Filter by Type" placeholder="Select type"
                    :value="datatable.request.getParam('transac_type')" :with-all-option="false"
                    @selectedChange="(value) => { datatable.request.updateParam('transac_type', value); refresh(); }">
                    <template #icon>
                        <lu-filter class="w-4 h-4 text-gray-400" />
                    </template>
                </custom-dropdown>
            </template>
            <!-- Custom Cell Rendering -->
            <template #cell-transac_type="{ value }">
                <span class="px-4 py-1.5 rounded-full text-xs font-medium leading-none uppercase" :class="{
                    'bg-green-300 text-green-700': value === 'incoming',
                    'bg-red-300 text-red-700': value === 'outgoing'
                }">
                    {{ value === 'incoming' ? 'Incoming' : value === 'outgoing' ? 'Outgoing' : value }}
                </span>
            </template>
        </CRCMDatatable>
    </AppLayout>
</template>