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
    <div class="flex w-full select-none flex-col items-start justify-between gap-4 py-3 sm:flex-row sm:items-center sm:gap-6 sm:py-4">
        <div class="flex min-w-0 max-w-full flex-col leading-tight">
            <div
                v-if="normalizedBreadcrumbs.length"
                class="mb-1.5 flex flex-wrap items-center gap-2 text-[0.65rem] font-semibold uppercase tracking-wider text-slate-500 sm:text-xs dark:text-slate-400">
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
                        class="transition-colors hover:text-indigo-600 dark:hover:text-indigo-400">
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
                class="truncate text-lg font-black tracking-tight text-slate-900 transition-colors hover:text-indigo-600 sm:text-xl dark:text-white dark:hover:text-indigo-400">
                {{ title }}
            </Link>
            <h1
                v-else
                class="truncate text-lg font-black tracking-tight text-slate-900 sm:text-xl dark:text-white">
                {{ title }}
            </h1>
            <p
                v-if="subtitle"
                class="mt-1 truncate text-xs font-medium text-slate-500 sm:text-sm dark:text-slate-400">
                {{ subtitle }}
            </p>
        </div>
        <div class="scrollbar-none flex w-full max-w-full shrink-0 items-center gap-2.5 overflow-x-auto py-1 sm:w-auto">
            <slot />
        </div>
    </div>
</template>
