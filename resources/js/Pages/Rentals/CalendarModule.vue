<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const currentDate = ref(new Date(2026, 1, 1)); // February 1, 2026
const viewMode = ref('month'); // 'month', 'week', 'day'
const filterType = ref('all'); // 'all', 'vehicles', 'venues'
const filterStatus = ref('all'); // 'all', 'pending', 'approved', 'rejected', 'completed', 'cancelled'

const vehicleRentals = ref([]);
const venueRentals = ref([]);
const loading = ref(true);
const error = ref(null);

const vehicleTypes = [
    { key: 'innova', label: 'Innova', color: '#3B82F6' },
    { key: 'pickup', label: 'Pickup', color: '#8B5CF6' },
    { key: 'van', label: 'Van', color: '#EC4899' },
    { key: 'suv', label: 'SUV', color: '#F59E0B' },
];

const venueTypes = [
    { key: 'plenary', label: 'Plenary Hall', color: '#10B981' },
    { key: 'training_room', label: 'Training Room', color: '#06B6D4' },
    { key: 'mph', label: 'Multi-Purpose Hall', color: '#6366F1' },
];

const statusColors = {
    pending: '#FBBF24',
    approved: '#10B981',
    rejected: '#EF4444',
    completed: '#6B7280',
    cancelled: '#9CA3AF',
};

// Fetch all rental data
const fetchRentalData = async () => {
    try {
        loading.value = true;
        error.value = null;

        const [vehiclesRes, venuesRes] = await Promise.all([
            fetch('/api/rental/vehicles?per_page=1000'),
            fetch('/api/rental/venues?per_page=1000'),
        ]);

        if (!vehiclesRes.ok || !venuesRes.ok) {
            throw new Error('Failed to fetch rental data');
        }

        const vehiclesData = await vehiclesRes.json();
        const venuesData = await venuesRes.json();

        vehicleRentals.value = vehiclesData.data || [];
        venueRentals.value = venuesData.data || [];
    } catch (err) {
        error.value = err.message;
        console.error('Error fetching rentals:', err);
    } finally {
        loading.value = false;
    }
};

// Filter rentals based on current filters
const filteredRentals = computed(() => {
    let rentals = [];

    if (filterType.value === 'all' || filterType.value === 'vehicles') {
        rentals = [...rentals, ...vehicleRentals.value.map(v => ({
            ...v,
            type: 'vehicle',
            resourceType: v.vehicle_type,
        }))];
    }

    if (filterType.value === 'all' || filterType.value === 'venues') {
        rentals = [...rentals, ...venueRentals.value.map(v => ({
            ...v,
            type: 'venue',
            resourceType: v.venue_type,
        }))];
    }

    if (filterStatus.value !== 'all') {
        rentals = rentals.filter(r => r.status === filterStatus.value);
    }

    return rentals;
});

// Get days in month
const daysInMonth = computed(() => {
    return new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 0).getDate();
});

// Get first day of month
const firstDayOfMonth = computed(() => {
    return new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), 1).getDay();
});

// Get all days for calendar grid
const calendarDays = computed(() => {
    const days = [];
    
    // Add empty cells for days before month starts
    for (let i = 0; i < firstDayOfMonth.value; i++) {
        days.push(null);
    }
    
    // Add days of month
    for (let i = 1; i <= daysInMonth.value; i++) {
        days.push(i);
    }
    
    return days;
});

// Get bookings for a specific date
const getBookingsForDate = (day) => {
    if (!day) return [];
    
    const dateStr = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), day)
        .toISOString().split('T')[0];
    
    return filteredRentals.value.filter(rental => {
        const rentalDateFrom = rental.date_from;
        const rentalDateTo = rental.date_to;
        
        return dateStr >= rentalDateFrom && dateStr <= rentalDateTo;
    });
};

// Get color for rental
const getColorForRental = (rental) => {
    if (rental.type === 'vehicle') {
        const vehicleType = vehicleTypes.find(v => v.key === rental.vehicle_type);
        return vehicleType?.color || '#6B7280';
    } else {
        const venueType = venueTypes.find(v => v.key === rental.venue_type);
        return venueType?.color || '#6B7280';
    }
};

// Get label for rental
const getLabelForRental = (rental) => {
    if (rental.type === 'vehicle') {
        return rental.vehicle_type.toUpperCase();
    } else {
        return rental.event_name || rental.venue_type.replace('_', ' ');
    }
};

// Navigation
const previousMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1);
};

const nextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1);
};

const goToToday = () => {
    currentDate.value = new Date();
};

// Format month/year
const monthYearLabel = computed(() => {
    const options = { month: 'long', year: 'numeric' };
    return currentDate.value.toLocaleDateString('en-US', options);
});

// Week days
const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

onMounted(() => {
    fetchRentalData();
});
</script>

<template>
    <AppLayout title="Rental Services Calendar">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Rental Services Calendar
            </h2>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto space-y-6">
                <!-- Controls -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Month Navigation -->
                        <div class="flex items-center justify-between sm:col-span-2 lg:col-span-1">
                            <button
                                @click="previousMonth"
                                class="px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300"
                            >
                                ← Previous
                            </button>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 whitespace-nowrap mx-2">
                                {{ monthYearLabel }}
                            </h3>
                            <button
                                @click="nextMonth"
                                class="px-3 py-2 rounded-md bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300"
                            >
                                Next →
                            </button>
                        </div>

                        <!-- Filter Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Resource Type
                            </label>
                            <select
                                v-model="filterType"
                                class="w-full px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                            >
                                <option value="all">All Resources</option>
                                <option value="vehicles">Vehicles Only</option>
                                <option value="venues">Venues Only</option>
                            </select>
                        </div>

                        <!-- Filter Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Status
                            </label>
                            <select
                                v-model="filterStatus"
                                class="w-full px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                            >
                                <option value="all">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <!-- Today Button -->
                        <div class="flex items-end">
                            <button
                                @click="goToToday"
                                class="w-full px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white font-medium"
                            >
                                Today
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Calendar -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <!-- Week days header -->
                            <thead class="bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <th
                                        v-for="day in weekDays"
                                        :key="day"
                                        class="px-4 py-3 text-center font-semibold text-gray-900 dark:text-gray-100"
                                    >
                                        {{ day }}
                                    </th>
                                </tr>
                            </thead>

                            <!-- Calendar days -->
                            <tbody>
                                <tr v-for="(week, weekIndex) in Math.ceil(calendarDays.length / 7)" :key="weekIndex" class="border-t border-gray-200 dark:border-gray-700">
                                    <td
                                        v-for="dayIndex in 7"
                                        :key="dayIndex"
                                        class="border-r border-gray-200 dark:border-gray-700 last:border-r-0 h-32 sm:h-40 p-2 bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                    >
                                        <div class="h-full flex flex-col">
                                            <!-- Day number -->
                                            <div class="font-semibold text-gray-900 dark:text-gray-100 mb-1">
                                                {{ calendarDays[(weekIndex - 1) * 7 + dayIndex - 1] || '' }}
                                            </div>

                                            <!-- Bookings -->
                                            <div class="flex-1 overflow-y-auto space-y-1">
                                                <div
                                                    v-for="booking in getBookingsForDate(calendarDays[(weekIndex - 1) * 7 + dayIndex - 1])"
                                                    :key="`${booking.id}-${booking.type}`"
                                                    :style="{ backgroundColor: getColorForRental(booking) + '20', borderColor: getColorForRental(booking) }"
                                                    class="text-xs p-1.5 rounded border-l-2 cursor-pointer hover:opacity-80 transition-opacity"
                                                    :title="`${booking.requested_by} - ${booking.status}`"
                                                >
                                                    <div class="font-medium text-gray-900 dark:text-gray-100 truncate">
                                                        {{ getLabelForRental(booking) }}
                                                    </div>
                                                    <div class="text-gray-600 dark:text-gray-400 truncate">
                                                        {{ booking.requested_by }}
                                                    </div>
                                                    <div
                                                        :style="{ color: statusColors[booking.status] }"
                                                        class="text-xs font-semibold capitalize"
                                                    >
                                                        {{ booking.status }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Legend -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Legend</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Vehicle Types -->
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Vehicles</h4>
                            <div class="space-y-2">
                                <div v-for="vtype in vehicleTypes" :key="vtype.key" class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded" :style="{ backgroundColor: vtype.color }"></div>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ vtype.label }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Venue Types -->
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Venues</h4>
                            <div class="space-y-2">
                                <div v-for="vtype in venueTypes" :key="vtype.key" class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded" :style="{ backgroundColor: vtype.color }"></div>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ vtype.label }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Status Colors</h4>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded" :style="{ backgroundColor: statusColors.pending }"></div>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Pending</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded" :style="{ backgroundColor: statusColors.approved }"></div>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Approved</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded" :style="{ backgroundColor: statusColors.rejected }"></div>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Rejected</span>
                                </div>
                            </div>
                        </div>

                        <!-- Info -->
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Statistics</h4>
                            <div class="space-y-1 text-sm text-gray-700 dark:text-gray-300">
                                <div>
                                    <span class="font-medium">Total Vehicles:</span>
                                    {{ vehicleRentals.length }}
                                </div>
                                <div>
                                    <span class="font-medium">Total Venues:</span>
                                    {{ venueRentals.length }}
                                </div>
                                <div>
                                    <span class="font-medium">Visible Bookings:</span>
                                    {{ filteredRentals.length }}
                                </div>
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
                        <span class="text-gray-600 dark:text-gray-400">Loading calendar data...</span>
                    </div>
                </div>

                <!-- Error State -->
                <div v-if="error" class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg p-4">
                    <p class="text-red-800 dark:text-red-200">
                        <strong>Error:</strong> {{ error }}
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
table {
    table-layout: fixed;
}

td {
    vertical-align: top;
}

/* Smooth transitions */
:deep(.transition-colors) {
    transition: background-color 0.2s ease-in-out;
}

:deep(.transition-opacity) {
    transition: opacity 0.2s ease-in-out;
}
</style>
