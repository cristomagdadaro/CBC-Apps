<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import Transaction from "@/Pages/Inventory/Scan/components/model/Transaction";

export default {
    name: "TransactionCard",
    mixins: [ApiMixin, DataFormatterMixin],
    beforeMount() {
        this.model = new Transaction();
    },
}
</script>

<template>
    <div class="flex flex-col gap-2 border p-3 rounded-md h-fit" :class="data.transac_type === 'incoming' ? 'bg-green-300':'bg-red-300'">
        <div class="flex justify-between">
            <div class="flex flex-col leading-none gap-0.5">
                <span class="font-bold text-xl">{{ data.item.name }}</span>
                <div>
                    <span class="text-sm border-r border-gray-800 mr-1 pr-1">{{ data.item.brand }}</span>
                    <span class="text-sm">{{ data.barcode }}</span>
                </div>
            </div>
            <div class="flex flex-col leading-tight text-right">
                <span class="font-bold text-xl">{{ data.quantity }}{{data.unit}}</span>
                <span class="text-sm uppercase">{{ data.transac_type }}</span>
            </div>
        </div>
        <div class="flex justify-between border-t border-gray-800 pt-1 ">
            <div class="flex flex-col leading-none">

            </div>
            <div class="flex flex-col leading-none text-right">
                <span class="text-sm font-bold">{{ data.created_at }}</span>
                <span class="text-xs">Transaction Date</span>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
