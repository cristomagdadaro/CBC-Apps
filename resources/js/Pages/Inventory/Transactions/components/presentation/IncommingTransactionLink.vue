<script>
export default {
    name: "IncommingTransactionLink",
};
</script>
>
<template>
    <transition-container type="pop-in" :duration="1000">
        <nav-link v-show="!route().current('transactions.incoming')"  :href="route('transactions.incoming')" class="flex items-center bg-blue-600 text-white px-2 py-1 rounded active:shadow-inner gap-1 text-sm active:scale-95 active:text-red-600 hover:bg-gray-300 hover:text-blue-600">
            <LuTrendingUp />
            <span>Incoming</span>
        </nav-link>
    </transition-container>
</template>
