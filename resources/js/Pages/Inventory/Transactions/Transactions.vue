<script setup>
import CRCMDatatable from '@/Components/CRCMDatatable/CRCMDatatable.vue';
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
        <CRCMDatatable :base-model="Transaction" :can-view="true" :can-create="false" :can-update="true" :can-delete="true" />
    </AppLayout>
</template>