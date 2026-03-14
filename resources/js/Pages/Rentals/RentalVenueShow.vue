<script setup>
import { onMounted, ref } from 'vue'

const props = defineProps({
    rental_id: {
        type: String,
        required: true,
    },
})

const loading = ref(true)
const error = ref('')
const rental = ref(null)

const loadRental = async () => {
    loading.value = true
    error.value = ''

    try {
        const response = await fetch(`/api/guest/rental/venues/${props.rental_id}`)

        if (!response.ok) {
            throw new Error('Unable to load venue rental details.')
        }

        const payload = await response.json()
        rental.value = payload?.data ?? null
    } catch (err) {
        error.value = err?.message || 'Failed to load venue rental details.'
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    loadRental()
})
</script>

<template>
    <Head title="Venue Rental Details" />

    <GuestFormPage
        title="Venue Rental Details"
        subtitle="View booking details and current status."
        :delay-ready="true"
    >
        <div class="rounded-lg border border-gray-200 bg-white p-5">
            <div v-if="loading" class="text-sm text-gray-500">Loading details...</div>
            <div v-else-if="error" class="text-sm text-red-600">{{ error }}</div>
            <div v-else-if="!rental" class="text-sm text-gray-500">Rental not found.</div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div><span class="font-semibold">Venue:</span> {{ rental.venue_type || '-' }}</div>
                <div><span class="font-semibold">Status:</span> {{ rental.status || '-' }}</div>
                <div><span class="font-semibold">From:</span> {{ rental.date_from || '-' }} {{ rental.time_from || '' }}</div>
                <div><span class="font-semibold">To:</span> {{ rental.date_to || '-' }} {{ rental.time_to || '' }}</div>
                <div><span class="font-semibold">Event Name:</span> {{ rental.event_name || '-' }}</div>
                <div><span class="font-semibold">Expected Attendees:</span> {{ rental.expected_attendees ?? '-' }}</div>
                <div><span class="font-semibold">Requested By:</span> {{ rental.requested_by || '-' }}</div>
                <div><span class="font-semibold">Contact:</span> {{ rental.contact_number || '-' }}</div>
                <div class="md:col-span-2"><span class="font-semibold">Destination Region:</span> {{ rental.destination_region || '-' }}</div>
                <div><span class="font-semibold">Destination Province:</span> {{ rental.destination_province || '-' }}</div>
                <div><span class="font-semibold">Destination City:</span> {{ rental.destination_city || '-' }}</div>
                <div class="md:col-span-2"><span class="font-semibold">Destination Location:</span> {{ rental.destination_location || '-' }}</div>
                <div class="md:col-span-2"><span class="font-semibold">Notes:</span> {{ rental.notes || '-' }}</div>
            </div>
        </div>
    </GuestFormPage>
</template>
