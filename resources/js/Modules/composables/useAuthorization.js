import { computed } from 'vue';
import { useAppContext } from '@/Modules/composables/useAppContext';

export function useAuthorization() {
    const { currentRoles, currentPermissions, isAdminUser } = useAppContext();

    const roles = computed(() => currentRoles.value);
    const permissions = computed(() => currentPermissions.value);

    const hasRole = (role) => isAdminUser.value || roles.value.includes(role);

    const hasAnyRole = (requiredRoles = []) => {
        if (!requiredRoles.length) return true;
        return isAdminUser.value || requiredRoles.some((role) => hasRole(role));
    };

    const hasPermission = (permission) => {
        if (!permission) return true;
        return isAdminUser.value || permissions.value.includes('*') || permissions.value.includes(permission);
    };

    return {
        roles,
        permissions,
        isAdminUser,
        hasRole,
        hasAnyRole,
        hasPermission,
    };
}
