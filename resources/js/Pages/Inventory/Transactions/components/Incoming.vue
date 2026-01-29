<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import IncomingForm from "@/Pages/Inventory/Transactions/components/IncomingForm.vue";
import CreateItemForm from "@/Pages/Inventory/Items/components/CreateItemForm.vue";
import TransactionHeaderAction from "@/Pages/Inventory/Transactions/components/TransactionHeaderAction.vue";

export default {
    name: "Incoming",
    components: {
        AppLayout,
        IncomingForm,
        CreateItemForm,
        TransactionHeaderAction,
    },
    data() {
        return {
            showNewItemForm: false,
        }
    },
}
</script>

<template>
    <app-layout title="Incoming Transaction Details">
        <template v-slot:header>
            <transaction-header-action />
        </template>

        <div class="flex">
            <incoming-form @showNewItemForm="showNewItemForm = $event"  />
            <create-item-form v-if="showNewItemForm" />
        </div>
    </app-layout>
</template>

<style scoped>

</style>
