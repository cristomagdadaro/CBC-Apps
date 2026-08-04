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
            <section class="transition-colors p-3 md:p-6">
                <div class="mb-5 flex flex-col gap-3 border-b border-slate-200 pb-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900">System Users</h2>
                        <p class="mt-2 text-sm text-slate-600">
                            Manage system users, their roles, and permissions. Create new users, edit existing ones, and control access to various features of the application.
                        </p>
                    </div>
                </div>

                <CRCMDatatable :base-model="User" :can-view="true" :can-create="true" :can-update="true" :can-delete="true" />
            </section>
        </div>
    </AppLayout>
</template>
