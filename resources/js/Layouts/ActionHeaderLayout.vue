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
    },
}
</script>

<template>
    <div class="flex justify-between items-center py-2 select-none text-gray-100 drop-shadow-md">
        <div class="leading-tight flex flex-col">
          <Link v-if="resolvedRouteLink" :href="resolvedRouteLink" class="font-medium uppercase hover:underline">
              {{ title }}
          </Link>
          <label v-else class="font-medium uppercase">
              {{ title }}
          </label>
          <span v-if="subtitle" class="text-xs">{{ subtitle }}</span>
        </div>
        <div class="flex justify-between gap-2 items-center">
            <slot />
        </div>
    </div>
</template>
