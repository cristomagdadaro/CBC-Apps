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
    <div class="bg-white flex select-none justify-between items-center gap-5 py-2 px-4">
        <div class="flex flex-col">
                <span class="font-bold text-xs whitespace-nowrap overflow-ellipsis overflow-hidden">
                    {{ data.name }} {{ data.description ? `(${data.description})` : '' }}
                </span>
            <span class="text-xs text-gray-500">{{ data.barcode }}</span>
            <span class="text-xs text-gray-500">{{ data.brand }}</span>
        </div>
        <span class="text-right">{{ formatNumber(data.remaining_quantity) }}</span>
    </div>
</template>

<style scoped>

</style>
