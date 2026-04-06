<script>

export default {
    name: 'ActionHeaderLayout',
    props: {
        routeLink: {
            type: String,
        },
        title: {
            type: String,
            required: true,
        },
        subtitle: {
            type: String,
            default: '',
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

            if (
                this.routeLink.startsWith('/') ||
                this.routeLink.startsWith('http://') ||
                this.routeLink.startsWith('https://')
            ) {
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
                    label: breadcrumb?.label || '',
                    href: this.resolveLink(breadcrumb?.href || breadcrumb?.route || null, breadcrumb?.params),
                    current: !!breadcrumb?.current,
                }))
                .filter((breadcrumb) => breadcrumb.label)
        },
    },
    methods: {
        resolveLink(link, params = undefined) {
            if (!link) {
                return null;
            }

            if (
                typeof link === 'string' &&
                (link.startsWith('/') || link.startsWith('http://') || link.startsWith('https://'))
            ) {
                return link;
            }

            try {
                return params === undefined ? route(link) : route(link, params);
            } catch (error) {
                return link;
            }
        },
    },
}
</script>

<template>
    <div class="flex justify-between items-center py-2 select-none text-gray-100 drop-shadow-md gap-4">
        <div class="leading-tight flex flex-col min-w-0">
          <div v-if="normalizedBreadcrumbs.length" class="mb-1 flex flex-wrap items-center gap-2 text-xs text-gray-200/90">
              <template v-for="(breadcrumb, index) in normalizedBreadcrumbs" :key="`${breadcrumb.label}-${index}`">
                  <span v-if="index > 0" class="text-gray-300">/</span>
                  <Link
                      v-if="breadcrumb.href && !breadcrumb.current"
                      :href="breadcrumb.href"
                      class="hover:underline"
                  >
                      {{ breadcrumb.label }}
                  </Link>
                  <span v-else class="font-medium text-white/95">
                      {{ breadcrumb.label }}
                  </span>
              </template>
          </div>
          <Link v-if="resolvedRouteLink" :href="resolvedRouteLink" class="font-medium uppercase hover:underline truncate">
              {{ title }}
          </Link>
          <label v-else class="font-medium uppercase truncate">
              {{ title }}
          </label>
          <span v-if="subtitle" class="text-xs truncate">{{ subtitle }}</span>
        </div>
        <div class="flex justify-between gap-2 items-center flex-wrap shrink-0">
            <slot />
        </div>
    </div>
</template>
