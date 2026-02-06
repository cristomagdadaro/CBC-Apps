<script>
import { ref } from 'vue';

export default {
    name: 'RentalServicesTopic',
    setup() {
        const activeSubsection = ref('overview');

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

        return {
            activeSubsection,
            subsections
        };
    },
};
</script>

<template>
    <div>
        <!-- Subsection Navigation -->
        <div class="mb-6 flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-700 pb-4">
            <button
                v-for="(label, id) in subsections"
                :key="id"
                @click="activeSubsection = id"
                :class="[
                    'px-3 py-2 text-sm font-medium rounded-md transition-colors',
                    activeSubsection === id
                        ? 'bg-blue-600 text-white'
                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'
                ]"
            >
                {{ label }}
            </button>
        </div>

        <!-- Overview Section -->
        <div v-if="activeSubsection === 'overview'" class="space-y-4">
            <p>The <strong>Rental Services Module</strong> provides a comprehensive system for managing vehicle rentals, venue bookings, and hostel accommodations with built-in conflict detection, real-time availability checking, and complete status tracking.</p>
            
            <h4 class="font-bold text-gray-900 dark:text-gray-100 mt-4">Module Components:</h4>
            <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
                <li><strong>Vehicle Rental System</strong> - Manage vehicle bookings (Innova, Pickup, Van, SUV)</li>
                <li><strong>Venue Rental System</strong> - Manage venue bookings (Plenary Hall, Training Room, Multi-Purpose Hall)</li>
                <li><strong>Hostel Management</strong> - Structure ready for hostel accommodations (future implementation)</li>
            </ul>

            <h4 class="font-bold text-gray-900 dark:text-gray-100 mt-4">Key Capabilities:</h4>
            <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
                <li>Automatic conflict detection prevents double-booking</li>
                <li>Real-time availability checking before submission</li>
                <li>Complete CRUD operations (Create, Read, Update, Delete)</li>
                <li>Full REST API with 14 endpoints</li>
                <li>Guest forms for public booking without authentication</li>
                <li>Comprehensive validation and error handling</li>
            </ul>
        </div>

        <!-- Features Section -->
        <div v-if="activeSubsection === 'features'" class="space-y-4">
            <h4 class="font-bold text-gray-900 dark:text-gray-100">Core Features</h4>
            
            <div class="space-y-3">
                <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
                    <h5 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">Conflict Detection</h5>
                    <p class="text-sm text-blue-800 dark:text-blue-200">Sophisticated date range logic prevents overlapping bookings for the same resource. Only approved and pending bookings are considered as blocking.</p>
                </div>

                <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg">
                    <h5 class="font-semibold text-green-900 dark:text-green-100 mb-2">Real-Time Availability</h5>
                    <p class="text-sm text-green-800 dark:text-green-200">Forms check availability instantly before submission. Submit button remains disabled until the requested dates are confirmed available.</p>
                </div>

                <div class="bg-purple-50 dark:bg-purple-900 p-4 rounded-lg">
                    <h5 class="font-semibold text-purple-900 dark:text-purple-100 mb-2">Status Tracking</h5>
                    <p class="text-sm text-purple-800 dark:text-purple-200">Each booking moves through a lifecycle: pending → approved/rejected → completed/cancelled, with full audit trail via soft deletes.</p>
                </div>

                <div class="bg-orange-50 dark:bg-orange-900 p-4 rounded-lg">
                    <h5 class="font-semibold text-orange-900 dark:text-orange-100 mb-2">Form Validation</h5>
                    <p class="text-sm text-orange-800 dark:text-orange-200">Multi-layer validation: dates cannot be in the past, times must have end after start, contact numbers must match specific formats, and more.</p>
                </div>
            </div>
        </div>

        <!-- Vehicle Rentals Section -->
        <div v-if="activeSubsection === 'vehicleRentals'" class="space-y-4">
            <p>Manage vehicle bookings for your organization's transportation needs.</p>

            <h4 class="font-bold text-gray-900 dark:text-gray-100">Available Vehicle Types:</h4>
            <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-300">
                <li><code>innova</code> - Toyota Innova</li>
                <li><code>pickup</code> - Pickup Truck</li>
                <li><code>van</code> - Van</li>
                <li><code>suv</code> - SUV</li>
            </ul>

            <h4 class="font-bold text-gray-900 dark:text-gray-100 mt-4">Vehicle Booking Form Access:</h4>
            <p class="text-gray-700 dark:text-gray-300">Navigate to <code>/rental/vehicle</code> to access the guest form. No authentication required - anyone can make a booking request.</p>

            <h4 class="font-bold text-gray-900 dark:text-gray-100 mt-4">Information Required:</h4>
            <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-300">
                <li>Vehicle type (select from dropdown)</li>
                <li>Date from and Date to (today or future dates only)</li>
                <li>Time from and Time to (end time must be after start time)</li>
                <li>Purpose of rental (max 500 characters)</li>
                <li>Requested by (name, max 255 characters)</li>
                <li>Contact number (valid format required)</li>
                <li>Notes (optional, max 1000 characters)</li>
            </ul>

            <h4 class="font-bold text-gray-900 dark:text-gray-100 mt-4">Booking Status:</h4>
            <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-300">
                <li><code>pending</code> - Awaiting approval</li>
                <li><code>approved</code> - Confirmed and ready</li>
                <li><code>rejected</code> - Request denied</li>
                <li><code>completed</code> - Rental finished</li>
                <li><code>cancelled</code> - Cancelled booking</li>
            </ul>
        </div>

        <!-- Venue Rentals Section -->
        <div v-if="activeSubsection === 'venueRentals'" class="space-y-4">
            <p>Book venues for events, conferences, meetings, and training sessions.</p>

            <h4 class="font-bold text-gray-900 dark:text-gray-100">Available Venue Types:</h4>
            <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-300">
                <li><code>plenary</code> - Plenary Hall (large capacity)</li>
                <li><code>training_room</code> - Training Room (medium capacity)</li>
                <li><code>mph</code> - Multi-Purpose Hall (flexible capacity)</li>
            </ul>

            <h4 class="font-bold text-gray-900 dark:text-gray-100 mt-4">Venue Booking Form Access:</h4>
            <p class="text-gray-700 dark:text-gray-300">Navigate to <code>/rental/venue</code> to access the guest form. No authentication required - anyone can make a booking request.</p>

            <h4 class="font-bold text-gray-900 dark:text-gray-100 mt-4">Information Required:</h4>
            <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-300">
                <li>Venue type (select from dropdown)</li>
                <li>Date from and Date to (today or future dates only)</li>
                <li>Time from and Time to (end time must be after start time)</li>
                <li>Expected attendees (1-5000 people)</li>
                <li>Event name (max 255 characters)</li>
                <li>Requested by (name, max 255 characters)</li>
                <li>Contact number (valid format required)</li>
                <li>Notes (optional, max 1000 characters)</li>
            </ul>

            <h4 class="font-bold text-gray-900 dark:text-gray-100 mt-4">Attendee Capacity:</h4>
            <p class="text-gray-700 dark:text-gray-300">Expected attendees must be between 1 and 5000. This helps administrators allocate the appropriate venue.</p>
        </div>

        <!-- API Endpoints Section -->
        <div v-if="activeSubsection === 'api'" class="space-y-4">
            <p>The module provides a comprehensive REST API with 14 endpoints for programmatic access.</p>

            <h4 class="font-bold text-gray-900 dark:text-gray-100">Vehicle Rental Endpoints (7):</h4>
            <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded">
                    <code class="text-blue-600 dark:text-blue-400">GET /api/rental/vehicles</code><br/>
                    <span class="text-xs">List all vehicle rentals with pagination</span>
                </div>
                <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded">
                    <code class="text-green-600 dark:text-green-400">POST /api/rental/vehicles</code><br/>
                    <span class="text-xs">Create new vehicle rental (checks for conflicts)</span>
                </div>
                <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded">
                    <code class="text-blue-600 dark:text-blue-400">GET /api/rental/vehicles/{id}</code><br/>
                    <span class="text-xs">Get specific rental details</span>
                </div>
                <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded">
                    <code class="text-yellow-600 dark:text-yellow-400">PUT /api/rental/vehicles/{id}</code><br/>
                    <span class="text-xs">Update rental details</span>
                </div>
                <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded">
                    <code class="text-red-600 dark:text-red-400">DELETE /api/rental/vehicles/{id}</code><br/>
                    <span class="text-xs">Delete rental (soft delete)</span>
                </div>
                <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded">
                    <code class="text-blue-600 dark:text-blue-400">GET /api/rental/vehicles/check-availability/{type}/{from}/{to}</code><br/>
                    <span class="text-xs">Check if dates are available</span>
                </div>
                <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded">
                    <code class="text-blue-600 dark:text-blue-400">GET /api/rental/vehicles/by-type/{vehicleType}</code><br/>
                    <span class="text-xs">Filter rentals by vehicle type</span>
                </div>
            </div>

            <h4 class="font-bold text-gray-900 dark:text-gray-100 mt-4">Venue Rental Endpoints (7):</h4>
            <p class="text-sm text-gray-700 dark:text-gray-300">Identical structure to vehicle endpoints, adapted for venues: <code>/api/rental/venues</code> with the same HTTP methods and functionality.</p>

            <h4 class="font-bold text-gray-900 dark:text-gray-100 mt-4">HTTP Status Codes:</h4>
            <ul class="list-disc list-inside space-y-1 text-sm text-gray-700 dark:text-gray-300">
                <li><code>200</code> - OK (GET, PUT, DELETE)</li>
                <li><code>201</code> - Created (successful POST)</li>
                <li><code>404</code> - Not Found (invalid resource ID)</li>
                <li><code>409</code> - Conflict (double-booking attempt)</li>
                <li><code>422</code> - Unprocessable Entity (validation errors)</li>
            </ul>
        </div>

        <!-- Validation Rules Section -->
        <div v-if="activeSubsection === 'validation'" class="space-y-4">
            <h4 class="font-bold text-gray-900 dark:text-gray-100">Vehicle Rental Validation:</h4>
            <ul class="list-disc list-inside space-y-1 text-sm text-gray-700 dark:text-gray-300">
                <li><code>vehicle_type</code> - Required, must be: innova, pickup, van, or suv</li>
                <li><code>date_from</code> - Required, must be today or future</li>
                <li><code>date_to</code> - Required, must be same or after date_from</li>
                <li><code>time_from</code> - Required, 24-hour format (HH:mm)</li>
                <li><code>time_to</code> - Required, must be after time_from</li>
                <li><code>purpose</code> - Required, max 500 characters</li>
                <li><code>requested_by</code> - Required, max 255 characters</li>
                <li><code>contact_number</code> - Required, valid phone number format</li>
                <li><code>notes</code> - Optional, max 1000 characters</li>
            </ul>

            <h4 class="font-bold text-gray-900 dark:text-gray-100 mt-4">Venue Rental Validation:</h4>
            <ul class="list-disc list-inside space-y-1 text-sm text-gray-700 dark:text-gray-300">
                <li><code>venue_type</code> - Required, must be: plenary, training_room, or mph</li>
                <li><code>date_from</code> - Required, must be today or future</li>
                <li><code>date_to</code> - Required, must be same or after date_from</li>
                <li><code>time_from</code> - Required, 24-hour format (HH:mm)</li>
                <li><code>time_to</code> - Required, must be after time_from</li>
                <li><code>expected_attendees</code> - Required, must be 1-5000</li>
                <li><code>event_name</code> - Required, max 255 characters</li>
                <li><code>requested_by</code> - Required, max 255 characters</li>
                <li><code>contact_number</code> - Required, valid phone number format</li>
                <li><code>notes</code> - Optional, max 1000 characters</li>
            </ul>

            <h4 class="font-bold text-gray-900 dark:text-gray-100 mt-4">Common Validation Errors:</h4>
            <ul class="list-disc list-inside space-y-1 text-sm text-gray-700 dark:text-gray-300">
                <li>Past dates are rejected - use today or future dates only</li>
                <li>End time must be after start time</li>
                <li>Contact number must match phone format (digits, +, -, spaces, parentheses)</li>
                <li>Text fields must not exceed maximum character limits</li>
            </ul>
        </div>

        <!-- Examples Section -->
        <div v-if="activeSubsection === 'examples'" class="space-y-4">
            <h4 class="font-bold text-gray-900 dark:text-gray-100">Vehicle Rental Example:</h4>
            <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded text-xs overflow-x-auto">
                <pre class="text-gray-800 dark:text-gray-200"><code>{
  "vehicle_type": "innova",
  "date_from": "2026-02-20",
  "date_to": "2026-02-22",
  "time_from": "08:00",
  "time_to": "17:00",
  "purpose": "Company team building",
  "requested_by": "John Doe",
  "contact_number": "09171234567",
  "notes": "Need fuel card"
}</code></pre>
            </div>

            <h4 class="font-bold text-gray-900 dark:text-gray-100 mt-4">Venue Rental Example:</h4>
            <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded text-xs overflow-x-auto">
                <pre class="text-gray-800 dark:text-gray-200"><code>{
  "venue_type": "plenary",
  "date_from": "2026-02-25",
  "date_to": "2026-02-25",
  "time_from": "09:00",
  "time_to": "17:00",
  "expected_attendees": 250,
  "event_name": "Annual Company Conference",
  "requested_by": "Jane Smith",
  "contact_number": "09181234567",
  "notes": "Need projector setup"
}</code></pre>
            </div>

            <h4 class="font-bold text-gray-900 dark:text-gray-100 mt-4">Availability Check Response:</h4>
            <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded text-xs overflow-x-auto">
                <pre class="text-gray-800 dark:text-gray-200"><code>{
  "available": true,
  "vehicle_type": "innova",
  "date_from": "2026-02-20",
  "date_to": "2026-02-22"
}</code></pre>
            </div>

            <h4 class="font-bold text-gray-900 dark:text-gray-100 mt-4">Conflict Error Response:</h4>
            <div class="bg-red-100 dark:bg-red-900 p-4 rounded text-xs overflow-x-auto">
                <pre class="text-red-800 dark:text-red-200"><code>{
  "message": "The selected vehicle is not available for the requested dates and time.",
  "error": "conflict"
}</code></pre>
            </div>
        </div>

        <!-- Testing Section -->
        <div v-if="activeSubsection === 'testing'" class="space-y-4">
            <p>The module includes comprehensive test coverage with 25+ automated tests.</p>

            <h4 class="font-bold text-gray-900 dark:text-gray-100">Test Coverage Includes:</h4>
            <ul class="list-disc list-inside space-y-1 text-sm text-gray-700 dark:text-gray-300">
                <li>✅ Create rentals with valid and invalid data</li>
                <li>✅ Validation of all required fields</li>
                <li>✅ Rejection of past dates</li>
                <li>✅ Conflict detection for overlapping bookings</li>
                <li>✅ Real-time availability checking</li>
                <li>✅ CRUD operations (Create, Read, Update, Delete)</li>
                <li>✅ Filtering by vehicle/venue type</li>
                <li>✅ Pagination of results</li>
                <li>✅ Contact number format validation</li>
                <li>✅ Time range validation (end > start)</li>
                <li>✅ Multi-resource booking scenarios</li>
            </ul>

            <h4 class="font-bold text-gray-900 dark:text-gray-100 mt-4">Running Tests:</h4>
            <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">Execute the following command from your terminal:</p>
            <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded text-sm">
                <code class="text-gray-800 dark:text-gray-200">php artisan test tests/Feature/RentalServiceApiTest.php</code>
            </div>

            <h4 class="font-bold text-gray-900 dark:text-gray-100 mt-4">Expected Results:</h4>
            <ul class="list-disc list-inside space-y-1 text-sm text-gray-700 dark:text-gray-300">
                <li>Total Tests: 25+</li>
                <li>Expected Pass Rate: 100%</li>
                <li>Execution Time: ~10-15 seconds</li>
            </ul>
        </div>

        <!-- Future Enhancements Section -->
        <div v-if="activeSubsection === 'future'" class="space-y-4">
            <h4 class="font-bold text-gray-900 dark:text-gray-100">Hostel Module (Ready for Implementation)</h4>
            <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">The <code>RentalHostel</code> model is already created and ready for integration. When ready to activate hostel functionality:</p>
            <ul class="list-disc list-inside space-y-1 text-sm text-gray-700 dark:text-gray-300">
                <li>Create HostelRentalFormGuest.vue component</li>
                <li>Create RentalHostelRepository and RentalHostelController</li>
                <li>Create request validation classes</li>
                <li>Add routes to routes/rental.php</li>
                <li>Add comprehensive tests</li>
                <li>Add hostel link to Welcome page</li>
            </ul>

            <h4 class="font-bold text-gray-900 dark:text-gray-100 mt-4">Calendar Module (Planned)</h4>
            <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">A unified calendar view showing:</p>
            <ul class="list-disc list-inside space-y-1 text-sm text-gray-700 dark:text-gray-300">
                <li>Vehicle bookings by type and date</li>
                <li>Venue bookings by type and date</li>
                <li>Equipment usage (if integrated)</li>
                <li>Laboratory reservations (if integrated)</li>
                <li>Date range filtering</li>
                <li>Color-coded by resource type</li>
            </ul>

            <h4 class="font-bold text-gray-900 dark:text-gray-100 mt-4">Admin Features (Recommended)</h4>
            <ul class="list-disc list-inside space-y-1 text-sm text-gray-700 dark:text-gray-300">
                <li>Dashboard showing all pending bookings</li>
                <li>Approve/reject functionality with notes</li>
                <li>Status transition UI</li>
                <li>Analytics and reports</li>
                <li>Email notifications on booking status changes</li>
                <li>SMS notifications for urgent approvals</li>
            </ul>
        </div>
    </div>
</template>

<style scoped>
code {
    background-color: #f3f4f6;
    color: #1f2937;
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
    font-family: 'Courier New', monospace;
    font-size: 0.875em;
}

:deep(.dark) code {
    background-color: #374151;
    color: #f3f4f6;
}
</style>
