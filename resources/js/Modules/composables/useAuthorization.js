import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useAuthorization() {
    const page = usePage();

    const roles = computed(() => page.props?.auth?.roles ?? []);
    const permissions = computed(() => page.props?.auth?.permissions ?? []);

    const hasRole = (role) => roles.value.includes(role);

    const hasAnyRole = (requiredRoles = []) => {
        if (!requiredRoles.length) return true;
        return requiredRoles.some((role) => hasRole(role));
    };

    const hasPermission = (permission) => {
        if (!permission) return true;
        return permissions.value.includes('*') || permissions.value.includes(permission);
    };

    return {
        roles,
        permissions,
        hasRole,
        hasAnyRole,
        hasPermission,
    };
}
