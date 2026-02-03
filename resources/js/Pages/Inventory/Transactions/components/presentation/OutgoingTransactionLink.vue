<script>
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";
import NavLink from "@/Components/NavLink.vue";

export default {
    name: "OutgoingTransactionLink",
    components: {TransitionContainer, NavLink},
};
</script>
>
<template>
    <transition-container type="pop-in" :duration="1000">
        <nav-link v-show="!route().current('transactions.outgoing')" :href="route('transactions.outgoing')" class="flex items-center bg-red-600 text-white px-2 py-1 rounded active:shadow-inner gap-1 text-sm active:scale-95 active:text-red-600 hover:bg-gray-300 hover:text-red-600">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bag-dash" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M5.5 10a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1-.5-.5"/>
                <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
            </svg>
            <span>Outgoing</span>
        </nav-link>
    </transition-container>
</template>