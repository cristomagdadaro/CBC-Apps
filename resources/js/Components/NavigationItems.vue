<script>
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

export default {
    name: 'NavigationItems',
    components: { NavLink, ResponsiveNavLink },
    props: {
        services: { type: Array, required: true },
        mode: { type: String, default: 'top' }, // 'top' | 'sidebar' | 'mobile'
        visibleChildren: { type: Function, required: true },
        isServiceActive: { type: Function, required: true },
    },
    methods: {
        handleClick() {
            this.$emit('item-clicked');
        }
    }
}
</script>

<template>
    <div class="flex flex-col">
        <template v-for="service in services" :key="service.label">
            <template v-if="mode === 'mobile'">
                <ResponsiveNavLink
                    v-if="service.href"
                    :href="route(service.href)"
                    :active="isServiceActive(service)"
                    @click="handleClick"
                >
                    {{ service.label }}
                </ResponsiveNavLink>

                <div v-else-if="service.children" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ service.label }}
                </div>

                <div v-if="service.children" class="ms-4 mt-1 flex flex-col gap-1 ps-3">
                    <ResponsiveNavLink v-for="child in visibleChildren(service)" :key="child.label" :href="route(child.href)" :active="route().current(child.href)" @click="handleClick">
                        {{ child.label }}
                    </ResponsiveNavLink>
                </div>
            </template>

            <template v-else-if="mode === 'sidebar'">
                <template v-if="service.children && visibleChildren(service).length">
                    <div class="px-3 pt-3 pb-1 text-xs text-gray-400">
                        {{ service.label }}
                    </div>
                    <NavLink v-if="service.href" :href="route(service.href)" :active="route().current(service.href)">
                        {{ service.label }}
                    </NavLink>
                    <NavLink v-for="child in visibleChildren(service)" :key="child.label" :href="route(child.href)" :active="route().current(child.href)">
                        {{ child.label }}
                    </NavLink>
                </template>
                <NavLink v-else :href="route(service.href)" :active="route().current(service.href)">
                    {{ service.label }}
                </NavLink>
            </template>

            <template v-else>
                <!-- top navigation -->
                <NavLink v-if="service.href" :href="route(service.href)" :active="route().current(service.href)">
                    {{ service.label }}
                </NavLink>
                <Dropdown v-else-if="service.children && visibleChildren(service).length" align="right" width="60">
                    <template #trigger>
                        <button class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200">
                            {{ service.label }}
                        </button>
                    </template>

                    <template #content>
                        <div class="w-60">
                            <template v-for="child in visibleChildren(service)" :key="child.label">
                                <NavLink :href="route(child.href)" :active="route().current(child.href)">
                                    {{ child.label }}
                                </NavLink>
                            </template>
                        </div>
                    </template>
                </Dropdown>
            </template>
        </template>
    </div>
</template>
