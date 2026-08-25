<script>
import User from "@/Modules/domain/User";
import UsersHeaderAction from "@/Pages/System/Users/components/UsersHeaderAction.vue";

export default {
    name: "UsersIndex",
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
            <section class="transition-colors">
                <CRCMDatatable
                    :base-model="User"
                    :can-view="true"
                    :can-create="true"
                    :can-update="true"
                    :can-delete="true" />
            </section>
        </div>
    </AppLayout>
</template>
