<script>
export default {
    name: "PersonnelRegistrationVerified",
    props: {
        registration: {
            type: Object,
            required: true,
        },
    },
};
</script>

<template>
    <Head title="Personnel Email Verified" />

    <guest-form-page
        title="Email Verified"
        subtitle="Your personnel registration is now ready for administrator review."
        guide-key="personnel-registration-guest"
        max-width="max-w-2xl"
    >
        <div class="rounded-2xl border border-emerald-200 bg-white p-6 shadow-xl">
            <div class="rounded-xl bg-emerald-50 p-5 text-emerald-900">
                <p class="text-xs font-bold uppercase tracking-widest text-emerald-700">Verification complete</p>
                <h1 class="mt-2 text-2xl font-black">{{ registration.full_name }}</h1>
                <p class="mt-2 text-sm">
                    We verified {{ registration.email }}. Your registration status is
                    <span class="font-semibold">{{ registration.status }}</span>, and an administrator can now approve it.
                </p>
            </div>

            <div class="mt-5 flex justify-end">
                <Link :href="route('welcome')" class="rounded-lg bg-AB px-4 py-2 text-sm font-semibold text-white shadow">
                    Return to services
                </Link>
            </div>
        </div>
    </guest-form-page>
</template>
