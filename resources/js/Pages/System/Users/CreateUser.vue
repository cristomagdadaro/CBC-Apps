<script>
import UserForm from '@/Pages/System/Users/components/UserForm.vue';
import UsersHeaderAction from '@/Pages/System/Users/components/UsersHeaderAction.vue';

export default {
    name: 'CreateUser',
    components: {
        UserForm,
        UsersHeaderAction,
    },
    props: {
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
    <AppLayout title="Create User">
        <template #header>
            <UsersHeaderAction />
        </template>

        <div class="default-container pt-5">
            <UserForm :role-options="roleOptions" :permission-options="permissionOptions" />
        </div>
    </AppLayout>
</template>
