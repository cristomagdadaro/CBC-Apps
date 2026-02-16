<script>
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import NotificationToast from '@/Components/NotificationToast.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import SwitchBtn from '@/Components/Buttons/SwitchBtn.vue';
import { router } from '@inertiajs/vue3'
import NavigationItems from '@/Components/NavigationItems.vue';

export default {
    name: 'AppLayout',
    components: {
        ApplicationMark,
        Banner,
        NotificationToast,
        ResponsiveNavLink,
        SwitchBtn,
        NavigationItems
    },
    props: {
        title: String,
    },
    data() {
        return {
            showingNavigationDropdown: false,
            sidebarOpen: false,
            sidebarCollapsed: false,
            navigationMode: this.$page.props.layout_navigation_mode || 'top',
            services: [
                {
                    label: 'Dashboard',
                    href: 'dashboard',
                },{
                    label: 'Event Forms',
                    href: 'forms.index',
                    permission: 'event.forms.manage',
                },{
                    label: 'Rentals',
                    href: null,
                    children: [
                        {
                            label: 'Vehicle Rentals',
                            href: 'rentals.vehicle.index',
                            permission: 'rental.vehicle.manage',
                        },{
                            label: 'Venue Rentals',
                            href: 'rentals.venue.index',
                            permission: 'rental.venue.manage',
                        }
                    ],
                    permission: 'rental.vehicle.manage',
                },{
                    label: 'FES Request Form',
                    href: 'accessUseRequest.index',
                    permission: 'fes.request.approve',
                },{
                    label: 'Lab Equipment Logger',
                    href: 'laboratory.dashboard',
                    permission: 'laboratory.logger.manage',
                },{
                    label: 'Inventory Management',
                    href: 'forms.index',
                    permission: 'inventory.manage',
                    children: [
                        {
                            label: 'Transactions',
                            href: 'transactions.index'
                        }, {
                            label: 'Barcode Printing',
                            href: 'inventory.barcodes.print'
                        },{
                            label: 'Items',
                            href: 'items.index',
                        },{
                            label: 'Suppliers',
                            href: 'suppliers.index'
                        },{
                            label: 'Personnels',
                            href: 'personnels.index'
                        },
                    ]
                },
                {
                    label: 'File Reports',
                    href: 'suppEquipReports.index',
                    permission: 'equipment.report.manage',
                },
                {
                    label: 'System',
                    href: null,
                    roles: ['admin'],
                    children: [
                        {
                            label: 'Options',
                            href: 'system.options.index',
                            roles: ['admin'],
                        },{
                            label: 'Users Management',
                            href: 'system.users.index',
                            permission: 'users.manage',
                            roles: ['admin'],
                        },
                    ]
                },
                {
                    label: 'Manuals & Guides',
                    href: 'manuals.index',
                },
            ],
        };
    },
    created() {
        if (typeof window === 'undefined') {
            return;
        }

        // Initialize from database prop
        if (this.$page.props.layout_navigation_mode) {
            this.navigationMode = this.$page.props.layout_navigation_mode;
        }

        // Keep sidebar collapsed state in localStorage
        const savedSidebarCollapsed = window.localStorage.getItem('layout.sidebar.collapsed');
        if (savedSidebarCollapsed === 'true' || savedSidebarCollapsed === 'false') {
            this.sidebarCollapsed = savedSidebarCollapsed === 'true';
        }
    },
    computed: {
        isSidebarModeResponsive() {
            // Only sidebar mode if layout_navigation_mode is sidebar AND screen is lg+
            if (typeof window === 'undefined') return false;
            const isLg = window.matchMedia('(min-width: 1024px)').matches;
            return this.$page.props.layout_navigation_mode === 'sidebar' && isLg;
        },

        // raw boolean for whether the layout mode is sidebar (used by template)
        isSidebarMode() {
            return this.navigationMode === 'sidebar' || this.rawLayoutMode === 'sidebar';
        },

        // expose raw layout mode for child components (desktop top vs sidebar)
        rawLayoutMode() {
            return this.$page.props.layout_navigation_mode || 'top';
        },
        visibleServices() {
            return this.services.filter((service) => {
                if (!this.canAccessService(service)) {
                    return false;
                }

                if (!Array.isArray(service.children)) {
                    return true;
                }

                return this.visibleChildren(service).length > 0 || !!service.href;
            });
        },
        formattedPermissions() {
            const permissions = this.$page.props.auth?.permissions || [];

            // Super admin case: ['*']
            if (permissions.length === 1 && permissions[0] === '*') {
                return [{
                    name: '*',
                    label: 'All Permissions',
                }];
            }

            return permissions.map(permission => {
                if (!permission || permission === '*') {
                    return {
                        name: permission,
                        label: 'All Permissions',
                    };
                }

                const base = permission
                    .split('.')
                    .slice(0, -1)
                    .join(' ');

                return {
                    name: permission,
                    label: this.formatLabel(base),
                };
            });
        },
        rolesList() {
            return this.$page.props.auth?.roles || [];
        },
        singleRole() {
            return this.rolesList.length === 1;
        },
        singleRoleLabel() {
            if (!this.singleRole) return '';
            return this.formatLabel(this.rolesList[0] || '');
        }
    },
    methods: {
        formatLabel(text) {
            if (!text) return '';
            const cleaned = String(text).replace(/_/g, ' ');
            return cleaned.split(' ').map(s => s.charAt(0).toUpperCase() + s.slice(1)).join(' ');
        },
        hasPermission(permission) {
            if (!permission) {
                return true;
            }

            const permissions = this.$page.props?.auth?.permissions ?? [];

            return permissions.includes('*') || permissions.includes(permission);
        },
        hasAnyRole(roles = []) {
            if (!roles.length) {
                return true;
            }

            const currentRoles = this.$page.props?.auth?.roles ?? [];

            return roles.some((role) => currentRoles.includes(role));
        },
        canAccessService(service) {
            if (!service) {
                return false;
            }

            return this.hasPermission(service.permission) && this.hasAnyRole(service.roles || []);
        },
        visibleChildren(service) {
            if (!Array.isArray(service?.children)) {
                return [];
            }

            return service.children.filter((child) => this.canAccessService(child));
        },
        isServiceActive(service) {
            if (!service) {
                return false;
            }

            if (service.href && route().current(service.href)) {
                return true;
            }

            if (Array.isArray(service.children)) {
                return this.visibleChildren(service).some((child) => route().current(child.href));
            }

            return false;
        },
        handleMobileMenuToggle() {
            if (this.isSidebarModeResponsive) {
                this.sidebarOpen = !this.sidebarOpen;
                return;
            }

            this.showingNavigationDropdown = !this.showingNavigationDropdown;
        },
        toggleSidebarCollapse() {
            this.sidebarCollapsed = !this.sidebarCollapsed;

            if (typeof window !== 'undefined') {
                window.localStorage.setItem('layout.sidebar.collapsed', String(this.sidebarCollapsed));
            }
        },
        handleHamburgerClick() {
            // For responsive behavior, use isSidebarModeResponsive
            if (!this.isSidebarModeResponsive) {
                this.showingNavigationDropdown = !this.showingNavigationDropdown;
                return;
            }

            if (typeof window !== 'undefined' && window.matchMedia('(min-width: 1024px)').matches) {
                this.toggleSidebarCollapse();
                return;
            }

            this.sidebarOpen = !this.sidebarOpen;
        },
        closeSidebar() {
            this.sidebarOpen = false;
        },
        switchToTeam(team) {
            router.put(route('current-team.update'), {
                team_id: team.id,
            }, {
                preserveState: false,
            });
        },
        logout() {
            router.post(route('logout'));
        },
    },
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />
        <NotificationToast />

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex">
            <Transition name="nav-switch" mode="out-in">
                <aside
                    v-if="isSidebarModeResponsive"
                    class="hidden lg:flex lg:shrink-0 lg:flex-col bg-white dark:bg-gray-800 border-r border-AA shadow-sm dark:border-gray-700 transition-all duration-300 ease-in-out overflow-hidden "
                    :class="sidebarCollapsed ? 'lg:w-14' : 'lg:w-72'"
                >
                    <!-- Header with hamburger always visible -->
                    <div class="px-2 py-2.5 border-b border-AA bg-AA dark:border-gray-700 text-gray-100 drop-shadow-md flex items-center justify-center lg:justify-start gap-2 transition-opacity duration-200">
                        <button
                            type="button"
                            class="inline-flex items-center justify-center p-2 rounded-md dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out"
                            @click="toggleSidebarCollapse"
                            :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                        >
                            <svg
                                class="h-6 w-6 transition-transform duration-300"
                                :class="sidebarCollapsed ? 'rotate-180' : 'rotate-0'"
                                stroke="currentColor"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 19l-7-7 7-7"
                                />
                            </svg>
                        </button>
                        <div class="flex items-center">
                            <div v-if="$page.props.jetstream.managesProfilePhotos && $page.props.auth?.user" class="shrink-0 me-3">
                                <img class="h-10 w-10 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth?.user?.name">
                            </div>

                            <div class="leading-none">
                                <div class="font-bold tracking-wide">
                                    {{ $page.props.auth?.user?.name || 'Account' }}
                                </div>
                                <div class="text-xs tracking-wide">
                                    {{ $page.props.auth?.user?.email }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Navigation content (hidden when collapsed) -->
                    <div v-if="!sidebarCollapsed" class="flex flex-col overflow-y-auto p-3 space-y-1 transition-opacity duration-200">
                        <div class="flex flex-col">
                            <div class="ms-3 relative">
                                <!-- Teams Dropdown -->
                                <Dropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="60">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.current_team.name }}

                                                <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="w-60">
                                            <!-- Team Management -->
                                            <div class="block px-4 py-2 text-xs text-gray-400">
                                                Manage Team
                                            </div>

                                            <!-- Team Settings -->
                                            <DropdownLink :href="route('teams.show', $page.props.auth.user.current_team)">
                                                Team Settings
                                            </DropdownLink>

                                            <DropdownLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')">
                                                Create New Team
                                            </DropdownLink>

                                            <!-- Team Switcher -->
                                            <template v-if="$page.props.auth.user.all_teams.length > 1">
                                                <div class="border-t border-gray-200 dark:border-gray-600" />

                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    Switch Teams
                                                </div>

                                                <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                                    <form @submit.prevent="switchToTeam(team)">
                                                        <DropdownLink as="button">
                                                            <div class="flex items-center">
                                                                <svg v-if="team.id == $page.props.auth.user.current_team_id" class="me-2 h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>

                                                                <div>{{ team.name }}</div>
                                                            </div>
                                                        </DropdownLink>
                                                    </form>
                                                </template>
                                            </template>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                        <NavigationItems :services="visibleServices" mode="sidebar" :visibleChildren="visibleChildren" :isServiceActive="isServiceActive" />
                        <div class="px-3 pt-3 pb-1 text-xs text-gray-400">
                            Manage Account
                        </div>
                        <NavLink :href="route('profile.show')" :active="route().current('profile.show')">
                            Profile
                        </NavLink>
                        <!-- Authentication -->
                        <form @submit.prevent="logout">
                            <NavLink as="button">
                                Log Out
                            </NavLink>
                        </form>
                    </div>
                </aside>
            </Transition>

            <div class="flex-1 min-w-0">
                <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                    <!-- Primary Navigation Menu -->
                    <div v-if="!isSidebarModeResponsive" class="default-container">
                        <div class="flex justify-between h-16">
                            <div class="flex items-center w-full">
                                <!-- Logo -->
                                <div class="shrink-0 flex items-center">
                                    <Link :href="route('dashboard')">
                                        <ApplicationMark class="block h-9 w-auto" />
                                    </Link>
                                </div>

                                <!-- Mobile hamburger (always top on mobile) -->
                                <button
                                    @click="showingNavigationDropdown = !showingNavigationDropdown"
                                    class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 ms-3"
                                    :aria-expanded="String(showingNavigationDropdown)"
                                    aria-controls="primary-navigation"
                                    aria-label="Toggle navigation"
                                >
                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </button>

                                <!-- Navigation Links -->
                                <Transition name="nav-fade" mode="out-in">
                                    <div v-if="!isSidebarMode" class="hidden sm:ms-6 sm:flex sm:items-center sm:gap-2 sm:flex-wrap">
                                        <template v-for="service in visibleServices" :key="service.label">
                                            <Dropdown v-if="service.children && visibleChildren(service).length" align="right" width="60">
                                                <template #trigger>
                                                    <button
                                                        type="button"
                                                        :class="[
                                                            'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md transition ease-in-out duration-150',
                                                            isServiceActive(service)
                                                                ? 'text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700'
                                                                : 'text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700',
                                                        ]"
                                                    >
                                                        {{ service.label }}
                                                        <caret-down class="ms-2 -me-0.5 h-4 w-4" />
                                                    </button>
                                                </template>

                                                <template #content>
                                                    <div v-for="child in visibleChildren(service)" :key="child.label" class="w-60">
                                                        <DropdownLink :href="route(child.href)" :active="route().current(child.href)">
                                                            <div class="flex items-center gap-1">
                                                                <span>{{ child.label }}</span>
                                                            </div>
                                                        </DropdownLink>
                                                    </div>
                                                </template>
                                            </Dropdown>

                                            <NavLink v-else :href="route(service.href)" :active="route().current(service.href)">
                                                {{ service.label }}
                                            </NavLink>
                                        </template>
                                    </div>
                                </Transition>
                            </div>

                            <div v-if="!isSidebarModeResponsive" class="hidden sm:flex sm:items-center sm:ms-6">
                                <div class="ms-3 relative">
                                    <!-- Teams Dropdown -->
                                    <Dropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="60">
                                        <template #trigger>
                                            <span class="inline-flex rounded-md">
                                                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                                                    {{ $page.props.auth.user.current_team.name }}

                                                    <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </template>

                                        <template #content>
                                            <div class="w-60">
                                                <!-- Team Management -->
                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    Manage Team
                                                </div>

                                                <!-- Team Settings -->
                                                <DropdownLink :href="route('teams.show', $page.props.auth.user.current_team)">
                                                    Team Settings
                                                </DropdownLink>

                                                <DropdownLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')">
                                                    Create New Team
                                                </DropdownLink>

                                                <!-- Team Switcher -->
                                                <template v-if="$page.props.auth.user.all_teams.length > 1">
                                                    <div class="border-t border-gray-200 dark:border-gray-600" />

                                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                                        Switch Teams
                                                    </div>

                                                    <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                                        <form @submit.prevent="switchToTeam(team)">
                                                            <DropdownLink as="button">
                                                                <div class="flex items-center">
                                                                    <svg v-if="team.id == $page.props.auth.user.current_team_id" class="me-2 h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>

                                                                    <div>{{ team.name }}</div>
                                                                </div>
                                                            </DropdownLink>
                                                        </form>
                                                    </template>
                                                </template>
                                            </div>
                                        </template>
                                    </Dropdown>
                                </div>
                                
                                <div class="ms-3 relative">
                                <!-- Settings Dropdown -->
                                    <Dropdown align="right" width="48">
                                        <template #trigger>
                                            <button v-if="$page.props.jetstream.managesProfilePhotos && $page.props.auth?.user" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                                <img class="h-8 w-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth?.user?.name">
                                            </button>

                                            <span v-else class="inline-flex rounded-md">
                                                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                                                    <div class="flex flex-col leading-4">
                                                        <span class="text-sm  font-medium ">{{ $page.props.auth?.user?.name || 'Account' }}</span>
                                                        <div v-if="singleRole" class=" text-xs leading-none ">
                                                            {{ singleRoleLabel }}
                                                        </div>
                                                    </div>
                                                    <caret-down class="ms-2 -me-0.5 h-4 w-4" />
                                                </button>
                                            </span>
                                        </template>

                                        <template #content>
                                            <!-- Account Management -->
                                            <div class="block px-4 py-2 text-xs text-gray-400">
                                                Manage Account
                                            </div>

                                            <DropdownLink :href="route('profile.show')" :active="route().current('profile.show')">
                                                Profile
                                            </DropdownLink>

                                            <div class="block px-4 py-1 text-xs text-gray-400">
                                                Permissions
                                            </div>
                                            <ul class="mb-1">
                                                <li v-for="permission in formattedPermissions" :key="permission.name" class="block px-6 text-xs text-gray-400">
                                                    - {{ permission.label }}
                                                </li>
                                            </ul>

                                            <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')" :active="route().current('api-tokens.index')">
                                                API Tokens
                                            </DropdownLink>

                                            <div class="border-t border-gray-200 dark:border-gray-600" />

                                            <!-- Authentication -->
                                            <form @submit.prevent="logout">
                                                <DropdownLink as="button">
                                                    Log Out
                                                </DropdownLink>
                                            </form>
                                        </template>
                                    </Dropdown>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Responsive Navigation Menu -->
                <div v-if="!isSidebarModeResponsive" :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}" id="primary-navigation" class="sm:hidden bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                    <div class="pt-2 pb-3 space-y-1">
                        <div class="sm:hidden flex flex-col gap-1">
                            <NavigationItems :services="visibleServices" mode="mobile" :visibleChildren="visibleChildren" :isServiceActive="isServiceActive" @item-clicked="showingNavigationDropdown = false" />
                        </div>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex items-center px-4">
                            <div v-if="$page.props.jetstream.managesProfilePhotos && $page.props.auth?.user" class="shrink-0 me-3">
                                <img class="h-10 w-10 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth?.user?.name">
                            </div>

                            <div>
                                <div class="font-medium text-base text-gray-800 dark:text-gray-200">
                                    {{ $page.props.auth?.user?.name || 'Account' }}
                                </div>
                                <div class="font-medium text-sm text-gray-500">
                                    {{ $page.props.auth?.user?.email }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">
                                Profile
                            </ResponsiveNavLink>

                            <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')" :active="route().current('api-tokens.index')">
                                API Tokens
                            </ResponsiveNavLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">
                                    Log Out
                                </ResponsiveNavLink>
                            </form>

                            <!-- Team Management -->
                            <template v-if="$page.props.jetstream.hasTeamFeatures">
                                <div class="border-t border-gray-200 dark:border-gray-600" />

                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    Manage Team
                                </div>

                                <!-- Team Settings -->
                                <ResponsiveNavLink :href="route('teams.show', $page.props.auth.user.current_team)" :active="route().current('teams.show')">
                                    Team Settings
                                </ResponsiveNavLink>

                                <ResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')" :active="route().current('teams.create')">
                                    Create New Team
                                </ResponsiveNavLink>

                                <!-- Team Switcher -->
                                <template v-if="$page.props.auth.user.all_teams.length > 1">
                                    <div class="border-t border-gray-200 dark:border-gray-600" />

                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        Switch Teams
                                    </div>

                                    <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                        <form @submit.prevent="switchToTeam(team)">
                                            <ResponsiveNavLink as="button">
                                                <div class="flex items-center">
                                                    <svg v-if="team.id == $page.props.auth.user.current_team_id" class="me-2 h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <div>{{ team.name }}</div>
                                                </div>
                                            </ResponsiveNavLink>
                                        </form>
                                    </template>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
                <div v-if="isSidebarModeResponsive && sidebarOpen" class="fixed inset-0 z-40 flex lg:hidden">
                    <button type="button" class="absolute inset-0 bg-black/40" @click="closeSidebar" />
                    <aside class="relative h-full w-72 max-w-full bg-white dark:bg-gray-800 border-e border-gray-100 dark:border-gray-700 overflow-y-auto">
                        <div class="h-16 px-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Navigation</span>
                            <button type="button" class="text-gray-500 dark:text-gray-400" @click="closeSidebar">
                                Close
                            </button>
                        </div>
                        <div class="p-3 space-y-1">
                            <button type="button" class="w-full text-left inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                Switch to Top Navigation
                            </button>
                            <template v-for="service in visibleServices" :key="`mobile-sidebar-${service.label}`">
                                <template v-if="service.children && visibleChildren(service).length">
                                    <div class="px-3 pt-3 pb-1 text-xs text-gray-400">
                                        {{ service.label }}
                                    </div>
                                    <ResponsiveNavLink v-if="service.href" :href="route(service.href)" :active="route().current(service.href)">
                                        {{ service.label }}
                                    </ResponsiveNavLink>
                                    <ResponsiveNavLink v-for="child in visibleChildren(service)" :key="child.label" :href="route(child.href)" :active="route().current(child.href)">
                                        {{ child.label }}
                                    </ResponsiveNavLink>
                                </template>
                                <ResponsiveNavLink v-else :href="route(service.href)" :active="route().current(service.href)">
                                    {{ service.label }}
                                </ResponsiveNavLink>
                            </template>
                        </div>
                    </aside>
                </div>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-AA dark:bg-gray-800 shadow-sm border-b border-AA dark:border-gray-700">
                <div class="default-container">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="overflow-auto">
                <slot />
            </main>
            </div>
        </div>
    </div>
</template>
