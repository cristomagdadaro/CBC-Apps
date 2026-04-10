<script>
import ApiMixin from '@/Modules/mixins/ApiMixin';
import DataFormatterMixin from '@/Modules/mixins/DataFormatterMixin';

export default {
    name: "OutgoingItemCard",
    mixins:[ApiMixin, DataFormatterMixin],
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
                    const limit = Math.min(20, val.data.length); // handle <20 items
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
        class="sm:grid sm:grid-cols-2 md:grid-cols-3 flex flex-col sm:gap-1 min-w-fit"
    >
        <div
            v-for="(item, index) in outgoingFromApi.data"
            :key="`${item.item_id || item.id}-${item.unit}-${item.barcode || 'nobarcode'}-${index}`"
            @click="selectItem(item)"
            class="flex flex-col bg-white shadow hover:bg-gray-200 hover:border-gray-500 border rounded active:scale-95 duration-75"
            :data-guide="index === randomIndex ? 'supplies-sample-item' : null"
        >
            <div class="flex flex-col justify-between py-2 px-4 h-full">
                <div class="flex justify-between items-center gap-5">
                    <div class="flex flex-col">
                        <!-- Item name -->
                        <span
                            class="font-bold text-xs whitespace-nowrap overflow-ellipsis overflow-hidden"
                        >
                            {{ item.name }}
                            {{
                                item.description ? `(${item.description})` : ""
                            }}
                        </span>

                        <!-- Expiration -->
                        <span
                            v-if="item.expiration"
                            class="text-xs"
                            :class="{
                                'text-red-600 font-semibold':
                                    getExpirationStatus(item.expiration) ===
                                    'expired',
                                'text-orange-600 font-semibold': [
                                    'expiring_soon',
                                    'expiring_today',
                                ].includes(
                                    getExpirationStatus(item.expiration),
                                ),
                                'text-gray-500': !getExpirationStatus(
                                    item.expiration,
                                ),
                            }"
                        >
                            Expiry: {{ formatDate(item.expiration) }}

                            <span
                                v-if="
                                    getExpirationStatus(item.expiration) ===
                                    'expired'
                                "
                                class="ml-1"
                                >(Expired)</span
                            >

                            <span
                                v-else-if="
                                    getExpirationStatus(item.expiration) ===
                                    'expiring_today'
                                "
                                class="ml-1"
                                >(Expires Today)</span
                            >

                            <span
                                v-else-if="
                                    getExpirationStatus(item.expiration) ===
                                    'expiring_soon'
                                "
                                class="ml-1"
                                >(Expiring Soon)</span
                            >
                        </span>

                        <!-- Brand -->
                        <span class="text-xs text-gray-500 leading-none">
                            {{ item.brand }}
                        </span>

                        <!-- Barcode -->
                        <span
                            class="text-xs leading-none"
                            :class="{
                                'text-red-600 font-semibold': !item.barcode,
                                'text-gray-500': item.barcode,
                            }"
                        >
                            {{ item.barcode || "Warning! NO BARCODE" }}
                        </span>
                    </div>

                    <!-- Quantity -->
                    <span class="text-right">
                        {{ formatNumber(item.remaining_quantity) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
