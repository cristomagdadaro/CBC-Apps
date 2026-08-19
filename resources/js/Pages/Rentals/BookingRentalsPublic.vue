<script>
import CalendarModule from "@/Components/CalendarModule.vue";
import { subscribeToRealtimeChannels } from "@/Modules/realtime/subscriptions";

export default {
    components: {
        CalendarModule,
    },

    data() {
        return {
            loading: true,
            error: null,
            vehicleRentals: [],
            venueRentals: [],
            searchKeyword: "",
            selectedMonth: "",
            realtimeCleanup: null,
            realtimeRefreshTimer: null,

            statusColors: {
                pending: "#FBBF24",
                approved: "#10B981",
                in_progress: "#3B82F6",
                rejected: "#EF4444",
                cancelled: "#6B7280",
                completed: "#334155",
            },

            statusOptions: [
                { key: "pending", label: "Pending" },
                { key: "approved", label: "Approved" },
                { key: "in_progress", label: "In Progress" },
                { key: "rejected", label: "Rejected" },
                { key: "cancelled", label: "Cancelled" },
                { key: "completed", label: "Completed" },
            ],

            typeOptions: [
                { key: "vehicle", label: "Vehicles", color: "#3B82F6" },
                { key: "venue", label: "Venues", color: "#10B981" },
            ],
        };
    },

    computed: {
        allEvents() {
            const vehicles = this.vehicleRentals.map((rental) => ({
                id: `vehicle-${rental.id}`,
                type: "vehicle",
                status: rental.status,
                date_from: rental.date_from,
                date_to: rental.date_to,
                label: `${rental.requested_by || "Unknown requester"} (${rental.vehicle_type || "Vehicle pending"})`,
                subtitle: [rental.destination_location, rental.purpose]
                    .filter(Boolean)
                    .join(" - "),
                checkoutPage: "rental.vehicle.show",
                checkoutPageId: rental.id,
                checkoutPageTarget: "_blank",
            }));

            const venues = this.venueRentals.map((rental) => ({
                id: `venue-${rental.id}`,
                type: "venue",
                status: rental.status,
                date_from: rental.date_from,
                date_to: rental.date_to,
                label: rental.event_name || "Untitled Event",
                subtitle: rental.requested_by || "",
                checkoutPage: "rental.venue.show",
                checkoutPageId: rental.id,
                checkoutPageTarget: "_blank",
            }));

            return [...vehicles, ...venues];
        },

        searchableEvents() {
            const keyword = this.searchKeyword.trim().toLowerCase();

            if (!keyword) {
                return this.allEvents;
            }

            return this.allEvents.filter((event) => {
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
        },

        availableMonths() {
            const keys = new Set();

            this.searchableEvents.forEach((event) => {
                const value = event.date_from || event.date_to;

                if (!value) return;

                const key = String(value).slice(0, 7);

                if (key.length === 7) {
                    keys.add(key);
                }
            });

            return Array.from(keys).sort();
        },

        selectedStartDate() {
            if (!this.selectedMonth) {
                return null;
            }

            return `${this.selectedMonth}-01`;
        },
    },

    watch: {
        availableMonths() {
            this.syncSelectedMonth();
        },
    },

    methods: {
        cleanupRealtime() {
            if (typeof this.realtimeCleanup === "function") {
                this.realtimeCleanup();
            }

            this.realtimeCleanup = null;
        },
        configureRealtime() {
            this.cleanupRealtime();

            this.realtimeCleanup = subscribeToRealtimeChannels([
                {
                    type: "public",
                    channel: "public.rentals.calendar",
                    event: "rentals.calendar.changed",
                    handler: () => this.scheduleRealtimeRefresh(),
                },
            ]);
        },
        scheduleRealtimeRefresh() {
            if (this.realtimeRefreshTimer) {
                clearTimeout(this.realtimeRefreshTimer);
            }

            this.realtimeRefreshTimer = setTimeout(() => {
                this.loadBookings();
            }, 400);
        },
        monthToLabel(monthKey) {
            const [year, month] = monthKey.split("-").map((v) => Number(v));

            return new Date(year, month - 1, 1).toLocaleDateString("en-US", {
                month: "long",
                year: "numeric",
            });
        },

        syncSelectedMonth() {
            if (!this.availableMonths.length) {
                this.selectedMonth = "";
                return;
            }

            const nowMonth = new Date().toISOString().slice(0, 7);

            if (
                this.selectedMonth &&
                this.availableMonths.includes(this.selectedMonth)
            ) {
                return;
            }

            if (this.availableMonths.includes(nowMonth)) {
                this.selectedMonth = nowMonth;
                return;
            }

            this.selectedMonth = this.availableMonths[0];
        },

        async loadBookings() {
            try {
                this.loading = true;
                this.error = null;

                const [vehicleRes, venueRes] = await Promise.all([
                    fetch(
                        "/api/guest/rental/vehicles?statuses=pending,approved,in_progress,rejected,cancelled,completed"
                    ),
                    fetch(
                        "/api/guest/rental/venues?statuses=pending,approved,in_progress,rejected,cancelled,completed"
                    ),
                ]);

                if (!vehicleRes.ok || !venueRes.ok) {
                    throw new Error(
                        "Unable to load booking calendars right now."
                    );
                }

                const vehicleData = await vehicleRes.json();
                const venueData = await venueRes.json();

                this.vehicleRentals = Array.isArray(vehicleData?.data)
                    ? vehicleData.data
                    : [];

                this.venueRentals = Array.isArray(venueData?.data)
                    ? venueData.data
                    : [];

                this.syncSelectedMonth();
            } catch (err) {
                this.error = err?.message || "Failed to load booking data.";
            } finally {
                this.loading = false;
            }
        },
    },

    mounted() {
        this.loadBookings();
        this.configureRealtime();
    },
    beforeUnmount() {
        if (this.realtimeRefreshTimer) {
            clearTimeout(this.realtimeRefreshTimer);
        }

        this.cleanupRealtime();
    },
};
</script>

<template>
    <Head title="Booking and Rentals" />

    <GuestFormPage
        title="Booking and Rentals"
        subtitle="Unified page for Vehicle Rental, Venue Rental, and Center Calendar visibility."
        guide-key="rental-bookings-public"
        :delay-ready="true"
    >
        <div class="flex flex-col gap-4 w-full">
            <div data-guide="rental-quick-links" class="grid md:grid-cols-2 gap-3">
                <a
                    :href="route('rental.vehicle.guest')"
                    class="flex gap-4 items-center rounded-2xl border border-gray-100 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg px-5 py-4 hover:-translate-y-1 hover:shadow-xl transition-all duration-300"
                >
                    <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-xl">
                        <lu-truck class="w-8 h-8 text-orange-500" />
                    </div>
                    <div>
                        <p class="font-bold text-gray-900 dark:text-gray-100">
                            Vehicle Rental Form
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Request and check vehicle bookings.
                        </p>
                    </div>
                </a>
                <a
                    :href="route('rental.venue.guest')"
                    class="flex gap-4 items-center rounded-2xl border border-gray-100 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg px-5 py-4 hover:-translate-y-1 hover:shadow-xl transition-all duration-300"
                >
                    <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl">
                        <lu-building class="w-8 h-8 text-indigo-500 dark:text-indigo-400" />
                    </div>
                    <div>
                        <p class="font-bold text-gray-900 dark:text-gray-100">Venue Rental Form</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Request and check venue bookings.
                        </p>
                    </div>
                </a>
            </div>

            <div
                id="center-calendar"
                class="rounded-2xl border border-gray-100 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg p-6 shadow-xl"
            >
                <div class="w-full mb-6">
                    <label class="block text-sm font-bold text-gray-900 dark:text-gray-100 mb-2"
                        >Search Bookings</label
                    >
                    <input
                        v-model="searchKeyword"
                        type="text"
                        data-guide='rental-calendar-search'
                        placeholder="Search by requester, event, type, or status"
                        class="w-full rounded-xl border border-gray-200 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50 dark:text-white px-4 py-3 text-sm focus:border-AB focus:ring-AB shadow-inner"
                    />
                </div>

                <div v-if="loading" class="text-sm text-gray-500 dark:text-slate-400 flex items-center gap-2 justify-center py-10">
                    <loader-icon class="w-6 h-6 text-AB dark:text-emerald-400 animate-spin" />
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
                    subtitle="Shows the full rental workflow across vehicle and venue bookings."
                    :events="searchableEvents"
                    :type-options="typeOptions"
                    :status-options="statusOptions"
                    :status-colors="statusColors"
                    :show-today="true"
                    :show-type-filter="true"
                    :show-status-filter="true"
                    :show-stats="true"
                    :start-date="selectedStartDate"
                    class="!bg-transparent !shadow-none !border-0"
                />
            </div>
        </div>
    </GuestFormPage>
</template>
