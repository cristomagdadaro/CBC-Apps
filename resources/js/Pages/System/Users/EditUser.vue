<script>
import UserForm from '@/Pages/System/Users/components/UserForm.vue';
import UsersHeaderAction from '@/Pages/System/Users/components/UsersHeaderAction.vue';

export default {
    name: 'EditUser',
    components: {
        UserForm,
        UsersHeaderAction,
    },
    props: {
        data: { type: Object, required: true },
        roleOptions: {
            type: Array,
            default: () => [],
        },
        permissionOptions: {
            type: Array,
            default: () => [],
        },
    },
};
</script>

<template>
    <AppLayout title="Edit User">
        <template #header>
            <UsersHeaderAction />
        </template>

        <div class="default-container pt-5">
            <UserForm :data="data" :role-options="roleOptions" :permission-options="permissionOptions" />
        </div>
    </AppLayout>
</template>
