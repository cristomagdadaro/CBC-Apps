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
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 1a.5.5 0 0 1 .5.5V3h2.793a.5.5 0 0 1 .353.854l-1.147 1.146a.5.5 0 1 1-.707-.707L10.086 4H8.5a.5.5 0 0 1-.5-.5V1.5A.5.5 0 0 1 8 1z"/>
                <path d="M2.5 2A1.5 1.5 0 0 0 1 3.5v9A1.5 1.5 0 0 0 2.5 14h11a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 13.5 2h-2a.5.5 0 0 0 0 1h2a.5.5 0 0 1 .5.5V6h-4.293a.5.5 0 0 0-.353.146L8 7.5 6.646 6.146A.5.5 0 0 0 6.293 6H2V3.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 0 0-1h-2zM2 7h4.086l1.561 1.561a.5.5 0 0 0 .707 0L9.914 7H14v5.5a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5V7z"/>
            </svg>
            <span>Recounting</span>
        </nav-link>
    </transition-container>
</template>
