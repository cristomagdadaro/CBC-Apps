<script>
export default {
    name: "ActionHeaderLayout",
    props: {
        routeLink: {
            type: String,
        },
        title: {
            type: String,
            required: false,
            default: null,
        },
        subtitle: {
            type: String,
            default: "",
        },
        breadcrumbs: {
            type: Array,
            default: () => [],
        },
    },
    computed: {
        resolvedRouteLink() {
            if (!this.routeLink) {
                return null;
            }

            if (this.routeLink.startsWith("/") || this.routeLink.startsWith("http://") || this.routeLink.startsWith("https://")) {
                return this.routeLink;
            }

            try {
                return route(this.routeLink);
            } catch (error) {
                return this.routeLink;
            }
        },
        normalizedBreadcrumbs() {
            return (this.breadcrumbs || [])
                .map((breadcrumb) => ({
                    label: breadcrumb?.label || "",
                    href: this.resolveLink(breadcrumb?.href || breadcrumb?.route || null, breadcrumb?.params),
                    current: !!breadcrumb?.current,
                }))
                .filter((breadcrumb) => breadcrumb.label);
        },
    },
    methods: {
        resolveLink(link, params = undefined) {
            if (!link) {
                return null;
            }

            if (typeof link === "string" && (link.startsWith("/") || link.startsWith("http://") || link.startsWith("https://"))) {
                return link;
            }

            try {
                return params === undefined ? route(link) : route(link, params);
            } catch (error) {
                return link;
            }
        },
    },
};
</script>

<template>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center py-3 sm:py-4 select-none gap-4 sm:gap-6 w-full">
        <div class="leading-tight flex flex-col min-w-0 max-w-full">
            <div
                v-if="normalizedBreadcrumbs.length"
                class="mb-1.5 flex flex-wrap items-center gap-2 text-[0.65rem] sm:text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                <template
                    v-for="(breadcrumb, index) in normalizedBreadcrumbs"
                    :key="`${breadcrumb.label}-${index}`">
                    <span
                        v-if="index > 0"
                        class="text-slate-300 dark:text-slate-600">
                        /
                    </span>
                    <Link
                        v-if="breadcrumb.href && !breadcrumb.current"
                        :href="breadcrumb.href"
                        class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        {{ breadcrumb.label }}
                    </Link>
                    <span
                        v-else
                        class="text-slate-800 dark:text-slate-200">
                        {{ breadcrumb.label }}
                    </span>
                </template>
            </div>
            <Link
                v-if="resolvedRouteLink"
                :href="resolvedRouteLink"
                class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate">
                {{ title }}
            </Link>
            <h1
                v-else
                class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight truncate">
                {{ title }}
            </h1>
            <p
                v-if="subtitle"
                class="mt-1 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 truncate">
                {{ subtitle }}
            </p>
        </div>
        <div class="flex items-center gap-2.5 overflow-x-auto max-w-full scrollbar-none py-1 w-full sm:w-auto shrink-0">
            <slot />
        </div>
    </div>
</template>
