import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { PUBLIC_SERVICES } from "@/Modules/constants/publicServices";

export function resolveAuthContext(pageProps = {}) {
  const auth = pageProps?.auth ?? {};
  const roles = Array.isArray(auth.roles) ? auth.roles : [];
  const permissions = Array.isArray(auth.permissions) ? auth.permissions : [];
  const user = auth.user ?? null;
  const isAdminUser = !!user?.is_admin || roles.includes("admin");

  return {
    user,
    roles,
    permissions,
    isAdminUser,
  };
}

export function useAppContext() {
  const page = usePage();

  const authContext = computed(() => resolveAuthContext(page.props));
  const currentUser = computed(() => authContext.value.user);
  const currentRoles = computed(() => authContext.value.roles);
  const currentPermissions = computed(() => authContext.value.permissions);
  const isAdminUser = computed(() => authContext.value.isAdminUser);
  const deploymentAccess = computed(() => page.props?.deployment_access ?? {});
  const publicServices = computed(() => PUBLIC_SERVICES);

  return {
    authContext,
    currentUser,
    currentRoles,
    currentPermissions,
    isAdminUser,
    deploymentAccess,
    publicServices,
  };
}
