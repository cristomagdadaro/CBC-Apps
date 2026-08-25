<script>
import { Link } from "@inertiajs/vue3";

export default {
    name: "RentalsHeaderAction",
    components: { Link },
    computed: {
        headerTitle() {
            if (route().current("rentals.calendar.index")) {
                return "Google Calendar Sync";
            }

            return route().current("rentals.vehicle.index") ? "Vehicle Rentals" : "Venue Rentals";
        },
        headerSubtitle() {
            if (route().current("rentals.calendar.index")) {
                return "Manage Google Calendar sync for rental schedules";
            }

            return route().current("rentals.vehicle.index") ? "Manage vehicle rentals" : "Manage venue rentals";
        },
    },
};
</script>

<template>
    <ActionHeaderLayout
        :title="headerTitle"
        :subtitle="headerSubtitle"
        :route-link="route('rentals.vehicle.index')">
        <transition-container
            type="pop-in"
            :duration="500">
            <div class="flex flex-wrap items-center gap-2">
                <Link
                    :href="route('rentals.vehicle.index')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-sm transition-all active:scale-95 shrink-0">
                    <lu-car class="w-4 h-4 text-blue-500" />
                    <span>Vehicle Rental</span>
                </Link>

                <Link
                    :href="route('rentals.venue.index')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-sm transition-all active:scale-95 shrink-0">
                    <lu-building class="w-4 h-4 text-purple-500" />
                    <span>Venue Rental</span>
                </Link>

                <Link
                    :href="route('rentals.calendar.index')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-sm transition-all active:scale-95 shrink-0">
                    <lu-calendar-days class="w-4 h-4 text-emerald-500" />
                    <span>Google Calendar</span>
                </Link>
            </div>
        </transition-container>
    </ActionHeaderLayout>
</template>
