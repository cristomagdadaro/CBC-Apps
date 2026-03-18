<script>
export default {
    name: 'RentalsHeaderAction',
    computed: {
        headerTitle() {
            if (route().current('rentals.calendar.index')) {
                return 'Google Calendar Sync'
            }

            return route().current('rentals.vehicle.index') ? 'Vehicle Rentals' : 'Venue Rentals'
        },
        headerSubtitle() {
            if (route().current('rentals.calendar.index')) {
                return 'Manage Google Calendar sync for rental schedules'
            }

            return route().current('rentals.vehicle.index') ? 'Manage vehicle rentals' : 'Manage venue rentals'
        },
    },
}
</script>

<template>
    <ActionHeaderLayout :title="headerTitle" :subtitle="headerSubtitle" :route-link="route('rentals.vehicle.index')">
        <Link
            :href="route('rentals.vehicle.index')"
            class="inline-flex items-center px-4 py-2 bg-blue-600 dark:bg-blue-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 dark:hover:bg-blue-600 focus:bg-blue-700 dark:focus:bg-blue-600 active:bg-blue-900 dark:active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
        >
            Vehicle Rental
        </Link>
        
        <Link
            :href="route('rentals.venue.index')"
            class="inline-flex items-center px-4 py-2 bg-purple-600 dark:bg-purple-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 dark:hover:bg-purple-600 focus:bg-purple-700 dark:focus:bg-purple-600 active:bg-purple-900 dark:active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
        >
            Venue Rental
        </Link>

        <Link
            :href="route('rentals.calendar.index')"
            class="inline-flex items-center px-4 py-2 bg-green-600 dark:bg-green-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 dark:hover:bg-green-600 focus:bg-green-700 dark:focus:bg-green-600 active:bg-green-900 dark:active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
        >
            Google Calendar
        </Link>
    </ActionHeaderLayout>
</template>
