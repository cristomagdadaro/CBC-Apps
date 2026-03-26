<script>
export default {
        name: "UsersHeaderAction",
        computed: {
                headerTitle() {
                        if (route().current("system.users.create")) {
                                return "Create User";
                        }

                        if (route().current("system.users.show")) {
                                return "Edit User";
                        }

                        return "User Management";
                },
                headerSubtitle() {
                        if (route().current("system.users.create")) {
                                return "Provision a new account, assign roles, and grant direct permissions.";
                        }

                        if (route().current("system.users.show")) {
                                return "Update account details, access roles, and direct permissions.";
                        }

                        return "Manage users, roles, and direct permissions from a single module.";
                },
        },
};
</script>

<template>
        <ActionHeaderLayout :title="headerTitle" :subtitle="headerSubtitle" :route-link="route('system.users.index')">
        </ActionHeaderLayout>
</template>
