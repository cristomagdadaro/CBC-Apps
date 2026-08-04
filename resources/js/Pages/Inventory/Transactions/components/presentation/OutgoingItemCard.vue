<script>
import ApiMixin from '@/Modules/mixins/ApiMixin';
import DataFormatterMixin from '@/Modules/mixins/DataFormatterMixin';

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
            this.$emit('select-item', item);
        },
    },
    data() {
        return {
            randomIndex: null
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
            }
        }
    }
};
</script>

<template>
    <div
        v-if="
            outgoingFromApi &&
            Array.isArray(outgoingFromApi.data) &&
            outgoingFromApi.data.length > 0
        "
        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 sm:gap-3 w-full text-slate-900 dark:text-slate-100"
    >
        <div
            v-for="(item, index) in outgoingFromApi.data"
            :key="`${item.item_id || item.id}-${item.unit}-${item.barcode || 'nobarcode'}-${index}`"
            @click="selectItem(item)"
            class="flex flex-col bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 shadow-xs hover:border-lime-500/50 hover:shadow-md cursor-pointer transition-all duration-200 active:scale-98 group"
            :data-guide="index === randomIndex ? 'supplies-sample-item' : null"
        >
            <div class="flex justify-between items-start gap-3 h-full">
                <div class="flex flex-col min-w-0 space-y-1">
                    <!-- Item name -->
                    <span class="font-bold text-xs sm:text-sm text-slate-900 dark:text-slate-100 truncate">
                        {{ item.name }}
                        <span v-if="item.description" class="text-slate-500 dark:text-slate-400 font-normal">({{ item.description }})</span>
                    </span>

                    <!-- Expiration -->
                    <span
                        v-if="item.expiration"
                        class="text-[0.7rem] sm:text-xs font-semibold inline-flex items-center gap-1"
                        :class="{
                            'text-rose-600 dark:text-rose-400': getExpirationStatus(item.expiration) === 'expired',
                            'text-amber-600 dark:text-amber-400': ['expiring_soon', 'expiring_today'].includes(getExpirationStatus(item.expiration)),
                            'text-slate-500 dark:text-slate-400': !getExpirationStatus(item.expiration),
                        }"
                    >
                        Exp: {{ formatDate(item.expiration) }}
                        <span v-if="getExpirationStatus(item.expiration) === 'expired'">(Expired)</span>
                        <span v-else-if="getExpirationStatus(item.expiration) === 'expiring_today'">(Expires Today)</span>
                        <span v-else-if="getExpirationStatus(item.expiration) === 'expiring_soon'">(Expiring Soon)</span>
                    </span>

                    <!-- Brand -->
                    <span v-if="item.brand" class="text-[0.7rem] text-slate-500 dark:text-slate-400">
                        {{ item.brand }}
                    </span>

                    <!-- Barcode -->
                    <span
                        class="text-[0.7rem] font-mono tracking-tight"
                        :class="item.barcode ? 'text-slate-500 dark:text-slate-400' : 'text-rose-500 font-bold'"
                    >
                        {{ item.barcode || "NO BARCODE" }}
                    </span>
                </div>

                <!-- Quantity badge -->
                <div class="text-right shrink-0">
                    <span class="px-2 py-1 rounded-lg text-xs font-extrabold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                        {{ formatNumber(item.remaining_quantity) }} {{ item.unit || '' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
