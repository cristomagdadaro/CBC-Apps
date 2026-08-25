<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";

export default {
    name: "OutgoingItemCard",
    mixins: [ApiMixin, DataFormatterMixin],
    props: {
        outgoingFromApi: {
            type: Object,
            required: true,
        },
    },
    methods: {
        selectItem(item) {
            this.$emit("select-item", item);
        },
    },
    data() {
        return {
            randomIndex: null,
        };
    },
    watch: {
        outgoingFromApi: {
            immediate: true,
            handler(val) {
                if (val?.data?.length && this.randomIndex === null) {
                    const limit = Math.min(20, val.data.length);
                    this.randomIndex = Math.floor(Math.random() * limit);
                }
            },
        },
    },
};
</script>

<template>
    <div
        v-if="outgoingFromApi && Array.isArray(outgoingFromApi.data) && outgoingFromApi.data.length > 0"
        class="grid w-full grid-cols-1 gap-2 text-slate-900 sm:grid-cols-2 sm:gap-3 md:grid-cols-3 dark:text-slate-100">
        <div
            v-for="(item, index) in outgoingFromApi.data"
            :key="`${item.item_id || item.id}-${item.unit}-${item.barcode || 'nobarcode'}-${index}`"
            @click="selectItem(item)"
            class="shadow-xs active:scale-98 group flex cursor-pointer flex-col rounded-xl border border-slate-200 bg-white p-3 transition-all duration-200 hover:border-lime-500/50 hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
            :data-guide="index === randomIndex ? 'supplies-sample-item' : null">
            <div class="flex h-full items-start justify-between gap-3">
                <div class="flex min-w-0 flex-col space-y-1">
                    <!-- Item name -->
                    <span class="truncate text-xs font-bold text-slate-900 sm:text-sm dark:text-slate-100">
                        {{ item.name }}
                        <span
                            v-if="item.description"
                            class="font-normal text-slate-500 dark:text-slate-400">
                            ({{ item.description }})
                        </span>
                    </span>

                    <!-- Expiration -->
                    <span
                        v-if="item.expiration"
                        class="inline-flex items-center gap-1 text-[0.7rem] font-semibold sm:text-xs"
                        :class="{
                            'text-rose-600 dark:text-rose-400': getExpirationStatus(item.expiration) === 'expired',
                            'text-amber-600 dark:text-amber-400': ['expiring_soon', 'expiring_today'].includes(getExpirationStatus(item.expiration)),
                            'text-slate-500 dark:text-slate-400': !getExpirationStatus(item.expiration),
                        }">
                        Exp: {{ formatDate(item.expiration) }}
                        <span v-if="getExpirationStatus(item.expiration) === 'expired'">(Expired)</span>
                        <span v-else-if="getExpirationStatus(item.expiration) === 'expiring_today'">(Expires Today)</span>
                        <span v-else-if="getExpirationStatus(item.expiration) === 'expiring_soon'">(Expiring Soon)</span>
                    </span>

                    <!-- Brand -->
                    <span
                        v-if="item.brand"
                        class="text-[0.7rem] text-slate-500 dark:text-slate-400">
                        {{ item.brand }}
                    </span>

                    <!-- Barcode -->
                    <span
                        class="font-mono text-[0.7rem] tracking-tight"
                        :class="item.barcode ? 'text-slate-500 dark:text-slate-400' : 'font-bold text-rose-500'">
                        {{ item.barcode || "NO BARCODE" }}
                    </span>
                </div>

                <!-- Quantity badge -->
                <div class="shrink-0 text-right">
                    <span class="rounded-lg border border-slate-200 bg-slate-100 px-2 py-1 text-xs font-extrabold text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ formatNumber(item.remaining_quantity) }} {{ item.unit || "" }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
