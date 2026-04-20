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
            <Link :href="route('system.users.create')">
                <add-icon
                    class="h-auto w-5 text-white dark:text-gray-800 dark:bg-gray-200"
                />
            </Link>
        </ActionHeaderLayout>
</template>
