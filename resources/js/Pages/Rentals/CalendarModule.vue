<script setup>
import { ref, computed, onMounted } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import CalendarModule from "@/Components/CalendarModule.vue";

const vehicleRentals = ref([]);
const venueRentals = ref([]);
const loading = ref(true);
const error = ref(null);

const vehicleTypes = [
    { key: "innova", label: "Innova", color: "#3B82F6" },
    { key: "pickup", label: "Pickup", color: "#8B5CF6" },
    { key: "van", label: "Van", color: "#EC4899" },
    { key: "suv", label: "SUV", color: "#F59E0B" },
];

const venueTypes = [
    { key: "plenary", label: "Plenary Hall", color: "#10B981" },
    { key: "training_room", label: "Training Room", color: "#06B6D4" },
    { key: "mph", label: "Multi-Purpose Hall", color: "#6366F1" },
];

const statusColors = {
    pending: "#FBBF24",
    approved: "#10B981",
    rejected: "#EF4444",
    completed: "#6B7280",
    cancelled: "#9CA3AF",
};

const statusOptions = [
    { key: "pending", label: "Pending" },
    { key: "approved", label: "Approved" },
    { key: "rejected", label: "Rejected" },
    { key: "completed", label: "Completed" },
    { key: "cancelled", label: "Cancelled" },
];

const typeOptions = [
    { key: "vehicle", label: "Vehicles" },
    { key: "venue", label: "Venues" },
];

const legendGroups = [
    {
        title: "Vehicles",
        items: vehicleTypes.map((type) => ({ label: type.label, color: type.color })),
    },
    {
        title: "Venues",
        items: venueTypes.map((type) => ({ label: type.label, color: type.color })),
    },
    {
        title: "Status",
        items: Object.entries(statusColors).map(([key, color]) => ({
            label: key.charAt(0).toUpperCase() + key.slice(1),
            color,
        })),
    },
];

const findTypeColor = (typeKey, list) => {
    return list.find((item) => item.key === typeKey)?.color || "#6B7280";
};

const calendarEvents = computed(() => {
    const vehicles = vehicleRentals.value.map((rental) => ({
        id: `vehicle-${rental.id}`,
        type: "vehicle",
        status: rental.status,
        date_from: rental.date_from,
        date_to: rental.date_to,
        label: (rental.vehicle_type || "").toUpperCase(),
        subtitle: rental.requested_by,
        color: findTypeColor(rental.vehicle_type, vehicleTypes),
    }));

    const venues = venueRentals.value.map((rental) => ({
        id: `venue-${rental.id}`,
        type: "venue",
        status: rental.status,
        date_from: rental.date_from,
        date_to: rental.date_to,
        label: rental.event_name || (rental.venue_type || "").replace("_", " "),
        subtitle: rental.requested_by,
        color: findTypeColor(rental.venue_type, venueTypes),
    }));

    return [...vehicles, ...venues];
});

const fetchRentalData = async () => {
    try {
        loading.value = true;
        error.value = null;

        const [vehiclesRes, venuesRes] = await Promise.all([
            fetch("/api/rental/vehicles?per_page=1000"),
            fetch("/api/rental/venues?per_page=1000"),
        ]);

        if (!vehiclesRes.ok || !venuesRes.ok) {
            throw new Error("Failed to fetch rental data");
        }

        const vehiclesData = await vehiclesRes.json();
        const venuesData = await venuesRes.json();

        vehicleRentals.value = vehiclesData.data || [];
        venueRentals.value = venuesData.data || [];
    } catch (err) {
        error.value = err.message;
        console.error("Error fetching rentals:", err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchRentalData();
});
</script>

<template>
    <AppLayout title="Rental Services Calendar">
        <template #header>
            <ActionHeaderLayout title="Rental Services Calendar" subtitle="Track vehicles and venues across the month." :route-link="route('rentals.vehicle.index')" />
        </template>

        <div class="default-container pt-5">
            <div class="max-w-7xl mx-auto space-y-6">
                <CalendarModule
                    title="Rental Services Calendar"
                    subtitle="Track vehicles and venues across the month."
                    :events="calendarEvents"
                    :type-options="typeOptions"
                    :status-options="statusOptions"
                    :status-colors="statusColors"
                    :legend-groups="legendGroups"
                />

                <div v-if="loading" class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
                    <div class="inline-flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-gray-600 dark:text-gray-400">Loading calendar data...</span>
                    </div>
                </div>

                <div v-if="error" class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg p-4">
                    <p class="text-red-800 dark:text-red-200">
                        <strong>Error:</strong> {{ error }}
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
