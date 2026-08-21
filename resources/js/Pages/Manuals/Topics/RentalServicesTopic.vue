<script>
import { computed, ref, watchEffect } from 'vue';
import TopicLayout from '@/Pages/Manuals/Components/TopicLayout.vue';

export default {
    name: 'RentalServicesTopic',
    components: {
        TopicLayout
    },
    props: {
        showDeveloperSections: {
            type: Boolean,
            default: true,
        },
    },
    setup(props) {
        const activeSubsection = ref('overview');
        const developerSubsectionIds = ['api', 'validation', 'examples', 'testing'];

        const subsections = {
            overview: 'Overview',
            features: 'Key Features',
            vehicleRentals: 'Vehicle Rentals',
            venueRentals: 'Venue Rentals',
            api: 'API Endpoints',
            validation: 'Validation Rules',
            examples: 'Usage Examples',
            testing: 'Testing',
            future: 'Future Enhancements',
        };

        const visibleSubsections = computed(() => {
            if (props.showDeveloperSections) {
                return subsections;
            }

            return Object.fromEntries(
                Object.entries(subsections).filter(([key]) => !developerSubsectionIds.includes(key))
            );
        });

        watchEffect(() => {
            if (!visibleSubsections.value[activeSubsection.value]) {
                activeSubsection.value = 'overview';
            }
        });

        return {
            activeSubsection,
            visibleSubsections,
        };
    },
};
</script>

<template>
    <TopicLayout
        title="Rental Services Module"
        description="Manage vehicle rentals, venue bookings, and hostel accommodations with built-in conflict detection and status tracking[cite: 5]."
        icon="LuKey"
        minHeight="min-h-[400px]"
    >
        <template #header-tabs>
            <!-- Subsection Navigation -->
            <div class="flex flex-wrap gap-2 px-5 py-4 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/50 dark:bg-slate-800/20 shadow-inner">
                <button
                    v-for="(label, id) in visibleSubsections"
                    :key="id"
                    @click="activeSubsection = id"
                    class="px-4 py-2 rounded-xl text-sm font-semibold transition-all whitespace-nowrap"
                    :class="activeSubsection === id ? 'bg-indigo-600 shadow-sm text-white ring-1 ring-indigo-700' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-700/50'"
                >
                    {{ label }}
                </button>
            </div>
        </template>

        <!-- Overview Section -->
                <div v-if="activeSubsection === 'overview'" class="space-y-6">
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300 leading-relaxed">
                        The Rental Services Module provides a comprehensive system for managing vehicle rentals, venue bookings, and hostel accommodations with built-in conflict detection, real-time availability checking, and complete status tracking[cite: 5].
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-3.5 flex items-center gap-1.5">
                                <LuBlocks class="w-3.5 h-3.5" /> Module Components
                            </h3>
                            <ul class="space-y-2.5 text-xs font-medium text-slate-700 dark:text-slate-300">
                                <li class="flex items-start gap-2.5"><LuCar class="w-4 h-4 text-indigo-500 shrink-0 mt-0.5" /> <span><span class="font-semibold text-slate-900 dark:text-slate-200">Vehicle Rental System</span> - Manage vehicle bookings (Innova, Pickup, Van, SUV)[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuBuilding class="w-4 h-4 text-indigo-500 shrink-0 mt-0.5" /> <span><span class="font-semibold text-slate-900 dark:text-slate-200">Venue Rental System</span> - Manage venue bookings (Plenary Hall, Training Room, Multi-Purpose Hall)[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuMapPin class="w-4 h-4 text-indigo-500 shrink-0 mt-0.5" /> <span><span class="font-semibold text-slate-900 dark:text-slate-200">Hostel Management</span> - Structure ready for hostel accommodations (future implementation)[cite: 5].</span></li>
                            </ul>
                        </div>

                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-3.5 flex items-center gap-1.5">
                                <LuSparkles class="w-3.5 h-3.5" /> Key Capabilities
                            </h3>
                            <ul class="space-y-2.5 text-xs font-medium text-slate-700 dark:text-slate-300">
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" /> <span>Automatic conflict detection prevents double-booking[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" /> <span>Real-time availability checking before submission[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" /> <span>Complete CRUD operations (Create, Read, Update, Delete)[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" /> <span>Full REST API with 14 endpoints[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" /> <span>Guest forms for public booking without authentication[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" /> <span>Comprehensive validation and error handling[cite: 5].</span></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Features Section -->
                <div v-if="activeSubsection === 'features'" class="space-y-4">
                    <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">Core Features</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-blue-50/50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 rounded-xl p-5 shadow-sm">
                            <h5 class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-2 flex items-center gap-2">
                                <LuShieldCheck class="w-4 h-4" /> Conflict Detection
                            </h5>
                            <p class="text-[0.65rem] font-medium text-blue-700 dark:text-blue-400/90 leading-relaxed">
                                Sophisticated date range logic prevents overlapping bookings for the same resource[cite: 5]. Only approved and pending bookings are considered as blocking[cite: 5].
                            </p>
                        </div>

                        <div class="bg-emerald-50/50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 rounded-xl p-5 shadow-sm">
                            <h5 class="text-xs font-semibold text-emerald-800 dark:text-emerald-300 mb-2 flex items-center gap-2">
                                <LuClock class="w-4 h-4" /> Real-Time Availability
                            </h5>
                            <p class="text-[0.65rem] font-medium text-emerald-700 dark:text-emerald-400/90 leading-relaxed">
                                Forms check availability instantly before submission[cite: 5]. Submit button remains disabled until the requested dates are confirmed available[cite: 5].
                            </p>
                        </div>

                        <div class="bg-purple-50/50 dark:bg-purple-500/10 border border-purple-100 dark:border-purple-500/20 rounded-xl p-5 shadow-sm">
                            <h5 class="text-xs font-semibold text-purple-800 dark:text-purple-300 mb-2 flex items-center gap-2">
                                <LuListChecks class="w-4 h-4" /> Status Tracking
                            </h5>
                            <p class="text-[0.65rem] font-medium text-purple-700 dark:text-purple-400/90 leading-relaxed">
                                Each booking moves through a lifecycle: pending → approved/rejected → completed/cancelled, with full audit trail via soft deletes[cite: 5].
                            </p>
                        </div>

                        <div class="bg-amber-50/50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-500/20 rounded-xl p-5 shadow-sm">
                            <h5 class="text-xs font-semibold text-amber-800 dark:text-amber-300 mb-2 flex items-center gap-2">
                                <LuCheckSquare class="w-4 h-4" /> Form Validation
                            </h5>
                            <p class="text-[0.65rem] font-medium text-amber-700 dark:text-amber-400/90 leading-relaxed">
                                Multi-layer validation: dates cannot be in the past, times must have end after start, contact numbers must match specific formats, and more[cite: 5].
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Rentals Section -->
                <div v-if="activeSubsection === 'vehicleRentals'" class="space-y-6">
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Manage vehicle bookings for your organization's transportation needs[cite: 5].</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-3.5">Available Types</h3>
                            <ul class="space-y-2 text-xs font-medium text-slate-700 dark:text-slate-300">
                                <li class="flex items-center gap-2"><code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold text-indigo-600 dark:text-indigo-400">innova</code> Toyota Innova[cite: 5]</li>
                                <li class="flex items-center gap-2"><code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold text-indigo-600 dark:text-indigo-400">pickup</code> Pickup Truck[cite: 5]</li>
                                <li class="flex items-center gap-2"><code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold text-indigo-600 dark:text-indigo-400">van</code> Van[cite: 5]</li>
                                <li class="flex items-center gap-2"><code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold text-indigo-600 dark:text-indigo-400">suv</code> SUV[cite: 5]</li>
                            </ul>
                        </div>

                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-3.5">Booking Statuses</h3>
                            <ul class="space-y-2 text-xs font-medium text-slate-700 dark:text-slate-300">
                                <li class="flex items-center gap-2"><code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold text-amber-500">pending</code> Awaiting approval[cite: 5]</li>
                                <li class="flex items-center gap-2"><code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold text-emerald-500">approved</code> Confirmed and ready[cite: 5]</li>
                                <li class="flex items-center gap-2"><code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold text-rose-500">rejected</code> Request denied[cite: 5]</li>
                                <li class="flex items-center gap-2"><code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold text-blue-500">completed</code> Rental finished[cite: 5]</li>
                                <li class="flex items-center gap-2"><code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold text-slate-500">cancelled</code> Cancelled booking[cite: 5]</li>
                            </ul>
                        </div>
                    </div>

                    <div class="bg-indigo-50/50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 rounded-xl p-5 shadow-sm">
                        <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 mb-2 flex items-center gap-1.5">
                            <LuFileText class="w-3.5 h-3.5" /> Information Required
                        </h4>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-400 mb-3">Navigate to <code class="font-mono bg-white dark:bg-slate-800 px-1 py-0.5 rounded shadow-sm text-indigo-500">/rental/vehicle</code> to access the guest form. No authentication required[cite: 5].</p>
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-medium text-slate-700 dark:text-slate-300">
                            <li class="flex items-start gap-2"><LuCheckCircle2 class="w-3.5 h-3.5 text-indigo-400 shrink-0 mt-0.5" /> Vehicle type (select from dropdown)[cite: 5]</li>
                            <li class="flex items-start gap-2"><LuCheckCircle2 class="w-3.5 h-3.5 text-indigo-400 shrink-0 mt-0.5" /> Date from and Date to (today or future)[cite: 5]</li>
                            <li class="flex items-start gap-2"><LuCheckCircle2 class="w-3.5 h-3.5 text-indigo-400 shrink-0 mt-0.5" /> Time from and Time to (end after start)[cite: 5]</li>
                            <li class="flex items-start gap-2"><LuCheckCircle2 class="w-3.5 h-3.5 text-indigo-400 shrink-0 mt-0.5" /> Purpose of rental (max 500 characters)[cite: 5]</li>
                            <li class="flex items-start gap-2"><LuCheckCircle2 class="w-3.5 h-3.5 text-indigo-400 shrink-0 mt-0.5" /> Requested by (name, max 255 characters)[cite: 5]</li>
                            <li class="flex items-start gap-2"><LuCheckCircle2 class="w-3.5 h-3.5 text-indigo-400 shrink-0 mt-0.5" /> Contact number (valid format required)[cite: 5]</li>
                            <li class="flex items-start gap-2"><LuCheckCircle2 class="w-3.5 h-3.5 text-indigo-400 shrink-0 mt-0.5" /> Notes (optional, max 1000 characters)[cite: 5]</li>
                        </ul>
                    </div>
                </div>

                <!-- Venue Rentals Section -->
                <div v-if="activeSubsection === 'venueRentals'" class="space-y-6">
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Book venues for events, conferences, meetings, and training sessions[cite: 5].</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-3.5">Available Types</h3>
                            <ul class="space-y-2 text-xs font-medium text-slate-700 dark:text-slate-300">
                                <li class="flex items-center gap-2"><code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold text-indigo-600 dark:text-indigo-400">plenary</code> Plenary Hall (large capacity)[cite: 5]</li>
                                <li class="flex items-center gap-2"><code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold text-indigo-600 dark:text-indigo-400">training_room</code> Training Room (medium capacity)[cite: 5]</li>
                                <li class="flex items-center gap-2"><code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold text-indigo-600 dark:text-indigo-400">mph</code> Multi-Purpose Hall (flexible capacity)[cite: 5]</li>
                            </ul>
                        </div>

                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-3.5">Attendee Capacity</h3>
                            <p class="text-xs font-medium text-slate-700 dark:text-slate-300 leading-relaxed">
                                Expected attendees must be between 1 and 5000[cite: 5]. This helps administrators allocate the appropriate venue[cite: 5].
                            </p>
                        </div>
                    </div>

                    <div class="bg-indigo-50/50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 rounded-xl p-5 shadow-sm">
                        <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 mb-2 flex items-center gap-1.5">
                            <LuFileText class="w-3.5 h-3.5" /> Information Required
                        </h4>
                        <p class="text-xs font-medium text-slate-600 dark:text-slate-400 mb-3">Navigate to <code class="font-mono bg-white dark:bg-slate-800 px-1 py-0.5 rounded shadow-sm text-indigo-500">/rental/venue</code> to access the guest form. No authentication required[cite: 5].</p>
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-medium text-slate-700 dark:text-slate-300">
                            <li class="flex items-start gap-2"><LuCheckCircle2 class="w-3.5 h-3.5 text-indigo-400 shrink-0 mt-0.5" /> Venue type (select from dropdown)[cite: 5]</li>
                            <li class="flex items-start gap-2"><LuCheckCircle2 class="w-3.5 h-3.5 text-indigo-400 shrink-0 mt-0.5" /> Date from and Date to (today or future)[cite: 5]</li>
                            <li class="flex items-start gap-2"><LuCheckCircle2 class="w-3.5 h-3.5 text-indigo-400 shrink-0 mt-0.5" /> Time from and Time to (end after start)[cite: 5]</li>
                            <li class="flex items-start gap-2"><LuCheckCircle2 class="w-3.5 h-3.5 text-indigo-400 shrink-0 mt-0.5" /> Expected attendees (1-5000 people)[cite: 5]</li>
                            <li class="flex items-start gap-2"><LuCheckCircle2 class="w-3.5 h-3.5 text-indigo-400 shrink-0 mt-0.5" /> Event name (max 255 characters)[cite: 5]</li>
                            <li class="flex items-start gap-2"><LuCheckCircle2 class="w-3.5 h-3.5 text-indigo-400 shrink-0 mt-0.5" /> Requested by & Contact number[cite: 5]</li>
                            <li class="flex items-start gap-2"><LuCheckCircle2 class="w-3.5 h-3.5 text-indigo-400 shrink-0 mt-0.5" /> Notes (optional, max 1000 characters)[cite: 5]</li>
                        </ul>
                    </div>
                </div>

                <!-- API Endpoints Section -->
                <div v-if="activeSubsection === 'api'" class="space-y-6">
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">The module provides a comprehensive REST API with 14 endpoints for programmatic access[cite: 5].</p>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-4 flex items-center gap-1.5">
                                <LuNetwork class="w-3.5 h-3.5" /> Vehicle Endpoints (7)
                            </h3>
                            <ul class="space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                                <li>
                                    <code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-sky-500">GET</code> <code class="font-mono text-[0.7rem] text-slate-800 dark:text-slate-200">/api/rental/vehicles</code><br/>
                                    <span class="text-[0.65rem] text-slate-500 ml-10 block mt-1">List all vehicle rentals with pagination[cite: 5]</span>
                                </li>
                                <li>
                                    <code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-emerald-500">POST</code> <code class="font-mono text-[0.7rem] text-slate-800 dark:text-slate-200">/api/rental/vehicles</code><br/>
                                    <span class="text-[0.65rem] text-slate-500 ml-10 block mt-1">Create new vehicle rental (checks for conflicts)[cite: 5]</span>
                                </li>
                                <li>
                                    <code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-sky-500">GET</code> <code class="font-mono text-[0.7rem] text-slate-800 dark:text-slate-200">/api/rental/vehicles/{id}</code><br/>
                                    <span class="text-[0.65rem] text-slate-500 ml-10 block mt-1">Get specific rental details[cite: 5]</span>
                                </li>
                                <li>
                                    <code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-amber-500">PUT</code> <code class="font-mono text-[0.7rem] text-slate-800 dark:text-slate-200">/api/rental/vehicles/{id}</code><br/>
                                    <span class="text-[0.65rem] text-slate-500 ml-10 block mt-1">Update rental details[cite: 5]</span>
                                </li>
                                <li>
                                    <code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-rose-500">DEL</code> <code class="font-mono text-[0.7rem] text-slate-800 dark:text-slate-200">/api/rental/vehicles/{id}</code><br/>
                                    <span class="text-[0.65rem] text-slate-500 ml-10 block mt-1">Delete rental (soft delete)[cite: 5]</span>
                                </li>
                                <li>
                                    <code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-sky-500">GET</code> <code class="font-mono text-[0.7rem] text-slate-800 dark:text-slate-200">/api/rental/vehicles/check-availability...</code><br/>
                                    <span class="text-[0.65rem] text-slate-500 ml-10 block mt-1">Check if dates are available[cite: 5]</span>
                                </li>
                                <li>
                                    <code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-sky-500">GET</code> <code class="font-mono text-[0.7rem] text-slate-800 dark:text-slate-200">/api/rental/vehicles/by-type/{type}</code><br/>
                                    <span class="text-[0.65rem] text-slate-500 ml-10 block mt-1">Filter rentals by vehicle type[cite: 5]</span>
                                </li>
                            </ul>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                                <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-1.5">
                                    <LuBuilding class="w-3.5 h-3.5" /> Venue Endpoints (7)
                                </h3>
                                <p class="text-xs font-medium text-slate-600 dark:text-slate-400 leading-relaxed">
                                    Identical structure to vehicle endpoints, adapted for venues: <code class="font-mono text-indigo-500 bg-white dark:bg-slate-800 px-1 py-0.5 rounded border border-slate-200 dark:border-slate-700">/api/rental/venues</code> with the same HTTP methods and functionality[cite: 5].
                                </p>
                            </div>

                            <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                                <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-1.5">
                                    <LuZap class="w-3.5 h-3.5" /> HTTP Status Codes
                                </h3>
                                <ul class="space-y-2.5 text-xs font-medium text-slate-700 dark:text-slate-300">
                                    <li class="flex items-center gap-2"><code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold text-emerald-600 dark:text-emerald-400 shadow-sm">200</code> OK (GET, PUT, DELETE)[cite: 5]</li>
                                    <li class="flex items-center gap-2"><code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold text-emerald-600 dark:text-emerald-400 shadow-sm">201</code> Created (successful POST)[cite: 5]</li>
                                    <li class="flex items-center gap-2"><code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold text-rose-600 dark:text-rose-400 shadow-sm">404</code> Not Found (invalid resource ID)[cite: 5]</li>
                                    <li class="flex items-center gap-2"><code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold text-amber-600 dark:text-amber-400 shadow-sm">409</code> Conflict (double-booking attempt)[cite: 5]</li>
                                    <li class="flex items-center gap-2"><code class="px-1.5 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold text-amber-600 dark:text-amber-400 shadow-sm">422</code> Unprocessable Entity (validation)[cite: 5]</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Validation Rules Section -->
                <div v-if="activeSubsection === 'validation'" class="space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-4 flex items-center gap-1.5">
                                <LuCar class="w-3.5 h-3.5" /> Vehicle Validation
                            </h3>
                            <ul class="space-y-3 text-xs font-medium text-slate-600 dark:text-slate-400">
                                <li><code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-600 dark:text-indigo-400">vehicle_type</code> Required, must be: innova, pickup, van, or suv[cite: 5]</li>
                                <li><code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-600 dark:text-indigo-400">date_from</code> Required, must be today or future[cite: 5]</li>
                                <li><code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-600 dark:text-indigo-400">date_to</code> Required, must be same or after date_from[cite: 5]</li>
                                <li><code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-600 dark:text-indigo-400">time_from</code> Required, 24-hour format (HH:mm)[cite: 5]</li>
                                <li><code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-600 dark:text-indigo-400">time_to</code> Required, must be after time_from[cite: 5]</li>
                                <li><code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-600 dark:text-indigo-400">purpose</code> Required, max 500 characters[cite: 5]</li>
                                <li><code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-600 dark:text-indigo-400">requested_by</code> Required, max 255 characters[cite: 5]</li>
                                <li><code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-600 dark:text-indigo-400">contact_number</code> Required, valid phone number format[cite: 5]</li>
                                <li><code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-slate-500">notes</code> Optional, max 1000 characters[cite: 5]</li>
                            </ul>
                        </div>

                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-4 flex items-center gap-1.5">
                                <LuBuilding class="w-3.5 h-3.5" /> Venue Validation
                            </h3>
                            <ul class="space-y-3 text-xs font-medium text-slate-600 dark:text-slate-400">
                                <li><code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-600 dark:text-indigo-400">venue_type</code> Required, must be: plenary, training_room, or mph[cite: 5]</li>
                                <li><code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-600 dark:text-indigo-400">date_from</code> Required, must be today or future[cite: 5]</li>
                                <li><code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-600 dark:text-indigo-400">date_to</code> Required, must be same or after date_from[cite: 5]</li>
                                <li><code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-600 dark:text-indigo-400">time_from</code> Required, 24-hour format (HH:mm)[cite: 5]</li>
                                <li><code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-600 dark:text-indigo-400">time_to</code> Required, must be after time_from[cite: 5]</li>
                                <li><code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-600 dark:text-indigo-400">expected_attendees</code> Required, must be 1-5000[cite: 5]</li>
                                <li><code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-600 dark:text-indigo-400">event_name</code> Required, max 255 characters[cite: 5]</li>
                                <li><code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-600 dark:text-indigo-400">requested_by</code> Required, max 255 characters[cite: 5]</li>
                                <li><code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-600 dark:text-indigo-400">contact_number</code> Required, valid phone number format[cite: 5]</li>
                                <li><code class="px-1.5 py-0.5 mr-1.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-slate-500">notes</code> Optional, max 1000 characters[cite: 5]</li>
                            </ul>
                        </div>
                    </div>

                    <div class="bg-amber-50/50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-500/20 rounded-xl p-5 shadow-sm">
                        <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400 mb-3 flex items-center gap-1.5">
                            <LuAlertTriangle class="w-3.5 h-3.5" /> Common Validation Errors
                        </h4>
                        <ul class="space-y-2 text-xs font-medium text-slate-700 dark:text-slate-300 ml-1">
                            <li class="flex items-start gap-2"><LuCheckCircle2 class="w-3.5 h-3.5 text-amber-500 shrink-0 mt-0.5" /> Past dates are rejected - use today or future dates only[cite: 5].</li>
                            <li class="flex items-start gap-2"><LuCheckCircle2 class="w-3.5 h-3.5 text-amber-500 shrink-0 mt-0.5" /> End time must be after start time[cite: 5].</li>
                            <li class="flex items-start gap-2"><LuCheckCircle2 class="w-3.5 h-3.5 text-amber-500 shrink-0 mt-0.5" /> Contact number must match phone format (digits, +, -, spaces, parentheses)[cite: 5].</li>
                            <li class="flex items-start gap-2"><LuCheckCircle2 class="w-3.5 h-3.5 text-amber-500 shrink-0 mt-0.5" /> Text fields must not exceed maximum character limits[cite: 5].</li>
                        </ul>
                    </div>
                </div>

                <!-- Examples Section -->
                <div v-if="activeSubsection === 'examples'" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        <div class="space-y-2">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">Vehicle Rental Example[cite: 5]</h4>
                            <div class="rounded-xl overflow-hidden shadow-inner border border-slate-200 dark:border-[#2d2d2d] bg-[#1e1e1e]">
                                <div class="flex items-center px-4 py-2.5 bg-[#2d2d2d] border-b border-[#1e1e1e]">
                                    <div class="flex gap-1.5">
                                        <div class="w-2.5 h-2.5 rounded-full bg-[#ff5f56]"></div>
                                        <div class="w-2.5 h-2.5 rounded-full bg-[#ffbd2e]"></div>
                                        <div class="w-2.5 h-2.5 rounded-full bg-[#27c93f]"></div>
                                    </div>
                                    <div class="ml-4 text-[0.65rem] font-medium text-[#858585] font-mono tracking-wide">payload.json</div>
                                </div>
                                <pre class="p-4 text-[0.75rem] font-mono overflow-x-auto leading-loose vscode-scrollbar text-[#d4d4d4]"><code class="!bg-transparent !p-0 !border-0 !shadow-none !text-inherit">{
  <span class="text-[#9cdcfe]">"vehicle_type"</span>: <span class="text-[#ce9178]">"innova"</span>,
  <span class="text-[#9cdcfe]">"date_from"</span>: <span class="text-[#ce9178]">"2026-02-20"</span>,
  <span class="text-[#9cdcfe]">"date_to"</span>: <span class="text-[#ce9178]">"2026-02-22"</span>,
  <span class="text-[#9cdcfe]">"time_from"</span>: <span class="text-[#ce9178]">"08:00"</span>,
  <span class="text-[#9cdcfe]">"time_to"</span>: <span class="text-[#ce9178]">"17:00"</span>,
  <span class="text-[#9cdcfe]">"purpose"</span>: <span class="text-[#ce9178]">"Company team building"</span>,
  <span class="text-[#9cdcfe]">"requested_by"</span>: <span class="text-[#ce9178]">"John Doe"</span>,
  <span class="text-[#9cdcfe]">"contact_number"</span>: <span class="text-[#ce9178]">"09171234567"</span>,
  <span class="text-[#9cdcfe]">"notes"</span>: <span class="text-[#ce9178]">"Need fuel card"</span>
}</code></pre>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">Venue Rental Example[cite: 5]</h4>
                            <div class="rounded-xl overflow-hidden shadow-inner border border-slate-200 dark:border-[#2d2d2d] bg-[#1e1e1e]">
                                <div class="flex items-center px-4 py-2.5 bg-[#2d2d2d] border-b border-[#1e1e1e]">
                                    <div class="flex gap-1.5">
                                        <div class="w-2.5 h-2.5 rounded-full bg-[#ff5f56]"></div>
                                        <div class="w-2.5 h-2.5 rounded-full bg-[#ffbd2e]"></div>
                                        <div class="w-2.5 h-2.5 rounded-full bg-[#27c93f]"></div>
                                    </div>
                                    <div class="ml-4 text-[0.65rem] font-medium text-[#858585] font-mono tracking-wide">payload.json</div>
                                </div>
                                <pre class="p-4 text-[0.75rem] font-mono overflow-x-auto leading-loose vscode-scrollbar text-[#d4d4d4]"><code class="!bg-transparent !p-0 !border-0 !shadow-none !text-inherit">{
  <span class="text-[#9cdcfe]">"venue_type"</span>: <span class="text-[#ce9178]">"plenary"</span>,
  <span class="text-[#9cdcfe]">"date_from"</span>: <span class="text-[#ce9178]">"2026-02-25"</span>,
  <span class="text-[#9cdcfe]">"date_to"</span>: <span class="text-[#ce9178]">"2026-02-25"</span>,
  <span class="text-[#9cdcfe]">"time_from"</span>: <span class="text-[#ce9178]">"09:00"</span>,
  <span class="text-[#9cdcfe]">"time_to"</span>: <span class="text-[#ce9178]">"17:00"</span>,
  <span class="text-[#9cdcfe]">"expected_attendees"</span>: <span class="text-[#b5cea8]">250</span>,
  <span class="text-[#9cdcfe]">"event_name"</span>: <span class="text-[#ce9178]">"Annual Company Conference"</span>,
  <span class="text-[#9cdcfe]">"requested_by"</span>: <span class="text-[#ce9178]">"Jane Smith"</span>,
  <span class="text-[#9cdcfe]">"contact_number"</span>: <span class="text-[#ce9178]">"09181234567"</span>,
  <span class="text-[#9cdcfe]">"notes"</span>: <span class="text-[#ce9178]">"Need projector setup"</span>
}</code></pre>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">Availability Check Response[cite: 5]</h4>
                            <div class="rounded-xl overflow-hidden shadow-inner border border-slate-200 dark:border-[#2d2d2d] bg-[#1e1e1e]">
                                <div class="flex items-center px-4 py-2.5 bg-[#2d2d2d] border-b border-[#1e1e1e]">
                                    <div class="flex gap-1.5">
                                        <div class="w-2.5 h-2.5 rounded-full bg-[#ff5f56]"></div>
                                        <div class="w-2.5 h-2.5 rounded-full bg-[#ffbd2e]"></div>
                                        <div class="w-2.5 h-2.5 rounded-full bg-[#27c93f]"></div>
                                    </div>
                                    <div class="ml-4 text-[0.65rem] font-medium text-[#858585] font-mono tracking-wide">response.json</div>
                                </div>
                                <pre class="p-4 text-[0.75rem] font-mono overflow-x-auto leading-loose vscode-scrollbar text-[#d4d4d4]"><code class="!bg-transparent !p-0 !border-0 !shadow-none !text-inherit">{
  <span class="text-[#9cdcfe]">"available"</span>: <span class="text-[#569cd6]">true</span>,
  <span class="text-[#9cdcfe]">"vehicle_type"</span>: <span class="text-[#ce9178]">"innova"</span>,
  <span class="text-[#9cdcfe]">"date_from"</span>: <span class="text-[#ce9178]">"2026-02-20"</span>,
  <span class="text-[#9cdcfe]">"date_to"</span>: <span class="text-[#ce9178]">"2026-02-22"</span>
}</code></pre>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">Conflict Error Response[cite: 5]</h4>
                            <div class="rounded-xl overflow-hidden shadow-inner border border-slate-200 dark:border-[#2d2d2d] bg-[#1e1e1e]">
                                <div class="flex items-center px-4 py-2.5 bg-[#2d2d2d] border-b border-[#1e1e1e]">
                                    <div class="flex gap-1.5">
                                        <div class="w-2.5 h-2.5 rounded-full bg-[#ff5f56]"></div>
                                        <div class="w-2.5 h-2.5 rounded-full bg-[#ffbd2e]"></div>
                                        <div class="w-2.5 h-2.5 rounded-full bg-[#27c93f]"></div>
                                    </div>
                                    <div class="ml-4 text-[0.65rem] font-medium text-[#858585] font-mono tracking-wide">response.json</div>
                                </div>
                                <pre class="p-4 text-[0.75rem] font-mono overflow-x-auto leading-loose vscode-scrollbar text-[#d4d4d4]"><code class="!bg-transparent !p-0 !border-0 !shadow-none !text-inherit">{
  <span class="text-[#9cdcfe]">"message"</span>: <span class="text-[#ce9178]">"The selected vehicle is not available for the requested dates and time."</span>,
  <span class="text-[#9cdcfe]">"error"</span>: <span class="text-[#ce9178]">"conflict"</span>
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Testing Section -->
                <div v-if="activeSubsection === 'testing'" class="space-y-6">
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">The module includes comprehensive test coverage with 25+ automated tests[cite: 5].</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-3.5 flex items-center gap-1.5">
                                <LuShieldCheck class="w-3.5 h-3.5" /> Test Coverage
                            </h3>
                            <ul class="space-y-2.5 text-xs font-medium text-slate-700 dark:text-slate-300">
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" /> <span>Create rentals with valid and invalid data[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" /> <span>Validation of all required fields[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" /> <span>Rejection of past dates[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" /> <span>Conflict detection for overlapping bookings[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" /> <span>Real-time availability checking[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" /> <span>CRUD operations (Create, Read, Update, Delete)[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" /> <span>Filtering by vehicle/venue type[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" /> <span>Pagination of results[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" /> <span>Contact number format validation[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" /> <span>Time range validation (end > start)[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" /> <span>Multi-resource booking scenarios[cite: 5].</span></li>
                            </ul>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-indigo-50/50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 rounded-xl p-5 shadow-sm">
                                <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 mb-3.5 flex items-center gap-1.5">
                                    <LuTerminal class="w-3.5 h-3.5" /> Running Tests
                                </h3>
                                <p class="text-xs font-medium text-slate-700 dark:text-slate-300 mb-2">Execute the following command from your terminal[cite: 5]:</p>
                                <div class="rounded-xl overflow-hidden shadow-inner border border-slate-200 dark:border-[#2d2d2d] bg-[#1e1e1e]">
                                    <pre class="p-4 text-[0.7rem] font-mono overflow-x-auto leading-loose vscode-scrollbar text-[#d4d4d4]"><code class="!bg-transparent !p-0 !border-0 !shadow-none !text-inherit"><span class="text-[#dcdcaa]">php</span> artisan test tests/Feature/RentalServiceApiTest.php</code></pre>
                                </div>
                            </div>

                            <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                                <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-3.5 flex items-center gap-1.5">
                                    <LuCheckSquare class="w-3.5 h-3.5" /> Expected Results
                                </h3>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-lg p-3 text-center">
                                        <p class="text-2xl font-black text-slate-700 dark:text-slate-300 mb-0.5">25+</p>
                                        <p class="text-[0.6rem] font-semibold uppercase tracking-widest text-slate-400">Total Tests[cite: 5]</p>
                                    </div>
                                    <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-lg p-3 text-center">
                                        <p class="text-2xl font-black text-emerald-500 dark:text-emerald-400 mb-0.5">100%</p>
                                        <p class="text-[0.6rem] font-semibold uppercase tracking-widest text-slate-400">Pass Rate[cite: 5]</p>
                                    </div>
                                    <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-lg p-3 text-center">
                                        <p class="text-xl font-black text-slate-700 dark:text-slate-300 mb-1 mt-1">~10-15s</p>
                                        <p class="text-[0.6rem] font-semibold uppercase tracking-widest text-slate-400">Execution Time[cite: 5]</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Future Enhancements Section -->
                <div v-if="activeSubsection === 'future'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="bg-indigo-50/50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 rounded-xl p-5 shadow-sm">
                            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 mb-3.5 flex items-center gap-1.5">
                                <LuBuilding class="w-3.5 h-3.5" /> Hostel Module (Ready)
                            </h3>
                            <p class="text-xs font-medium text-slate-700 dark:text-slate-300 mb-3 leading-relaxed">
                                The <code class="font-mono text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 px-1 py-0.5 rounded shadow-sm border border-slate-200 dark:border-slate-700">RentalHostel</code> model is already created and ready for integration[cite: 5]. When ready to activate:
                            </p>
                            <ul class="space-y-2.5 text-xs font-medium text-slate-700 dark:text-slate-300 ml-1">
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5" /> <span>Create HostelRentalFormGuest.vue component[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5" /> <span>Create RentalHostelRepository and RentalHostelController[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5" /> <span>Create request validation classes[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5" /> <span>Add routes to routes/rental.php[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5" /> <span>Add comprehensive tests[cite: 5].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5" /> <span>Add hostel link to Welcome page[cite: 5].</span></li>
                            </ul>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                                <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-3.5 flex items-center gap-1.5">
                                    <LuCalendarDays class="w-3.5 h-3.5" /> Calendar Module (Planned)
                                </h3>
                                <p class="text-xs font-medium text-slate-700 dark:text-slate-300 mb-3 leading-relaxed">
                                    A unified calendar view showing[cite: 5]:
                                </p>
                                <ul class="space-y-2 text-xs font-medium text-slate-700 dark:text-slate-300 ml-1">
                                    <li class="flex items-center gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-slate-400 shrink-0" /> Vehicle bookings by type and date[cite: 5].</li>
                                    <li class="flex items-center gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-slate-400 shrink-0" /> Venue bookings by type and date[cite: 5].</li>
                                    <li class="flex items-center gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-slate-400 shrink-0" /> Equipment usage & Lab reservations (if integrated)[cite: 5].</li>
                                    <li class="flex items-center gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-slate-400 shrink-0" /> Date range filtering & Color-coding[cite: 5].</li>
                                </ul>
                            </div>

                            <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                                <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-3.5 flex items-center gap-1.5">
                                    <LuSettings2 class="w-3.5 h-3.5" /> Admin Features (Recommended)
                                </h3>
                                <ul class="space-y-2 text-xs font-medium text-slate-700 dark:text-slate-300 ml-1">
                                    <li class="flex items-center gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-slate-400 shrink-0" /> Dashboard showing all pending bookings[cite: 5].</li>
                                    <li class="flex items-center gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-slate-400 shrink-0" /> Approve/reject functionality with notes[cite: 5].</li>
                                    <li class="flex items-center gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-slate-400 shrink-0" /> Analytics, reports, & status transition UI[cite: 5].</li>
                                    <li class="flex items-center gap-2.5"><LuCheckCircle2 class="w-4 h-4 text-slate-400 shrink-0" /> Email/SMS notifications for status changes[cite: 5].</li>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>

    </TopicLayout>
</template>

<style scoped>
/* VS Code Scrollbar Replication */
.vscode-scrollbar::-webkit-scrollbar {
    height: 10px;
    width: 10px;
}
.vscode-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.vscode-scrollbar::-webkit-scrollbar-thumb {
    background: #424242;
    border: 2px solid #1e1e1e;
    border-radius: 6px;
}
.vscode-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #4f4f4f;
}

/* Force strip global backgrounds applied to code tags */
pre code {
    background-color: transparent !important;
    padding: 0 !important;
    border: none !important;
    box-shadow: none !important;
    color: inherit !important;
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>