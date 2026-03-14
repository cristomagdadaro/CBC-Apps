<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import CalendarModule from '@/Components/CalendarModule.vue';

const loading = ref(true);
const error = ref(null);
const vehicleRentals = ref([]);
const venueRentals = ref([]);
const searchKeyword = ref('');
const selectedMonth = ref('');
const monthToLabel = (monthKey) => {
    const [year, month] = monthKey.split('-').map((value) => Number(value));
    return new Date(year, month - 1, 1).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
};

const statusColors = {
    pending: '#FBBF24',
    approved: '#10B981',
    rejected: '#EF4444',
};

const statusOptions = [
    { key: 'pending', label: 'Pending' },
    { key: 'approved', label: 'Approved' },
    { key: 'rejected', label: 'Declined' },
];

const typeOptions = [
    { key: 'vehicle', label: 'Vehicles', color: '#3B82F6' },
    { key: 'venue', label: 'Venues', color: '#10B981' },
];

const allEvents = computed(() => {
    const vehicles = vehicleRentals.value.map((rental) => ({
        id: `vehicle-${rental.id}`,
        type: 'vehicle',
        status: rental.status,
        date_from: rental.date_from,
        date_to: rental.date_to,
        label: (rental.vehicle_type || '').toUpperCase(),
        subtitle: rental.requested_by || '',
        color: '#3B82F6',
    }));

    const venues = venueRentals.value.map((rental) => ({
        id: `venue-${rental.id}`,
        type: 'venue',
        status: rental.status,
        date_from: rental.date_from,
        date_to: rental.date_to,
        label: rental.event_name || rental.venue_type,
        subtitle: rental.requested_by || '',
        color: '#10B981',
    }));

    return [...vehicles, ...venues];
});

const searchableEvents = computed(() => {
    const keyword = searchKeyword.value.trim().toLowerCase();

    if (!keyword) {
        return allEvents.value;
    }

    return allEvents.value.filter((event) => {
        const haystack = [
            event.label,
            event.subtitle,
            event.type,
            event.status,
            event.date_from,
            event.date_to,
        ]
            .filter(Boolean)
            .join(' ')
            .toLowerCase();

        return haystack.includes(keyword);
    });
});

const availableMonths = computed(() => {
    const keys = new Set();

    searchableEvents.value.forEach((event) => {
        const value = event.date_from || event.date_to;
        if (!value) {
            return;
        }

        const key = String(value).slice(0, 7);
        if (key.length === 7) {
            keys.add(key);
        }
    });

    return Array.from(keys).sort();
});

const selectedStartDate = computed(() => {
    if (!selectedMonth.value) {
        return null;
    }

    return `${selectedMonth.value}-01`;
});

const syncSelectedMonth = () => {
    if (!availableMonths.value.length) {
        selectedMonth.value = '';
        return;
    }

    const nowMonth = new Date().toISOString().slice(0, 7);

    if (selectedMonth.value && availableMonths.value.includes(selectedMonth.value)) {
        return;
    }

    if (availableMonths.value.includes(nowMonth)) {
        selectedMonth.value = nowMonth;
        return;
    }

    selectedMonth.value = availableMonths.value[0];
};

watch(availableMonths, () => {
    syncSelectedMonth();
});

const loadBookings = async () => {
    try {
        loading.value = true;
        error.value = null;

        const [vehicleRes, venueRes] = await Promise.all([
            fetch('/api/guest/rental/vehicles?statuses=pending,approved,rejected'),
            fetch('/api/guest/rental/venues?statuses=pending,approved,rejected'),
        ]);

        if (!vehicleRes.ok || !venueRes.ok) {
            throw new Error('Unable to load booking calendars right now.');
        }

        const vehicleData = await vehicleRes.json();
        const venueData = await venueRes.json();

        vehicleRentals.value = Array.isArray(vehicleData?.data) ? vehicleData.data : [];
        venueRentals.value = Array.isArray(venueData?.data) ? venueData.data : [];

        syncSelectedMonth();
    } catch (err) {
        error.value = err?.message || 'Failed to load booking data.';
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadBookings();
});
</script>

<template>
    <Head title="Booking and Rentals" />

    <GuestFormPage
        title="Booking and Rentals"
        subtitle="Unified page for Vehicle Rental, Venue Rental, and Center Calendar visibility."
        :delay-ready="true"
    >
        <div class="w-full flex flex-col gap-4">
            <div class="grid md:grid-cols-2 gap-3">
                <a :href="route('rental.vehicle.guest')" class="rounded-lg border border-gray-200 bg-white px-4 py-3 hover:bg-gray-50">
                    <p class="font-semibold text-gray-900">Vehicle Rental</p>
                    <p class="text-sm text-gray-600">Request and check vehicle bookings.</p>
                </a>
                <a :href="route('rental.venue.guest')" class="rounded-lg border border-gray-200 bg-white px-4 py-3 hover:bg-gray-50">
                    <p class="font-semibold text-gray-900">Venue Rental</p>
                    <p class="text-sm text-gray-600">Request and check venue bookings.</p>
                </a>
            </div>

            <div id="center-calendar" class="rounded-lg border border-gray-200 bg-white p-4">
                <div class="flex flex-col md:flex-row md:items-end gap-3 mb-3">
                    <div class="w-full md:max-w-sm">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search Bookings</label>
                        <input
                            v-model="searchKeyword"
                            type="text"
                            placeholder="Search by requester, event, type, or status"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-AB focus:ring-AB"
                        />
                    </div>
                    <div class="w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Available Months</label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="month in availableMonths"
                                :key="month"
                                type="button"
                                class="px-3 py-1.5 rounded-md border text-sm"
                                :class="selectedMonth === month ? 'bg-AB text-white border-AB' : 'bg-white text-gray-700 border-gray-300'"
                                @click="selectedMonth = month"
                            >
                                {{ monthToLabel(month) }}
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="loading" class="text-sm text-gray-500">Loading booking calendars...</div>
                <div v-else-if="error" class="text-sm text-red-600">{{ error }}</div>
                <div v-else-if="!searchableEvents.length" class="text-sm text-gray-500">No bookings matched your search.</div>
                <calendar-module
                    v-else
                    title="Center Booking Calendar"
                    subtitle="Shows pending, approved, and declined bookings from venue and vehicle rentals."
                    :events="searchableEvents"
                    :type-options="typeOptions"
                    :status-options="statusOptions"
                    :status-colors="statusColors"
                    :show-today="true"
                    :show-type-filter="true"
                    :show-status-filter="true"
                    :show-stats="true"
                    :start-date="selectedStartDate"
                />
            </div>
        </div>
    </GuestFormPage>
</template>
