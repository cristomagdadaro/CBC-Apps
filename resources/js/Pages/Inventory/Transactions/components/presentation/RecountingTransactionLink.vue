<script>
export default {
    name: 'RecountingTransactionLink',
};
</script>

<template>
    <transition-container type="pop-in" :duration="1000">
        <nav-link
            v-show="!route().current('transactions.recounting')"
            :href="route('transactions.recounting')"
            class="flex items-center bg-violet-600 text-white px-2 py-1 rounded active:shadow-inner gap-1 text-sm active:scale-95 hover:bg-gray-300 hover:text-violet-700"
        >
            <LuClipboardCheck />
            <span>Recounting</span>
        </nav-link>
    </transition-container>
</template>
