<script>
import axios from 'axios'
import { useNotifier } from '@/Modules/composables/useNotifier'
import User from '@/Modules/domain/User';

export default {
    name: 'UsersIndex',
    data() {
        return {
            loading: false,
            users: [],
            search: '',
        }
    },
    created() {
    },
    mounted() {
        this.loadUsers()
    },
    computed: {
        User() {
            return User
        }
    },
    methods: {
        formatLabel(value) {
            if (!value) return ''
            return String(value)
                .replace(/[._]/g, ' ')
                .split(' ')
                .filter(Boolean)
                .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ')
        },
        async loadUsers() {
            this.loading = true
            try {
                const response = await axios.get(route('api.users.index'), {
                    params: {
                        search: this.search || undefined,
                        with: 'roles',
                        per_page: 20,
                    },
                })

                this.users = response?.data?.data?.data ?? response?.data?.data ?? []
            } catch (error) {
                // ApiService handles error notification
            } finally {
                this.loading = false
            }
        },
        async deleteUser(id) {
            if (!confirm('Delete this user?')) {
                return
            }

            try {
                await axios.delete(route('api.users.destroy', id))
                await this.loadUsers()
            } catch (error) {
                // ApiService handles error notification
            }
        }
    }
}
</script>

<template>
    <AppLayout title="User Management">
        <template #header>
            <ActionHeaderLayout title="User Management" subtitle="Manage users and their roles within the system." :route-link="route('system.users.index')" />
        </template>

        <div class="default-container pt-5">
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
                                <th class="text-left py-2">Permissions</th>
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
                                <td class="py-2">
                                    <span class="text-xs">{{ (user.permissions || []).map((p) => formatLabel(p)).join(', ') || '-' }}</span>
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
            
            <DataTable
                index-api="api.users.index"
                :model="User"
                mode="online"
                enable-export
                enable-filters
                enable-search
                striped
                hoverable
            />
        </div>
    </AppLayout>
</template>
