<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { useNotifier } from '@/Modules/composables/useNotifier';

const { success, error: notifyError, warning } = useNotifier();

const loading = ref(false);
const users = ref([]);
const search = ref('');

const loadUsers = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('api.users.index'), {
            params: {
                search: search.value || undefined,
                with: 'roles',
                per_page: 20,
            },
        });

        users.value = response?.data?.data?.data ?? response?.data?.data ?? [];
    } catch (error) {
        notifyError('Failed to load users.');
    } finally {
        loading.value = false;
    }
};

const deleteUser = async (id) => {
    if (!confirm('Delete this user?')) {
        warning('User deletion was cancelled.');
        return;
    }

    try {
        await axios.delete(route('api.users.destroy', id));
        success('User deleted successfully.');
        await loadUsers();
    } catch (error) {
        notifyError('Failed to delete user.');
    }
};

onMounted(loadUsers);
</script>

<template>
    <AppLayout title="User Management">
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">User Management</h1>
                <Link :href="route('system.users.create')" class="px-3 py-2 rounded bg-AB text-white text-sm hover:bg-AC">
                    Create User
                </Link>
            </div>
        </template>

        <div class="max-w-6xl mx-auto px-4">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="mb-4 flex gap-2">
                    <input
                        v-model="search"
                        type="text"
                        class="flex-1 rounded border-gray-300"
                        placeholder="Search by name, email, or employee ID"
                        @keyup.enter="loadUsers"
                    />
                    <button class="px-3 py-2 rounded border" @click="loadUsers">Search</button>
                </div>

                <div v-if="loading" class="text-sm text-gray-500">Loading users...</div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2">Name</th>
                                <th class="text-left py-2">Email</th>
                                <th class="text-left py-2">Employee ID</th>
                                <th class="text-left py-2">Roles</th>
                                <th class="text-left py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users" :key="user.id" class="border-b">
                                <td class="py-2">{{ user.name }}</td>
                                <td class="py-2">{{ user.email }}</td>
                                <td class="py-2">{{ user.employee_id || '-' }}</td>
                                <td class="py-2">
                                    <span class="text-xs">{{ (user.roles || []).map((r) => r.label || r.name).join(', ') || (user.is_admin ? 'Admin' : '-') }}</span>
                                </td>
                                <td class="py-2 flex gap-2">
                                    <Link :href="route('system.users.show', user.id)" class="text-blue-600">Edit</Link>
                                    <button class="text-red-600" @click="deleteUser(user.id)">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
