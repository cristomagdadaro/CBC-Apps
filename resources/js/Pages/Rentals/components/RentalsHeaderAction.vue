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
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50 active:scale-95 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    <lu-car class="h-4 w-4 text-blue-500" />
                    <span>Vehicle Rental</span>
                </Link>

                <Link
                    :href="route('rentals.venue.index')"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50 active:scale-95 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    <lu-building class="h-4 w-4 text-purple-500" />
                    <span>Venue Rental</span>
                </Link>

                <Link
                    :href="route('rentals.calendar.index')"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50 active:scale-95 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    <lu-calendar-days class="h-4 w-4 text-emerald-500" />
                    <span>Google Calendar</span>
                </Link>
            </div>
        </transition-container>
    </ActionHeaderLayout>
</template>
