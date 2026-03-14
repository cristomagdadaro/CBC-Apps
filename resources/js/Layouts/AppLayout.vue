<script>
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import NotificationToast from '@/Components/NotificationToast.vue';
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
            userDropdownOpen: false,
            teamDropdownOpen: false,
            navigationMode: this.$page.props.layout_navigation_mode || 'top',
            services: [
                {
                    label: 'Dashboard',
                    href: 'dashboard',
                    icon: 'LuLayoutDashboard',
                },{
                    label: 'Certificate Generator',
                    href: 'certificates.index',
                    permission: 'event.certificates.manage',
                    icon: 'LuAward',
                },{
                    label: 'Event Forms',
                    href: 'forms.index',
                    permission: 'event.forms.manage',
                    icon: 'LuCalendar',
                },{
                    label: 'Rentals',
                    href: null,
                    icon: 'LuCar',
                    children: [
                        {
                            label: 'Vehicle Rentals',
                            href: 'rentals.vehicle.index',
                            permission: 'rental.vehicle.manage',
                            icon: 'LuCar',
                        },{
                            label: 'Venue Rentals',
                            href: 'rentals.venue.index',
                            permission: 'rental.venue.manage',
                            icon: 'LuBuilding',
                        }
                    ],
                    permission: 'rental.vehicle.manage',
                },{
                    label: 'FES Request Form',
                    href: 'accessUseRequest.index',
                    permission: 'fes.request.approve',
                    icon: 'LuShield',
                },{
                    label: 'Equipment Logger',
                    href: 'laboratory.dashboard',
                    permission: 'laboratory.logger.manage',
                    icon: 'LuMicroscope',
                },{
                    label: 'Inventory',
                    href: null,
                    icon: 'LuPackage',
                    permission: 'inventory.manage',
                    children: [
                        {
                            label: 'Dashboard',
                            href: 'transactions.dashboard',
                            icon: 'LuBarChart3',
                        }, {
                            label: 'Transactions',
                            href: 'transactions.index',
                            icon: 'LuArrowLeftRight',
                        }, {
                            label: 'Barcode Printing',
                            href: 'inventory.barcodes.print',
                            icon: 'LuBarcode',
                        },{
                            label: 'Items',
                            href: 'items.index',
                            icon: 'LuBox',
                        },{
                            label: 'Suppliers',
                            href: 'suppliers.index',
                            icon: 'LuTruck',
                        },{
                            label: 'Personnels',
                            href: 'personnels.index',
                            icon: 'LuUsers',
                        },
                    ]
                },
                {
                    label: 'File Reports',
                    href: 'suppEquipReports.index',
                    permission: 'equipment.report.manage',
                    icon: 'LuFileText',
                },
                {
                    label: 'System',
                    href: null,
                    icon: 'LuSettings',
                    roles: ['admin'],
                    children: [
                        {
                            label: 'Options',
                            href: 'system.options.index',
                            roles: ['admin'],
                            icon: 'LuSliders',
                        },{
                            label: 'Users Management',
                            href: 'system.users.index',
                            permission: 'users.manage',
                            roles: ['admin'],
                            icon: 'LuUserCog',
                        },
                    ]
                },
                {
                    label: 'Manuals & Guides',
                    href: 'manuals.index',
                    icon: 'LuBookOpen',
                },
            ],
        };
    },
    created() {
        if (typeof window === 'undefined') return;

        if (this.$page.props.layout_navigation_mode) {
            this.navigationMode = this.$page.props.layout_navigation_mode;
        }

        const savedSidebarCollapsed = window.localStorage.getItem('layout.sidebar.collapsed');
        if (savedSidebarCollapsed === 'true' || savedSidebarCollapsed === 'false') {
            this.sidebarCollapsed = savedSidebarCollapsed === 'true';
        }

        // Close dropdowns on route change
        router.on('navigate', () => {
            this.showingNavigationDropdown = false;
            this.sidebarOpen = false;
            this.userDropdownOpen = false;
            this.teamDropdownOpen = false;
        });
    },
    computed: {
        isSidebarModeResponsive() {
            if (typeof window === 'undefined') return false;
            const isLg = window.matchMedia('(min-width: 1024px)').matches;
            return this.$page.props.layout_navigation_mode === 'sidebar' && isLg;
        },
        isSidebarMode() {
            return this.navigationMode === 'sidebar' || this.rawLayoutMode === 'sidebar';
        },
        rawLayoutMode() {
            return this.$page.props.layout_navigation_mode || 'top';
        },
        visibleServices() {
            return this.services.filter((service) => {
                if (!this.canAccessService(service)) return false;
                if (!Array.isArray(service.children)) return true;
                return this.visibleChildren(service).length > 0 || !!service.href;
            });
        },
        formattedPermissions() {
            const permissions = this.$page.props.auth?.permissions || [];
            if (permissions.length === 1 && permissions[0] === '*') {
                return [{ name: '*', label: 'All Permissions' }];
            }
            return permissions.map(permission => {
                if (!permission || permission === '*') {
                    return { name: permission, label: 'All Permissions' };
                }
                const base = permission.split('.').slice(0, -1).join(' ');
                return { name: permission, label: this.formatLabel(base) };
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
        },
        currentRouteName() {
            return route().current();
        },
    },
    methods: {
        formatLabel(text) {
            if (!text) return '';
            const cleaned = String(text).replace(/_/g, ' ');
            return cleaned.split(' ').map(s => s.charAt(0).toUpperCase() + s.slice(1)).join(' ');
        },
        hasPermission(permission) {
            if (!permission) return true;
            const permissions = this.$page.props?.auth?.permissions ?? [];
            return permissions.includes('*') || permissions.includes(permission);
        },
        hasAnyRole(roles = []) {
            if (!roles.length) return true;
            const currentRoles = this.$page.props?.auth?.roles ?? [];
            return roles.some((role) => currentRoles.includes(role));
        },
        canAccessService(service) {
            if (!service) return false;
            return this.hasPermission(service.permission) && this.hasAnyRole(service.roles || []);
        },
        visibleChildren(service) {
            if (!Array.isArray(service?.children)) return [];
            return service.children.filter((child) => this.canAccessService(child));
        },
        isServiceActive(service) {
            if (!service) return false;
            if (service.href && route().current(service.href)) return true;
            if (Array.isArray(service.children)) {
                return this.visibleChildren(service).some((child) => route().current(child.href));
            }
            return false;
        },
        isChildActive(child) {
            return route().current(child.href);
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
            }, { preserveState: false });
        },
        logout() {
            router.post(route('logout'));
        },
    },
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <Head :title="title" />

        <Banner />
        <NotificationToast />

        <div class="flex min-h-screen">
            <!-- Sidebar Navigation (Desktop) -->
            <Transition 
                enter-active-class="transition-all duration-300 ease-in-out"
                enter-from-class="opacity-0 -translate-x-full"
                enter-to-class="opacity-100 translate-x-0"
                leave-active-class="transition-all duration-300 ease-in-out"
                leave-from-class="opacity-100 translate-x-0"
                leave-to-class="opacity-0 -translate-x-full">
                <aside
                    v-if="isSidebarModeResponsive"
                    class="hidden max-h-screen sticky top-0 lg:flex lg:flex-col lg:sticky inset-y-0 left-0 z-40 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-lg lg:shadow-none transition-all duration-300"
                    :class="sidebarCollapsed ? 'w-20' : 'w-64'">
                    
                    <!-- Sidebar Header -->
                    <div class="bg-AA dark:bg-gray-800 shadow-sm border-b border-AA dark:border-gray-700 h-16">
                        <!-- User Profile Summary -->
                        <div v-if="!sidebarCollapsed" class="flex items-center h-full pl-3">
                            <div class="flex items-center gap-3">
                                <img 
                                    v-if="$page.props.jetstream.managesProfilePhotos && $page.props.auth?.user"
                                    :src="$page.props.auth.user.profile_photo_url" 
                                    :alt="$page.props.auth?.user?.name"
                                    class="h-10 w-10 rounded-full object-cover ring-2 ring-white dark:ring-gray-700 shadow-sm">
                                <div v-else class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-sm">
                                    {{ $page.props.auth?.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                                </div>
                                <div class="flex-1 min-w-0 leading-tight">
                                    <p class="text-sm font-semibold text-gray-50 dark:text-gray-200 truncate uppercase">
                                        {{ $page.props.auth?.user?.name || 'User' }}
                                    </p>
                                    <p class="text-xs text-gray-200 dark:text-gray-500 truncate">
                                        {{ singleRoleLabel || 'Member' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="p-3 border-b border-gray-200 dark:border-gray-700 flex justify-center">
                            <img 
                                v-if="$page.props.jetstream.managesProfilePhotos && $page.props.auth?.user"
                                :src="$page.props.auth.user.profile_photo_url" 
                                class="h-10 w-10 rounded-full object-cover ring-2 ring-white dark:ring-gray-700">
                            <div v-else class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold">
                                {{ $page.props.auth?.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                            </div>
                        </div>
                        <button
                            v-if="!sidebarCollapsed"
                            @click="toggleSidebarCollapse"
                            class="p-1.5 rounded-lg text-white/70 hover:text-white hover:bg-white/10 transition-colors"
                            title="Collapse sidebar">
                            <LuPanelLeft class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Collapsed Toggle (when collapsed) -->
                    <button
                        v-if="sidebarCollapsed"
                        @click="toggleSidebarCollapse"
                        class="absolute -right-3 top-20 bg-blue-600 text-white p-1.5 rounded-full shadow-lg hover:bg-blue-700 transition-colors z-50"
                        title="Expand sidebar">
                        <LuChevronRight class="w-4 h-4" />
                    </button>

                    <!-- Navigation Items -->
                    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                        <template v-for="service in visibleServices" :key="service.label">
                            <!-- Single Link -->
                            <Link
                                v-if="!service.children || !visibleChildren(service).length"
                                :href="route(service.href)"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 group relative"
                                :class="isServiceActive(service) 
                                    ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300' 
                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
                                <component :is="service.icon" class="w-5 h-5 flex-shrink-0" :class="isServiceActive(service) ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300'" />
                                <span v-if="!sidebarCollapsed" class="truncate">{{ service.label }}</span>
                                <span v-if="isServiceActive(service) && !sidebarCollapsed" class="ml-auto w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                
                                <!-- Tooltip for collapsed state -->
                                <div v-if="sidebarCollapsed" class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50">
                                    {{ service.label }}
                                </div>
                            </Link>

                            <!-- Dropdown Group -->
                            <div v-else class="space-y-1">
                                <button
                                    @click="service.isOpen = !service.isOpen"
                                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 group"
                                    :class="isServiceActive(service) 
                                        ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300' 
                                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
                                    <component :is="service.icon" class="w-5 h-5 flex-shrink-0" :class="isServiceActive(service) ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300'" />
                                    <span v-if="!sidebarCollapsed" class="flex-1 text-left truncate">{{ service.label }}</span>
                                    <LuChevronDown v-if="!sidebarCollapsed" class="w-4 h-4 transition-transform duration-200" :class="service.isOpen ? 'rotate-180' : ''" />
                                    
                                    <!-- Collapsed dropdown indicator -->
                                    <div v-if="sidebarCollapsed && isServiceActive(service)" class="absolute left-0 w-1 h-8 bg-blue-600 rounded-r-full"></div>
                                </button>
                                
                                <!-- Expanded children -->
                                <div v-if="!sidebarCollapsed && service.isOpen" class="ml-4 pl-4 border-l-2 border-gray-200 dark:border-gray-700 space-y-1">
                                    <Link
                                        v-for="child in visibleChildren(service)"
                                        :key="child.label"
                                        :href="route(child.href)"
                                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all duration-200"
                                        :class="isChildActive(child)
                                            ? 'text-blue-700 dark:text-blue-300 bg-blue-50/50 dark:bg-blue-900/10 font-medium'
                                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/30'">
                                        <component :is="child.icon" v-if="child.icon" class="w-4 h-4" :class="isChildActive(child) ? 'text-blue-600' : 'text-gray-400'" />
                                        <span v-else class="w-1.5 h-1.5 rounded-full" :class="isChildActive(child) ? 'bg-blue-600' : 'bg-gray-300'"></span>
                                        <span class="truncate">{{ child.label }}</span>
                                    </Link>
                                </div>
                            </div>
                        </template>
                    </nav>

                    <!-- Sidebar Footer -->
                    <div class="p-3 border-t border-gray-200 dark:border-gray-700 space-y-1">
                        <Link
                            :href="route('profile.show')"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors group relative">
                            <LuUser class="w-5 h-5 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300" />
                            <span v-if="!sidebarCollapsed">Profile</span>
                            <div v-if="sidebarCollapsed" class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50">
                                Profile
                            </div>
                        </Link>
                        <button
                            @click="logout"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors group relative">
                            <LuLogOut class="w-5 h-5" />
                            <span v-if="!sidebarCollapsed">Log Out</span>
                            <div v-if="sidebarCollapsed" class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50">
                                Log Out
                            </div>
                        </button>
                    </div>
                </aside>
            </Transition>

            <!-- Mobile Sidebar Overlay -->
            <Transition
                enter-active-class="transition-opacity duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-300"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0">
                <div 
                    v-if="isSidebarModeResponsive && sidebarOpen" 
                    class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden"
                    @click="closeSidebar">
                </div>
            </Transition>

            <!-- Mobile Sidebar Drawer -->
            <Transition
                enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="-translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transition-transform duration-300 ease-in"
                leave-from-class="translate-x-0"
                leave-to-class="-translate-x-full">
                <aside
                    v-if="isSidebarModeResponsive && sidebarOpen"
                    class="fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-gray-800 shadow-2xl lg:hidden flex flex-col">
                    
                    <!-- Mobile Header -->
                    <div class="h-16 flex items-center justify-between px-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-600 to-indigo-600">
                        <span class="font-bold text-white">FES System</span>
                        <button @click="closeSidebar" class="p-2 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition-colors">
                            <LuX class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Mobile User Info -->
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <div class="flex items-center gap-3">
                            <img 
                                v-if="$page.props.jetstream.managesProfilePhotos && $page.props.auth?.user"
                                :src="$page.props.auth.user.profile_photo_url" 
                                class="h-12 w-12 rounded-full object-cover ring-2 ring-white dark:ring-gray-700">
                            <div v-else class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg">
                                {{ $page.props.auth?.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $page.props.auth?.user?.name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $page.props.auth?.user?.email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Navigation -->
                    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                        <template v-for="service in visibleServices" :key="`mobile-${service.label}`">
                            <Link
                                v-if="!service.children || !visibleChildren(service).length"
                                :href="route(service.href)"
                                @click="closeSidebar"
                                class="flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-medium transition-colors"
                                :class="isServiceActive(service) 
                                    ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300' 
                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
                                <component :is="service.icon" class="w-5 h-5" :class="isServiceActive(service) ? 'text-blue-600' : 'text-gray-400'" />
                                {{ service.label }}
                            </Link>
                            
                            <div v-else>
                                <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                    {{ service.label }}
                                </div>
                                <div class="ml-2 space-y-1">
                                    <Link
                                        v-for="child in visibleChildren(service)"
                                        :key="`mobile-child-${child.label}`"
                                        :href="route(child.href)"
                                        @click="closeSidebar"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors"
                                        :class="isChildActive(child)
                                            ? 'text-blue-700 dark:text-blue-300 bg-blue-50/50 dark:bg-blue-900/10 font-medium'
                                            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/30'">
                                        <component :is="child.icon" v-if="child.icon" class="w-4 h-4" />
                                        <span v-else class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                                        {{ child.label }}
                                    </Link>
                                </div>
                            </div>
                        </template>
                    </nav>

                    <!-- Mobile Footer -->
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700 space-y-2">
                        <Link
                            :href="route('profile.show')"
                            @click="closeSidebar"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50">
                            <LuUser class="w-5 h-5 text-gray-400" />
                            Profile Settings
                        </Link>
                        <button
                            @click="logout"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                            <LuLogOut class="w-5 h-5" />
                            Log Out
                        </button>
                    </div>
                </aside>
            </Transition>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
                
                <!-- Top Navigation (Top mode or mobile) -->
                <nav 
                    v-if="!isSidebarModeResponsive || !isSidebarMode"
                    class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-30">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between h-16">
                            <!-- Left: Logo & Mobile Menu -->
                            <div class="flex items-center gap-4">
                                <button
                                    v-if="isSidebarModeResponsive"
                                    @click="sidebarOpen = true"
                                    class="lg:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                                    <LuMenu class="w-6 h-6" />
                                </button>

                                <Link :href="route('dashboard')" class="flex items-center gap-2">
                                    <ApplicationMark class="h-8 w-8 text-blue-600" />
                                    <span class="font-bold text-xl text-gray-900 dark:text-white hidden sm:block">FES</span>
                                </Link>

                                <!-- Desktop Navigation -->
                                <div class="hidden md:flex items-center gap-1 ml-8">
                                    <template v-for="service in visibleServices" :key="`top-${service.label}`">
                                        <Dropdown v-if="service.children && visibleChildren(service).length" align="left" width="56">
                                            <template #trigger>
                                                <button
                                                    class="inline-flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                                                    :class="isServiceActive(service)
                                                        ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20'
                                                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
                                                    <component :is="service.icon" class="w-4 h-4" />
                                                    {{ service.label }}
                                                    <LuChevronDown class="w-4 h-4 opacity-50" />
                                                </button>
                                            </template>
                                            <template #content>
                                                <div class="py-1">
                                                    <Link
                                                        v-for="child in visibleChildren(service)"
                                                        :key="child.label"
                                                        :href="route(child.href)"
                                                        class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 first:rounded-t-md last:rounded-b-md">
                                                        <component :is="child.icon" v-if="child.icon" class="w-4 h-4 text-gray-400" />
                                                        {{ child.label }}
                                                    </Link>
                                                </div>
                                            </template>
                                        </Dropdown>

                                        <Link
                                            v-else
                                            :href="route(service.href)"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                                            :class="isServiceActive(service)
                                                ? 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20'
                                                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50'">
                                            <component :is="service.icon" class="w-4 h-4" />
                                            {{ service.label }}
                                        </Link>
                                    </template>
                                </div>
                            </div>

                            <!-- Right: User Menu -->
                            <div class="flex items-center gap-2">
                                <!-- Notifications (placeholder) -->
                                <button class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-700 transition-colors relative">
                                    <LuBell class="w-5 h-5" />
                                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white dark:border-gray-800"></span>
                                </button>

                                <!-- User Dropdown -->
                                <Dropdown align="right" width="64">
                                    <template #trigger>
                                        <button class="flex items-center gap-3 p-1.5 pr-3 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
                                            <img 
                                                v-if="$page.props.jetstream.managesProfilePhotos && $page.props.auth?.user"
                                                :src="$page.props.auth.user.profile_photo_url" 
                                                class="h-8 w-8 rounded-full object-cover ring-2 ring-white dark:ring-gray-700">
                                            <div v-else class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm">
                                                {{ $page.props.auth?.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                                            </div>
                                            <div class="hidden sm:block text-left">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white leading-tight">{{ $page.props.auth?.user?.name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 leading-tight">{{ singleRoleLabel }}</p>
                                            </div>
                                            <LuChevronDown class="w-4 h-4 text-gray-400 hidden sm:block" />
                                        </button>
                                    </template>
                                    
                                    <template #content>
                                        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $page.props.auth?.user?.name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $page.props.auth?.user?.email }}</p>
                                        </div>
                                        
                                        <div class="py-1">
                                            <DropdownLink :href="route('profile.show')" class="flex items-center gap-2">
                                                <LuUser class="w-4 h-4" />
                                                Profile
                                            </DropdownLink>
                                            
                                            <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">
                                                <LuKey class="w-4 h-4 mr-2" />
                                                API Tokens
                                            </DropdownLink>
                                        </div>

                                        <div class="border-t border-gray-100 dark:border-gray-700 py-1">
                                            <button @click="logout" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                                <LuLogOut class="w-4 h-4" />
                                                Log Out
                                            </button>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Navigation Menu -->
                    <Transition
                        enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 -translate-y-2"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition-all duration-200 ease-in"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 -translate-y-2">
                        <div v-if="showingNavigationDropdown" class="md:hidden border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                            <div class="px-4 py-3 space-y-1">
                                <template v-for="service in visibleServices" :key="`mobile-top-${service.label}`">
                                    <Link
                                        v-if="!service.children || !visibleChildren(service).length"
                                        :href="route(service.href)"
                                        @click="showingNavigationDropdown = false"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                                        :class="isServiceActive(service) 
                                            ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300' 
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700'">
                                        <component :is="service.icon" class="w-5 h-5" />
                                        {{ service.label }}
                                    </Link>
                                    
                                    <div v-else class="space-y-1">
                                        <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                            {{ service.label }}
                                        </div>
                                        <Link
                                            v-for="child in visibleChildren(service)"
                                            :key="`mobile-top-child-${child.label}`"
                                            :href="route(child.href)"
                                            @click="showingNavigationDropdown = false"
                                            class="flex items-center gap-3 px-3 py-2 ml-4 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:bg-white dark:hover:bg-gray-700 transition-colors">
                                            <component :is="child.icon" v-if="child.icon" class="w-4 h-4" />
                                            {{ child.label }}
                                        </Link>
                                    </div>
                                </template>
                            </div>
                            
                            <!-- Mobile User Section -->
                            <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-3">
                                <div class="flex items-center gap-3 mb-3">
                                    <img 
                                        v-if="$page.props.jetstream.managesProfilePhotos && $page.props.auth?.user"
                                        :src="$page.props.auth.user.profile_photo_url" 
                                        class="h-10 w-10 rounded-full">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $page.props.auth?.user?.name }}</p>
                                        <p class="text-sm text-gray-500">{{ $page.props.auth?.user?.email }}</p>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <Link :href="route('profile.show')" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700">
                                        <LuUser class="w-4 h-4" />
                                        Profile
                                    </Link>
                                    <button @click="logout" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                        <LuLogOut class="w-4 h-4" />
                                        Log Out
                                    </button>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </nav>

                <!-- Page Header -->
                <header v-if="$slots.header" class="bg-AA dark:bg-gray-800 shadow-sm border-b border-AA dark:border-gray-700 h-16 ">
                    <div class="default-container">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Main Content -->
                <main class="overflow-x-auto bg-gray-50 dark:bg-gray-900">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>