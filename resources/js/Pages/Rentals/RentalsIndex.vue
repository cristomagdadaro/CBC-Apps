<script setup>
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const stats = ref({
    totalVehicleRentals: 0,
    totalVenueRentals: 0,
    pendingRequests: 0,
    approvedBookings: 0,
});

const loading = ref(true);
const error = ref(null);

const fetchStats = async () => {
    try {
        loading.value = true;
        
        const [vehiclesRes, venuesRes] = await Promise.all([
            fetch('/api/rental/vehicles'),
            fetch('/api/rental/venues'),
        ]);

        if (!vehiclesRes.ok || !venuesRes.ok) {
            throw new Error('Failed to fetch rental data');
        }

        const vehiclesData = await vehiclesRes.json();
        const venuesData = await venuesRes.json();

        const allVehicles = vehiclesData.data || [];
        const allVenues = venuesData.data || [];

        stats.value = {
            totalVehicleRentals: allVehicles.length,
            totalVenueRentals: allVenues.length,
            pendingRequests: [
                ...allVehicles.filter(v => v.status === 'pending'),
                ...allVenues.filter(v => v.status === 'pending'),
            ].length,
            approvedBookings: [
                ...allVehicles.filter(v => v.status === 'approved'),
                ...allVenues.filter(v => v.status === 'approved'),
            ].length,
        };
    } catch (err) {
        error.value = err.message;
        console.error('Error fetching stats:', err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchStats();
});

const menuItems = [
    {
        title: 'Vehicle Rentals',
        icon: '🚗',
        description: 'Manage vehicle rental bookings',
        link: '/rental/vehicle',
        color: 'bg-blue-500',
    },
    {
        title: 'Venue Rentals',
        icon: '🏛️',
        description: 'Book event venues',
        link: '/rental/venue',
        color: 'bg-purple-500',
    },
    {
        title: 'Calendar View',
        icon: '📅',
        description: 'View all bookings in calendar format',
        link: route('rentals.calendar'),
        color: 'bg-green-500',
    },
];
</script>

<template>
    <AppLayout title="Rental Services">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Rental Services Dashboard
            </h2>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto space-y-6">
                <!-- Statistics Grid -->
                <div v-if="!loading" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="text-4xl">🚗</span>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Vehicle Rentals
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        {{ stats.totalVehicleRentals }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="text-4xl">🏛️</span>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Venue Rentals
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        {{ stats.totalVenueRentals }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="text-4xl">⏳</span>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Pending Requests
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        {{ stats.pendingRequests }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="text-4xl">✅</span>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Approved Bookings
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        {{ stats.approvedBookings }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
                    <div class="inline-flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-gray-600 dark:text-gray-400">Loading statistics...</span>
                    </div>
                </div>

                <!-- Quick Access Menu -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Link
                        v-for="item in menuItems"
                        :key="item.title"
                        :href="item.link"
                        class="group"
                    >
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow duration-200 p-6 cursor-pointer h-full">
                            <div :class="[item.color, 'w-12 h-12 rounded-lg flex items-center justify-center text-white text-2xl mb-4']">
                                {{ item.icon }}
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                {{ item.title }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                {{ item.description }}
                            </p>
                            <div class="mt-4 flex items-center gap-2 text-blue-600 dark:text-blue-400">
                                <span>Get Started</span>
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl">ℹ️</span>
                        <div>
                            <h4 class="font-semibold text-blue-900 dark:text-blue-100">Welcome to Rental Services</h4>
                            <p class="text-sm text-blue-800 dark:text-blue-200 mt-1">
                                Use this dashboard to manage vehicle rentals, venue bookings, and view all your reservations in one place. The calendar view provides a comprehensive overview of all bookings across all resource types.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
