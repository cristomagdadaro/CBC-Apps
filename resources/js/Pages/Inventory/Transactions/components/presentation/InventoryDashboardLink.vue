<script>
export default {
    name: "InventoryDashboardLink",
};
</script>

<template>
    <transition-container type="pop-in" :duration="1000">
        <nav-link
            v-show="!route().current('transactions.dashboard')"
            :href="route('transactions.dashboard')"
            class="flex items-center bg-emerald-600 text-white px-2 py-1 rounded active:shadow-inner gap-1 text-sm active:scale-95 hover:bg-gray-300 hover:text-emerald-700"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                fill="currentColor"
                viewBox="0 0 16 16"
            >
                <path d="M0 0h1v15h15v1H0V0zm14.817 3.113a.5.5 0 0 1 .07.704l-4.5 5.5a.5.5 0 0 1-.74.037L7.06 6.767 4.03 10.03a.5.5 0 1 1-.732-.682l3.4-3.667a.5.5 0 0 1 .725-.013l2.565 2.565 4.125-5.04a.5.5 0 0 1 .704-.07z"/>
            </svg>
            <span>Dashboard</span>
        </nav-link>
    </transition-container>
</template>
