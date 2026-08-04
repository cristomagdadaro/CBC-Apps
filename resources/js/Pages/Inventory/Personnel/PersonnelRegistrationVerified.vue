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
        <div class="md:rounded-lg border border-slate-100 bg-white p-6 w-full">
            <div class="flex flex-col items-center text-center w-full">
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/10">
                    Verification Complete
                </span>
                
                <h1 class="mt-4 text-xl font-bold tracking-tight text-slate-900">
                    Welcome, {{ registration.full_name }}
                </h1>
                
                <p class="mt-2 text-sm leading-relaxed text-slate-600">
                    Your email (<span class="font-medium text-slate-900">{{ registration.email }}</span>) has been successfully verified. Your status is currently 
                    <span class="inline-flex items-center rounded bg-amber-50 px-1.5 py-0.5 text-xs font-medium text-amber-800 ring-1 ring-inset ring-amber-600/20">{{ registration.status }}</span>, 
                    and is awaiting final administrator approval.
                </p>
            </div>

            <div class="mt-6 border-t border-slate-100 pt-4 flex justify-center">
                <Link href="/" class="w-full sm:w-auto text-center rounded-xl bg-AB px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-AB/90 transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-AB">
                    Return to Home
                </Link>
            </div>
        </div>
    </guest-form-page>
</template>
