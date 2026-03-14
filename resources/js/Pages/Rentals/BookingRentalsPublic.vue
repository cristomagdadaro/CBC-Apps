<script setup>
import { computed, onMounted, ref, watch } from "vue";
import CalendarModule from "@/Components/CalendarModule.vue";

const loading = ref(true);
const error = ref(null);
const vehicleRentals = ref([]);
const venueRentals = ref([]);
const searchKeyword = ref("");
const selectedMonth = ref("");
const monthToLabel = (monthKey) => {
    const [year, month] = monthKey.split("-").map((value) => Number(value));
    return new Date(year, month - 1, 1).toLocaleDateString("en-US", {
        month: "long",
        year: "numeric",
    });
};

const statusColors = {
    pending: "#FBBF24",
    approved: "#10B981",
    rejected: "#EF4444",
};

const statusOptions = [
    { key: "pending", label: "Pending" },
    { key: "approved", label: "Approved" },
    { key: "rejected", label: "Declined" },
];

const typeOptions = [
    { key: "vehicle", label: "Vehicles", color: "#3B82F6" },
    { key: "venue", label: "Venues", color: "#10B981" },
];

const allEvents = computed(() => {
    const vehicles = vehicleRentals.value.map((rental) => ({
        id: `vehicle-${rental.id}`,
        type: "vehicle",
        status: rental.status,
        date_from: rental.date_from,
        date_to: rental.date_to,
        label: (rental.vehicle_type || "").toUpperCase(),
        subtitle: rental.requested_by || "",
        color: "#3B82F6",
        checkoutPage: "rental.vehicle.show",
        checkoutPageId: rental.id,
        checkoutPageTarget: "_blank",
    }));

    const venues = venueRentals.value.map((rental) => ({
        id: `venue-${rental.id}`,
        type: "venue",
        status: rental.status,
        date_from: rental.date_from,
        date_to: rental.date_to,
        label: rental.event_name || rental.venue_type,
        subtitle: rental.requested_by || "",
        color: "#10B981",
        checkoutPage: "rental.venue.show",
        checkoutPageId: rental.id,
        checkoutPageTarget: "_blank",
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
            .join(" ")
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
        selectedMonth.value = "";
        return;
    }

    const nowMonth = new Date().toISOString().slice(0, 7);

    if (
        selectedMonth.value &&
        availableMonths.value.includes(selectedMonth.value)
    ) {
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
            fetch(
                "/api/guest/rental/vehicles?statuses=pending,approved,rejected",
            ),
            fetch(
                "/api/guest/rental/venues?statuses=pending,approved,rejected",
            ),
        ]);

        if (!vehicleRes.ok || !venueRes.ok) {
            throw new Error("Unable to load booking calendars right now.");
        }

        const vehicleData = await vehicleRes.json();
        const venueData = await venueRes.json();

        vehicleRentals.value = Array.isArray(vehicleData?.data)
            ? vehicleData.data
            : [];
        venueRentals.value = Array.isArray(venueData?.data)
            ? venueData.data
            : [];

        syncSelectedMonth();
    } catch (err) {
        error.value = err?.message || "Failed to load booking data.";
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
                <a
                    :href="route('rental.vehicle.guest')"
                    class="flex gap-2 items-center rounded-lg border border-gray-200 bg-white px-4 py-3 hover:bg-gray-50"
                >
                    <lu-truck class="w-8 h-8 text-orange-500" />
                    <div>
                        <p class="font-semibold text-gray-900">
                            Vehicle Rental Form
                        </p>
                        <p class="text-sm text-gray-600">
                            Request and check vehicle bookings.
                        </p>
                    </div>
                </a>
                <a
                    :href="route('rental.venue.guest')"
                    class="flex gap-2 items-center rounded-lg border border-gray-200 bg-white px-4 py-3 hover:bg-gray-50"
                >
                    <lu-building class="w-8 h-8 text-indigo-500" />
                    <div>
                        <p class="font-semibold text-gray-900">Venue Rental Form</p>
                        <p class="text-sm text-gray-600">
                            Request and check venue bookings.
                        </p>
                    </div>
                </a>
            </div>

            <div
                id="center-calendar"
                class="rounded-lg border border-gray-200 bg-white p-4"
            >
                <div class="w-full mb-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Search Bookings</label
                    >
                    <input
                        v-model="searchKeyword"
                        type="text"
                        placeholder="Search by requester, event, type, or status"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-AB focus:ring-AB"
                    />
                </div>

                <div v-if="loading" class="text-sm text-gray-500 flex items-center gap-2 justify-center">
                    <loader-icon class="w-6 h-6 text-gray-500 animate-spin" />
                    Loading booking calendars...
                </div>
                <div v-else-if="error" class="text-sm text-red-600">
                    {{ error }}
                </div>
                <div
                    v-else-if="!searchableEvents.length"
                    class="text-sm text-gray-500"
                >
                    No bookings matched your search.
                </div>
                <calendar-module
                    v-else
                    title="Vehicle and Venue Bookings"
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
