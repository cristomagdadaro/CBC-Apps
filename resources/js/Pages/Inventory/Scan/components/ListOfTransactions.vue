<script>
import TransactionCard from "@/Pages/Inventory/Scan/components/TransactionCard.vue";

export default {
    name: "ListOfTransactions",
    components: {TransactionCard},
    props: {
        formsData: Array,
    },
    methods: {
        handleDelete(deletedModel) {
            this.$emit("removeModel", deletedModel);
        },
    },
}
</script>

<template>
    <div class="grid lg:grid-cols-2 grid-cols-1 gap-2 p-2">
        <transaction-card
            v-for="data in formsData"
            :key="data.id"
            :data="data"
            @deletedModel="handleDelete"
        />
    </div>
</template>

<style scoped>

</style>
