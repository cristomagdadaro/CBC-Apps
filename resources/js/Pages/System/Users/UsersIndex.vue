<script>
import User from '@/Modules/domain/User';

export default {
    name: 'UsersIndex',
    computed: {
        User() {
            return User
        }
    },
}
</script>

<template>
    <AppLayout title="User Management">
        <template #header>
            <ActionHeaderLayout title="User Management" subtitle="Manage users and their roles within the system." :route-link="route('system.users.index')" />
        </template>

        <CRCMDatatable :base-model="User" :can-view="true" :can-create="true" :can-update="true" :can-delete="true" />
    </AppLayout>
</template>
