<script setup>
import SearchComp from '@/Components/Search/SearchComp.vue';
import DataTable from '@/Modules/DataTable/presentation/DataTable.vue';
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

    <div class="default-container pt-5">
      <SearchComp
        :prop-model="Transaction"
        :card-slot="DataTable"
        :quick-filter-options="transactionTypeOptions"
        quick-filter-column="transac_type"
        quick-filter-label="Type"
        enable-export
      />
    </div>
  </AppLayout>
</template>