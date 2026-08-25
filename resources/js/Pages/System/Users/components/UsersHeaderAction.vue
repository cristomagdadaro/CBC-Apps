<script>
import { Link } from "@inertiajs/vue3";

export default {
    name: "UsersHeaderAction",
    components: { Link },
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

            return "Manage system users, their roles, and permissions. Create new users, edit existing ones, and control access to various features of the application";
        },
    },
};
</script>

<template>
    <ActionHeaderLayout
        :title="headerTitle"
        :subtitle="headerSubtitle"
        :route-link="route('system.users.index')">
        <transition-container
            type="pop-in"
            :duration="500">
            <Link
                v-if="route().current('system.users.index')"
                :href="route('system.users.create')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-sm transition-all active:scale-95 shrink-0">
                <lu-plus class="w-4 h-4 text-indigo-500" />
                <span>Create User</span>
            </Link>
        </transition-container>
    </ActionHeaderLayout>
</template>
