<script>
import User from '@/Modules/domain/User';
import UsersHeaderAction from '@/Pages/System/Users/components/UsersHeaderAction.vue';

export default {
    name: 'UsersIndex',
    components: {
        UsersHeaderAction,
    },
    computed: {
        User() {
            return User;
        },
    },
};
</script>

<template>
    <AppLayout title="User Management">
        <template #header>
            <UsersHeaderAction />
        </template>

        <div class="default-container pt-5">
            <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm md:p-6">
                <div class="mb-5 flex flex-col gap-3 border-b border-slate-200 pb-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Directory</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900">System users</h2>
                        <p class="mt-2 text-sm text-slate-600">Review users, inspect assigned roles, and update direct permissions from the management table.</p>
                    </div>

                    <Link
                        :href="route('system.users.create')"
                        class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700"
                    >
                        Create User
                    </Link>
                </div>

                <CRCMDatatable :base-model="User" :can-view="true" :can-create="true" :can-update="true" :can-delete="true" />
            </section>
        </div>
    </AppLayout>
</template>
