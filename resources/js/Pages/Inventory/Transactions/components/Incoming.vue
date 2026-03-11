<script>
import IncomingForm from "@/Pages/Inventory/Transactions/components/IncomingForm.vue";
import ItemForm from "@/Pages/Inventory/Items/components/ItemForm.vue";
import TransactionHeaderAction from "@/Pages/Inventory/Transactions/components/TransactionHeaderAction.vue";

export default {
    name: "Incoming",
    props: {
        data: {
            type: Object,
            default: null,
        },
        attachedReports: {
            type: Array,
            default: () => [],
        },
    },
    components: {
        IncomingForm,
        ItemForm,
        TransactionHeaderAction,
    },
    computed: {
        isUpdate() {
            return !!this.data?.id;
        },
    },
    data() {
        return {
            showNewItemForm: false,
        }
    },
}
</script>

<template>
    <app-layout :title="isUpdate ? 'Update Transaction Details' : 'Incoming Transaction Details'">
        <template v-slot:header>
            <transaction-header-action />
        </template>

        <div class="flex">
            <incoming-form
                :data="data"
                :attached-reports="attachedReports"
                @showNewItemForm="showNewItemForm = $event"
            />
            <transition-container
                type="pop-in"
            >
                    <item-form v-if="showNewItemForm" @close="showNewItemForm = false" />
            </transition-container>
                
        </div>
    </app-layout>
</template>

<style scoped>

</style>
