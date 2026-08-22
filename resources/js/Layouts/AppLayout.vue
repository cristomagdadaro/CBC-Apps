<script>
import ApplicationMark from "@/Components/ApplicationMark.vue";
import Banner from "@/Components/Banner.vue";
import NotificationToast from "@/Components/NotificationToast.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import SwitchBtn from "@/Components/Buttons/SwitchBtn.vue";
import {router} from "@inertiajs/vue3";
import NavigationItems from "@/Components/NavigationItems.vue";
import Dropdown from "@/Components/Dropdown.vue";

export default {
    name: "AppLayout",
    components: {
        ApplicationMark,
        Banner,
        NotificationToast,
        ResponsiveNavLink,
        SwitchBtn,
        NavigationItems,
        Dropdown,
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
            navigationMode: this.$page.props.layout_navigation_mode || "top",
            services: [
                {
                    label: "Main Dashboard",
                    href: "dashboard",
                    icon: "LuLayoutDashboard",
                },
                {
                    label: "Inventory System",
                    href: null,
                    icon: "LuPackage",
                    permission: "inventory.manage",
                    children: [
                        {
                            label: "Dashboard",
                            href: "transactions.dashboard",
                            moduleKey: "inventory",
                            icon: "LuBarChart3",
                        },
                        {
                            label: "Transactions List",
                            href: "transactions.index",
                            moduleKey: "inventory",
                            icon: "LuArrowLeftRight",
                        }, {
                            label: "Incoming",
                            href: "transactions.incoming",
                            moduleKey: "inventory",
                            icon: "LuTrendingUp",
                        }, {
                            label: "Outgoing",
                            href: "transactions.outgoing",
                            moduleKey: "inventory",
                            icon: "LuTrendingDown",
                        }, {
                            label: "Recounting",
                            href: "transactions.recounting",
                            moduleKey: "inventory",
                            icon: "LuClipboardCheck",
                        },
                        {
                            label: "Barcode Printing",
                            href: "inventory.barcodes.print",
                            moduleKey: "inventory",
                            icon: "LuBarcode",
                        },
                        {
                            label: "Items List",
                            href: "items.index",
                            moduleKey: "inventory",
                            icon: "LuBox",
                        },
                        {
                            label: "Suppliers List",
                            href: "suppliers.index",
                            moduleKey: "inventory",
                            icon: "LuTruck",
                        },
                        {
                            label: "Reports Recieved",
                            href: "suppEquipReports.index",
                            permission: "equipment.report.manage",
                            moduleKey: "inventory",
                            icon: "LuFileText",
                        },
                    ],
                },
                {
                    label: "Equipment Logger",
                    href: "equipment-logger.dashboard",
                    permission: "laboratory.logger.manage",
                    moduleKey: "laboratory_dashboard",
                    icon: "LuMicroscope",
                },
                {
                    label: "Personnel List",
                    href: "personnels.index",
                    moduleKey: "inventory",
                    icon: "LuUsers",
                },
                {
                    label: "FES Request",
                    href: "accessUseRequest.index",
                    permission: "fes.request.approve",
                    moduleKey: "fes",
                    icon: "LuShield",
                },
                {
                    label: "Bookings and Rentals",
                    href: null,
                    icon: "LuDollarSign",
                    children: [
                        {
                            label: "Vehicle Requests List",
                            href: "rentals.vehicle.index",
                            permission: "rental.vehicle.manage",
                            moduleKey: "rentals",
                            icon: "LuCar",
                        },
                        {
                            label: "Venue Requests List",
                            href: "rentals.venue.index",
                            permission: "rental.venue.manage",
                            moduleKey: "rentals",
                            icon: "LuBuilding",
                        },
                        {
                            label: "Google Calendar Sync",
                            href: "rentals.calendar.index",
                            permission: "rental.vehicle.manage",
                            moduleKey: "rentals",
                            icon: "LuCalendarDays",
                        },
                    ],
                    permission: "rental.vehicle.manage",
                },
                {
                    label: "Form Builder",
                    href: null,
                    icon: "LuWrench",
                    children: [
                        {
                            label: "Event Forms List",
                            href: "forms.index",
                            permission: "event.forms.manage",
                            moduleKey: "forms",
                            icon: "LuCalendar",
                        },
                        {
                            label: "Certificate Generator",
                            href: "certificates.index",
                            permission: "event.certificates.manage",
                            moduleKey: "forms",
                            icon: "LuAward",
                        },
                    ],
                },
                {
                    label: "Research",
                    href: null,
                    icon: "LuFlaskConical",
                    permission: "research.dashboard.view",
                    children: [
                        {
                            label: "Research Dashboard Summary",
                            href: "research.dashboard",
                            permission: "research.dashboard.view",
                            moduleKey: "research",
                            icon: "LuLayoutDashboard",
                        },
                        {
                            label: "Projects Dashboard",
                            href: "research.projects.index",
                            permission: "research.projects.view",
                            moduleKey: "research",
                            icon: "LuLayers",
                        },
                        {
                            label: "Sample Inventory",
                            href: "research.samples.inventory",
                            permission: "research.samples.manage",
                            moduleKey: "research",
                            icon: "LuBox",
                        },
                    ],
                },
                {
                    label: "Go Link Manager",
                    href: "golinks.index",
                    permission: "golinks.manage",
                    moduleKey: "golink",
                    icon: "LuLink",
                },
                {
                    label: "System Settings",
                    href: null,
                    icon: "LuSettings",
                    roles: ["admin"],
                    children: [
                        {
                            label: "Options Module",
                            href: "system.options.index",
                            roles: ["admin"],
                            moduleKey: "options",
                            icon: "LuSliders",
                        },
                        {
                            label: "User Management",
                            href: "system.users.index",
                            permission: "users.manage",
                            roles: ["admin"],
                            icon: "LuUserCog",
                        },
                        {
                            label: "Email Templates",
                            href: "dev.views.index",
                            roles: ["admin"],
                            icon: "LuCode2",
                        },
                    ],
                },
                {
                    label: "Manuals & Guides",
                    href: "manuals.index",
                    icon: "LuBookOpen",
                },
                {
                    label: "SproutAi Microservice",
                    href: "sproutai.microservice",
                    icon: "LuBot",
                    newTab: true,
                },
            ],
        };
    },
    created() {
        if (typeof window === "undefined") return;

        this.services = this.services.map((service) => ({
            ...service,
            isOpen: Array.isArray(service.children)
                ? route().current(service.href || "") || !!service.children.find((child) => route().current(child.href))
                : false,
        }));

        if (this.$page.props.layout_navigation_mode) {
            this.navigationMode = this.$page.props.layout_navigation_mode;
        }

        const savedSidebarCollapsed = window.localStorage.getItem("layout.sidebar.collapsed");
        if (savedSidebarCollapsed === "true" || savedSidebarCollapsed === "false") {
            this.sidebarCollapsed = savedSidebarCollapsed === "true";
        }

        // Close dropdowns on route change
        router.on("navigate", () => {
            this.showingNavigationDropdown = false;
            this.sidebarOpen = false;
            this.userDropdownOpen = false;
            this.teamDropdownOpen = false;
            if (typeof document !== "undefined") {
                document.body.classList.remove("overflow-hidden");
            }
        });
    },
    beforeUnmount() {
        if (typeof document !== "undefined") {
            document.body.classList.remove("overflow-hidden");
        }
    },
    watch: {
        showingNavigationDropdown(isOpen) {
            if (typeof document !== "undefined") {
                if (isOpen) {
                    document.body.classList.add("overflow-hidden");
                } else {
                    document.body.classList.remove("overflow-hidden");
                }
            }
        },
        sidebarOpen(isOpen) {
            if (typeof document !== "undefined") {
                if (isOpen) {
                    document.body.classList.add("overflow-hidden");
                } else {
                    document.body.classList.remove("overflow-hidden");
                }
            }
        },
    },
    computed: {
        isSidebarModeResponsive() {
            if (typeof window === "undefined") return false;
            const isXl = window.matchMedia("(min-width: 1280px)").matches;
            return this.$page.props.layout_navigation_mode === "sidebar" && isXl;
        },
        isSidebarMode() {
            return this.navigationMode === "sidebar" || this.rawLayoutMode === "sidebar";
        },
        rawLayoutMode() {
            return this.$page.props.layout_navigation_mode || "top";
        },
        visibleServices() {
            return this.services.filter((service) => {
                if (!this.canAccessService(service)) return false;
                if (!Array.isArray(service.children)) return true;
                return this.visibleChildren(service).length > 0 || !!service.href;
            });
        },
        formattedPermissions() {
            const permissions = this.$currentPermissions || [];
            if (permissions.length === 1 && permissions[0] === "*") {
                return [{name: "*", label: "All Permissions"}];
            }
            return permissions.map((permission) => {
                if (!permission || permission === "*") {
                    return {name: permission, label: "All Permissions"};
                }
                const base = permission.split(".").slice(0, -1).join(" ");
                return {name: permission, label: this.formatLabel(base)};
            });
        },
        rolesList() {
            return this.$currentRoles || [];
        },
        singleRole() {
            return this.rolesList.length === 1;
        },
        singleRoleLabel() {
            if (!this.singleRole) return "";
            return this.formatLabel(this.rolesList[0] || "");
        },
        currentRouteName() {
            return route().current();
        },
        deploymentModules() {
            return this.$page.props.deployment_access?.modules || {};
        },
    },
    methods: {
        formatLabel(text) {
            if (!text) return "";
            const cleaned = String(text).replace(/_/g, " ");
            return cleaned
                .split(" ")
                .map((s) => s.charAt(0).toUpperCase() + s.slice(1))
                .join(" ");
        },
        canAccessDirectService(service) {
            if (!service) return false;
            return (
                this.hasPermission(service.permission) &&
                this.hasAnyRole(service.roles || []) &&
                this.hasVisibleModule(service.moduleKey)
            );
        },
        hasPermission(permission) {
            if (!permission) return true;
            if (this.$isAdminUser) return true;
            const permissions = this.$currentPermissions ?? [];
            return permissions.includes("*") || permissions.includes(permission);
        },
        hasAnyRole(roles = []) {
            if (!roles.length) return true;
            if (this.$isAdminUser) return true;
            const currentRoles = this.$currentRoles ?? [];
            return roles.some((role) => currentRoles.includes(role));
        },
        hasVisibleModule(moduleKey) {
            if (!moduleKey) return true;

            const moduleState = this.deploymentModules?.[moduleKey];
            if (!moduleState) return true;

            if (moduleState.mode === "deactivated") {
                return false;
            }

            if (this.$isAdminUser) return true;

            return moduleState.available !== false;
        },
        canAccessService(service) {
            if (!service) return false;
            if (Array.isArray(service.children) && service.children.length) {
                if (
                    !this.hasAnyRole(service.roles || []) ||
                    !this.hasVisibleModule(service.moduleKey)
                ) {
                    return false;
                }

                if (service.href && this.canAccessDirectService(service)) {
                    return true;
                }

                return this.visibleChildren(service).length > 0;
            }

            return this.canAccessDirectService(service);
        },
        visibleChildren(service) {
            if (!Array.isArray(service?.children)) return [];
            return service.children.filter((child) => {
                const effectiveChild = {
                    ...child,
                    permission: child.permission !== undefined ? child.permission : service.permission,
                    roles: child.roles !== undefined ? child.roles : service.roles,
                    moduleKey: child.moduleKey !== undefined ? child.moduleKey : service.moduleKey
                };
                return this.canAccessDirectService(effectiveChild);
            });
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
            if (typeof window !== "undefined") {
                window.localStorage.setItem(
                    "layout.sidebar.collapsed",
                    String(this.sidebarCollapsed)
                );
            }
        },
        handleHamburgerClick() {
            if (!this.isSidebarModeResponsive) {
                this.showingNavigationDropdown = !this.showingNavigationDropdown;
                return;
            }
            if (
                typeof window !== "undefined" &&
                window.matchMedia("(min-width: 1024px)").matches
            ) {
                this.toggleSidebarCollapse();
                return;
            }
            this.sidebarOpen = !this.sidebarOpen;
        },
        closeSidebar() {
            this.sidebarOpen = false;
        },
        switchToTeam(team) {
            router.put(
                route("current-team.update"),
                {
                    team_id: team.id,
                },
                {preserveState: false}
            );
        },
        logout() {
            router.post(route("logout"));
        },
    },
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-[#0B1120] text-slate-900 dark:text-slate-100">
        <Head :title="title"/>

        <Banner/>
        <NotificationToast/>

        <div class="flex min-h-screen">
            <!-- Sidebar Navigation (Desktop) -->
            <Transition
                enter-active-class="transition-all duration-300 ease-in-out"
                enter-from-class="opacity-0 -translate-x-full"
                enter-to-class="opacity-100 translate-x-0"
                leave-active-class="transition-all duration-300 ease-in-out"
                leave-from-class="opacity-100 translate-x-0"
                leave-to-class="opacity-0 -translate-x-full"
            >
                <aside
                    v-if="isSidebarModeResponsive"
                    class="hidden max-h-screen overflow-visible sticky top-0 xl:flex xl:flex-col xl:sticky inset-y-0 left-0 z-40 bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border-r border-slate-200/60 dark:border-slate-800/60 shadow-lg xl:shadow-none transition-all duration-300"
                    :class="sidebarCollapsed ? 'w-20' : 'w-64'"
                >
                    <!-- Sidebar Header -->
                    <div
                        class="bg-transparent border-b border-slate-200/60 dark:border-slate-800/60 h-16 relative"
                    >
                        <!-- User Profile Summary -->
                        <div v-if="!sidebarCollapsed" class="flex items-center h-full pl-3">
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="
                    $page.props.jetstream.managesProfilePhotos && $page.props.auth?.user
                  "
                                    :src="$page.props.auth.user.profile_photo_url"
                                    :alt="$page.props.auth?.user?.name"
                                    class="h-10 w-10 rounded-full object-cover ring-2 ring-white dark:ring-gray-700 shadow-sm"
                                />
                                <div
                                    v-else
                                    class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-sm"
                                >
                                    {{ $page.props.auth?.user?.name?.charAt(0)?.toUpperCase() || "U" }}
                                </div>
                                <div class="flex-1 min-w-0 leading-tight">
                                    <div
                                        class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate uppercase"
                                    >
                                        {{ $page.props.auth?.user?.name || "User" }}
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 truncate font-medium">
                                        {{ singleRoleLabel || "Member" }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="p-3 border-b border-slate-200/60 dark:border-slate-800/60 flex justify-center h-full items-center"
                        >
                            <img
                                v-if="
                  $page.props.jetstream.managesProfilePhotos && $page.props.auth?.user
                "
                                :src="$page.props.auth.user.profile_photo_url"
                                class="h-10 w-10 rounded-full object-cover ring-2 ring-white dark:ring-gray-700"
                            />
                            <div
                                v-else
                                class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold"
                            >
                                {{ $page.props.auth?.user?.name?.charAt(0)?.toUpperCase() || "U" }}
                            </div>
                        </div>
                    </div>

                    <!-- Collapsed Toggle (when collapsed) -->
                    <button
                        @click="toggleSidebarCollapse"
                        :class="{ 'rotate-180': !sidebarCollapsed }"
                        class="absolute -right-3 top-20 bg-indigo-600 dark:bg-indigo-500 text-white p-1 rounded-full shadow-lg hover:bg-indigo-700 dark:hover:bg-indigo-400 transition-all duration-300 z-50 transform hover:scale-110 ring-2 ring-white dark:ring-slate-900"
                        title="Expand sidebar"
                    >
                        <LuChevronRight class="w-4 h-4"/>
                    </button>

                    <!-- Navigation Items -->
                    <nav class="flex-1 overflow-y-auto overflow-x-hidden overflow-visible py-4 px-3 space-y-1 custom-scrollbar">
                        <template v-for="service in visibleServices" :key="service.label">
                            <!-- Single Link -->
                            <component
                                :is="service.newTab ? 'a' : 'Link'"
                                v-if="!service.children || !visibleChildren(service).length"
                                :href="route(service.href)"
                                :target="service.newTab ? '_blank' : undefined"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 group relative"
                                :class="
                  isServiceActive(service)
                    ? 'bg-indigo-50/80 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 shadow-sm border border-indigo-100/50 dark:border-indigo-500/20'
                    : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 border border-transparent hover:text-slate-900 dark:hover:text-slate-200'
                "
                            >
                                <component
                                    :is="service.icon"
                                    class="w-5 h-5 flex-shrink-0 transition-colors"
                                    :class="
                    isServiceActive(service)
                      ? 'text-indigo-600 dark:text-indigo-400'
                      : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300'
                  "
                                />
                                <span v-if="!sidebarCollapsed" class="truncate">{{ service.label }}</span>
                                <span
                                    v-if="isServiceActive(service) && !sidebarCollapsed"
                                    class="ml-auto w-1.5 h-1.5 rounded-full bg-indigo-500 dark:bg-indigo-400"
                                ></span>

                                <!-- Tooltip for collapsed state -->
                                <div
                                    v-if="sidebarCollapsed"
                                    class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50"
                                >
                                    {{ service.label }}
                                </div>
                            </component>

                            <!-- Dropdown Group -->
                            <div v-else class="space-y-1">
                                <button
                                    @click="service.isOpen = !service.isOpen"
                                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 group"
                                    :class="
                    isServiceActive(service)
                      ? 'bg-indigo-50/80 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 shadow-sm border border-indigo-100/50 dark:border-indigo-500/20'
                      : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 border border-transparent hover:text-slate-900 dark:hover:text-slate-200'
                  "
                                >
                                    <component
                                        :is="service.icon"
                                        class="w-5 h-5 flex-shrink-0 transition-colors"
                                        :class="
                      isServiceActive(service)
                        ? 'text-indigo-600 dark:text-indigo-400'
                        : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300'
                    "
                                    />
                                    <span v-if="!sidebarCollapsed" class="flex-1 text-left truncate">{{
                                            service.label
                                        }}</span>
                                    <LuChevronDown
                                        v-if="!sidebarCollapsed"
                                        class="w-4 h-4 transition-transform duration-200"
                                        :class="service.isOpen ? 'rotate-180 text-indigo-500 dark:text-indigo-400' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300'"
                                    />

                                    <!-- Collapsed dropdown indicator -->
                                    <div
                                        v-if="sidebarCollapsed && isServiceActive(service)"
                                        class="absolute left-0 w-1 h-8 bg-indigo-500 dark:bg-indigo-400 rounded-r-full"
                                    ></div>
                                </button>

                                <!-- Expanded children -->
                                <div
                                    v-if="!sidebarCollapsed && service.isOpen"
                                    class="ml-5 pl-3 border-l-2 border-slate-200/60 dark:border-slate-800/60 space-y-1 py-1"
                                >
                                    <component
                                        :is="child.newTab ? 'a' : 'Link'"
                                        v-for="child in visibleChildren(service)"
                                        :key="child.label"
                                        :href="route(child.href)"
                                        :target="child.newTab ? '_blank' : undefined"
                                        class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition-all duration-200"
                                        :class="
                      isChildActive(child)
                        ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/50 dark:bg-indigo-500/10 font-medium shadow-sm border border-indigo-100/30 dark:border-indigo-500/10'
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 border border-transparent'
                    "
                                    >
                                        <component
                                            :is="child.icon"
                                            v-if="child.icon"
                                            class="w-4 h-4 transition-colors"
                                            :class="isChildActive(child) ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 group-hover:text-slate-500'"
                                        />
                                        <span
                                            v-else
                                            class="w-1.5 h-1.5 rounded-full transition-colors"
                                            :class="isChildActive(child) ? 'bg-indigo-500 dark:bg-indigo-400' : 'bg-slate-300 dark:bg-slate-600'"
                                        ></span>
                                        <span class="truncate">{{ child.label }}</span>
                                    </component>
                                </div>
                            </div>
                        </template>
                    </nav>

                    <!-- Sidebar Footer -->
                    <div class="p-3 border-t border-slate-200/60 dark:border-slate-800/60 space-y-1">
                        <Link
                            :href="route('profile.show')"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 transition-colors group relative"
                        >
                            <LuUser
                                class="w-5 h-5 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300 transition-colors"
                            />
                            <span v-if="!sidebarCollapsed">Profile</span>
                            <div
                                v-if="sidebarCollapsed"
                                class="absolute left-full ml-2 px-2 py-1 bg-slate-900 text-white text-xs rounded-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50"
                            >
                                Profile
                            </div>
                        </Link>
                        <button
                            @click="logout"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50/50 dark:hover:bg-red-500/10 transition-colors group relative border border-transparent"
                        >
                            <LuLogOut class="w-5 h-5 group-hover:scale-110 transition-transform"/>
                            <span v-if="!sidebarCollapsed">Log Out</span>
                            <div
                                v-if="sidebarCollapsed"
                                class="absolute left-full ml-2 px-2 py-1 bg-slate-900 text-white text-xs rounded-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50"
                            >
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
                leave-to-class="opacity-0"
            >
                <div
                    v-if="isSidebarModeResponsive && sidebarOpen"
                    class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm xl:hidden"
                    @click="closeSidebar"
                ></div>
            </Transition>

            <!-- Mobile Sidebar Drawer -->
            <Transition
                enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="-translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transition-transform duration-300 ease-in"
                leave-from-class="translate-x-0"
                leave-to-class="-translate-x-full"
            >
                <aside
                    v-if="isSidebarModeResponsive && sidebarOpen"
                    class="fixed inset-y-0 left-0 z-50 w-72 bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl shadow-2xl xl:hidden flex flex-col border-r border-slate-200/50 dark:border-slate-800/50"
                >
                    <!-- Mobile Header -->
                    <div
                        class="h-16 flex items-center justify-between px-4 border-b border-slate-200/50 dark:border-slate-800/50 bg-transparent"
                    >
                        <span class="font-bold text-slate-900 dark:text-white">FES System</span>
                        <button
                            @click="closeSidebar"
                            class="p-2 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-200/50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800 transition-colors"
                        >
                            <LuX class="w-5 h-5"/>
                        </button>
                    </div>

                    <!-- Mobile User Info -->
                    <div
                        class="p-4 border-b border-slate-200/50 dark:border-slate-800/50 bg-transparent"
                    >
                        <div class="flex items-center gap-3">
                            <img
                                v-if="
                  $page.props.jetstream.managesProfilePhotos && $page.props.auth?.user
                "
                                :src="$page.props.auth.user.profile_photo_url"
                                class="h-12 w-12 rounded-full object-cover ring-2 ring-white dark:ring-slate-800 shadow-sm"
                            />
                            <div
                                v-else
                                class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-sm"
                            >
                                {{ $page.props.auth?.user?.name?.charAt(0)?.toUpperCase() || "U" }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-slate-900 dark:text-white truncate">
                                    {{ $page.props.auth?.user?.name }}
                                </p>
                                <p class="text-sm text-slate-500 dark:text-slate-400 truncate">
                                    {{ $page.props.auth?.user?.email }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Navigation -->
                    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 custom-scrollbar">
                        <template v-for="service in visibleServices" :key="`mobile-${service.label}`">
                            <Link
                                v-if="!service.children || !visibleChildren(service).length"
                                :href="route(service.href)"
                                @click="closeSidebar"
                                class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition-colors"
                                :class="
                  isServiceActive(service)
                    ? 'bg-indigo-50/80 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 shadow-sm border border-indigo-100/50 dark:border-indigo-500/20'
                    : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 border border-transparent'
                "
                            >
                                <component
                                    :is="service.icon"
                                    class="w-5 h-5 transition-colors"
                                    :class="isServiceActive(service) ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400'"
                                />
                                {{ service.label }}
                            </Link>

                            <div v-else>
                                <div
                                    class="px-3 py-2 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider"
                                >
                                    {{ service.label }}
                                </div>
                                <div class="ml-2 space-y-1">
                                    <Link
                                        v-for="child in visibleChildren(service)"
                                        :key="`mobile-child-${child.label}`"
                                        :href="route(child.href)"
                                        @click="closeSidebar"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-colors"
                                        :class="
                      isChildActive(child)
                        ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/50 dark:bg-indigo-500/10 font-medium shadow-sm border border-indigo-100/30 dark:border-indigo-500/10'
                        : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 border border-transparent hover:text-slate-900 dark:hover:text-slate-200'
                    "
                                    >
                                        <component :is="child.icon" v-if="child.icon" class="w-4 h-4 transition-colors" :class="isChildActive(child) ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400'"/>
                                        <span v-else class="w-1.5 h-1.5 rounded-full transition-colors" :class="isChildActive(child) ? 'bg-indigo-500 dark:bg-indigo-400' : 'bg-slate-300 dark:bg-slate-600'"></span>
                                        {{ child.label }}
                                    </Link>
                                </div>
                            </div>
                        </template>
                    </nav>

                    <!-- Mobile Footer -->
                    <div class="p-4 border-t border-slate-200/50 dark:border-slate-800/50 space-y-2">
                        <Link
                            :href="route('profile.show')"
                            @click="closeSidebar"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <LuUser class="w-5 h-5 text-slate-400"/>
                            Profile Settings
                        </Link>
                        <button
                            @click="logout"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50/50 dark:hover:bg-red-500/10 transition-colors border border-transparent"
                        >
                            <LuLogOut class="w-5 h-5"/>
                            Log Out
                        </button>
                    </div>
                </aside>
            </Transition>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Top Navigation (Top mode or mobile) -->
                <nav
                    v-if="!isSidebarModeResponsive || !isSidebarMode"
                    class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border-b border-slate-200/50 dark:border-slate-800/50 sticky top-0 z-30"
                >
                    <div class="mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between h-16">
                            <!-- Left: Logo & Mobile Menu -->
                            <div class="flex flex-1 min-w-0 items-center gap-4">
                                <button
                                    @click="isSidebarMode ? (sidebarOpen = true) : (showingNavigationDropdown = !showingNavigationDropdown)"
                                    class="xl:hidden p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition-colors"
                                >
                                    <LuMenu class="w-6 h-6"/>
                                </button>

                                <Link :href="route('dashboard')" class="flex items-center gap-2 flex-shrink-0">
                                    <ApplicationMark class="h-8 md:h-10 lg:h-12 w-auto text-indigo-600 dark:text-indigo-500"/>
                                    <span
                                        class="font-bold text-xl text-slate-900 dark:text-white tracking-tight"
                                    >{{ $appName }}</span
                                    >
                                </Link>

                                <!-- Desktop Navigation -->
                                <div class="hidden xl:flex flex-1 min-w-0 items-center gap-1 ml-8 overflow-visible custom-scrollbar py-2 mask-linear-fade">
                                    <template
                                        v-for="service in visibleServices"
                                        :key="`top-${service.label}`"
                                    >
                                        <Dropdown
                                            v-if="service.children && visibleChildren(service).length"
                                            align="left"
                                            width="56"
                                        >
                                            <template #trigger>
                                                <button
                                                    class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors whitespace-nowrap"
                                                    :class="
                            isServiceActive(service)
                              ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/80 dark:bg-indigo-500/10'
                              : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-800/50'
                          "
                                                >
                                                    <component :is="service.icon" class="w-4 h-4 flex-shrink-0"/>
                                                    {{ service.label }}
                                                    <LuChevronDown class="w-4 h-4 flex-shrink-0 opacity-50"/>
                                                </button>
                                            </template>
                                            <template #content>
                                                <div class="py-1">
                                                    <Link
                                                        v-for="child in visibleChildren(service)"
                                                        :key="child.label"
                                                        :href="route(child.href)"
                                                        class="flex whitespace-nowrap items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                                    >
                                                        <component
                                                            :is="child.icon"
                                                            v-if="child.icon"
                                                            class="w-4 h-4 text-slate-400"
                                                        />
                                                        {{ child.label }}
                                                    </Link>
                                                </div>
                                            </template>
                                        </Dropdown>

                                        <Link
                                            v-else
                                            :href="route(service.href)"
                                            class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors whitespace-nowrap"
                                            :class="
                        isServiceActive(service)
                          ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/80 dark:bg-indigo-500/10'
                          : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-800/50'
                      "
                                        >
                                            <component :is="service.icon" class="w-4 h-4 flex-shrink-0"/>
                                            {{ service.label }}
                                        </Link>
                                    </template>
                                </div>
                            </div>

                            <!-- Right: User Menu -->
                            <div class="flex items-center gap-2">
                                <!-- Notifications (placeholder) -->
                                <button
                                    class="p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition-colors relative"
                                >
                                    <LuBell class="w-5 h-5"/>
                                    <span
                                        class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white dark:border-slate-900"
                                    ></span>
                                </button>

                                <!-- User Dropdown -->
                                <div class="hidden xl:flex items-center">
                                    <Dropdown align="right" width="48">
                                        <template #trigger>
                                            <button
                                                class="flex items-center gap-3 p-1.5 pr-3 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                            >
                                                <img
                                                    v-if="
                                      $page.props.jetstream.managesProfilePhotos &&
                                      $page.props.auth?.user
                                    "
                                                    :src="$page.props.auth.user.profile_photo_url"
                                                    class="h-8 w-8 rounded-full object-cover ring-2 ring-white dark:ring-gray-700"
                                                />
                                                <div
                                                    v-else
                                                    class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm"
                                                >
                                                    {{
                                                        $page.props.auth?.user?.name?.charAt(0)?.toUpperCase() || "U"
                                                    }}
                                                </div>
                                                <div class="hidden sm:block text-left">
                                                    <p
                                                        class="text-sm font-semibold text-slate-900 dark:text-white leading-tight truncate"
                                                    >
                                                        {{ $page.props.auth?.user?.name }}
                                                    </p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-tight font-medium">
                                                        {{ singleRoleLabel }}
                                                    </p>
                                                </div>
                                                <LuChevronDown class="w-4 h-4 text-slate-400 hidden sm:block"/>
                                            </button>
                                        </template>

                                        <template #content>
                                            <Link
                                                :href="route('profile.show')"
                                                class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                            >
                                                <LuUser class="w-4 h-4"/>
                                                Profile
                                            </Link>
                                            <button
                                                @click="logout"
                                                class="w-full flex items-center gap-2 px-4 py-2 text-left text-sm text-red-600 dark:text-red-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors border border-transparent"
                                            >
                                                <LuLogOut class="w-4 h-4"/>
                                                Log Out
                                            </button>
                                        </template>
                                    </Dropdown>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Navigation Backdrop Overlay -->
                    <Transition
                        enter-active-class="transition-opacity duration-200"
                        enter-from-class="opacity-0"
                        enter-to-class="opacity-100"
                        leave-active-class="transition-opacity duration-150"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <div
                            v-if="showingNavigationDropdown && (!isSidebarModeResponsive || !isSidebarMode)"
                            @click="showingNavigationDropdown = false"
                            class="fixed inset-0 bg-slate-900/60 z-40 xl:hidden backdrop-blur-xs"
                        ></div>
                    </Transition>

                    <!-- Mobile Navigation Menu -->
                    <Transition
                        enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 -translate-y-2"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition-all duration-200 ease-in"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 -translate-y-2"
                    >
                        <div
                            v-if="showingNavigationDropdown && (!isSidebarModeResponsive || !isSidebarMode)"
                            class="xl:hidden relative z-50 border-t border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-2xl max-h-[80vh] overflow-y-auto"
                        >
                            <div class="px-4 py-3 space-y-1">
                                <template
                                    v-for="service in visibleServices"
                                    :key="`mobile-top-${service.label}`"
                                >
                                    <Link
                                        v-if="!service.children || !visibleChildren(service).length"
                                        :href="route(service.href)"
                                        @click="showingNavigationDropdown = false"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors"
                                        :class="
                      isServiceActive(service)
                        ? 'bg-indigo-50/80 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400'
                        : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'
                    "
                                    >
                                        <component :is="service.icon" class="w-5 h-5"/>
                                        {{ service.label }}
                                    </Link>

                                    <div v-else class="space-y-1">
                                        <div
                                            class="px-3 py-2 text-xs font-bold text-slate-400 uppercase tracking-wider"
                                        >
                                            {{ service.label }}
                                        </div>
                                        <Link
                                            v-for="child in visibleChildren(service)"
                                            :key="`mobile-top-child-${child.label}`"
                                            :href="route(child.href)"
                                            @click="showingNavigationDropdown = false"
                                            class="flex items-center gap-3 px-3 py-2 ml-4 rounded-xl text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                        >
                                            <component :is="child.icon" v-if="child.icon" class="w-4 h-4"/>
                                            {{ child.label }}
                                        </Link>
                                    </div>
                                </template>
                            </div>

                            <!-- Mobile User Section -->
                            <div class="border-t border-slate-200 dark:border-slate-800 px-4 py-3">
                                <div class="flex items-center gap-3 mb-3">
                                    <img
                                        v-if="$page.props.jetstream.managesProfilePhotos && $page.props.auth?.user"
                                        :src="$page.props.auth.user.profile_photo_url"
                                        class="h-10 w-10 rounded-full"
                                    />
                                    <div>
                                        <p class="font-medium text-slate-900 dark:text-white">
                                            {{ $page.props.auth?.user?.name }}
                                        </p>
                                        <p class="text-sm text-slate-500">
                                            {{ $page.props.auth?.user?.email }}
                                        </p>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <Link
                                        :href="route('profile.show')"
                                        class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800"
                                    >
                                        <LuUser class="w-4 h-4"/>
                                        Profile
                                    </Link>
                                    <button
                                        @click="logout"
                                        class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
                                    >
                                        <LuLogOut class="w-4 h-4"/>
                                        Log Out
                                    </button>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </nav>

                <!-- Page Header -->
                <header
                    v-if="$slots.header"
                    class="bg-white/50 dark:bg-slate-900/50 backdrop-blur-md shadow-sm border-b border-slate-200/60 dark:border-slate-800/60 min-h-16 h-auto py-1 sm:py-0 transition-all z-20 relative"
                >
                    <div class="mx-auto px-4 sm:px-6 lg:px-8 w-full flex-1">
                        <slot name="header"/>
                    </div>
                </header>

                <!-- Main Content -->
                <main class="overflow-x-auto bg-transparent flex-1">
                    <slot/>
                </main>
            </div>
        </div>
    </div>
</template>
