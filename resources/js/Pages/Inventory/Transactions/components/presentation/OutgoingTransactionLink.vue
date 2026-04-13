<script>
export default {
    name: "OutgoingTransactionLink",
};
</script>
>
<template>
    <transition-container type="pop-in" :duration="1000">
        <nav-link v-show="!route().current('transactions.outgoing')" :href="route('transactions.outgoing')" class="flex items-center bg-red-600 text-white px-2 py-1 rounded active:shadow-inner gap-1 text-sm active:scale-95 active:text-red-600 hover:bg-gray-300 hover:text-red-600">
            <LuTrendingDown />
            <span>Outgoing</span>
        </nav-link>
    </transition-container>
</template>
