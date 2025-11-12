<script>
import TransactionCard from "@/Pages/Inventory/Scan/components/TransactionCard.vue";
import { router } from '@inertiajs/vue3'

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
        stockLevelBackground (total, stock) {
            const t = Number.parseFloat(total);
            const s = Number.parseFloat(stock);
            let percent = 0;
            if (!isNaN(t) && t > 0 && !isNaN(s)) {
                percent = (s / t) * 100;
            }
            // Softer palette with better contrast against gray text
            if (percent >= 100) {
                return 'bg-white';
            } else if (percent >= 50) {
                return 'bg-amber-50';
            } else if (percent >= 25) {
                return 'bg-amber-100';
            } else if (percent > 0) {
                return 'bg-amber-200';
            } else {
                return 'bg-gray-100';
            }
        },
        formatNumber(value) {
            if (value)
                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            return 0;
        },
        showItem(id) {
            router.visit(route('items.show', id));
        }
    },
}
</script>

<template>
    <div v-if="formsData" class="flex gap-2 w-full">
        <div v-if="!!formsData" class="p-5 sm:grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 flex flex-col sm:gap-2 w-full gap-1">
            <div v-for="item in formsData" :key="item?.item_id ?? item?.id" class="flex flex-col border p-2 gap-1 rounded shadow w-full"
                 :class="stockLevelBackground(item?.total_ingoing, item?.remaining_quantity)"
            >
                <div class="flex gap-1 justify-between border-b p-1 select-none cursor-pointer duration-75 rounded hover:bg-gray-200"
                     @dblclick="showItem(item?.item_id)"
                     title="Double click to view item details"
                >
                    <span class="font-bold">{{ item?.name }}</span>
                    <span class="text-gray-600">({{ item?.unit }} | {{ item?.brand }})</span>
                </div>
                <div class="flex justify-between p-1 select-none">
                    <div class="flex flex-col text-center">
                        <span class="text-md text-gray-800 font-bold text-lg">{{ formatNumber(item?.total_ingoing) }}</span>
                        <span class="text-xs text-gray-600">Total Stock</span>
                    </div>
                    <div class="flex flex-col text-center">
                        <span class="text-md text-gray-800 font-bold text-lg">{{ formatNumber(item?.total_outgoing) }}</span>
                        <span class="text-xs text-gray-600">Total Consumed</span>
                    </div>
                    <div class="flex flex-col text-center">
                        <span class="text-md text-gray-800 font-bold text-lg">{{ formatNumber(item?.remaining_quantity) }}</span>
                        <span class="text-xs text-gray-600">Remaining</span>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="w-full text-center text-gray-500 select-none">No data found</div>

<!--        <transaction-card
            v-for="data in formsData"
            :key="data.id"
            :data="data"
            @deletedModel="handleDelete"
        />-->
    </div>
</template>

<style scoped>

</style>
