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
    setup(props) {
        return {
            routeLink: props.routeLink,
            title: props.title,
            subtitle: props.subtitle,
        };
    },
}
</script>

<template>
    <div class="flex justify-between items-center py-2 select-none">
        <div class="leading-tight">
          <Link v-if="routeLink" :href="routeLink" class="font-medium text-gray-800 dark:text-gray-200 uppercase hover:underline">
              {{ title }}
          </Link>
          <p v-else class="font-medium text-gray-800 dark:text-gray-200 uppercase">
              {{ title }}
          </p>
          <p v-if="subtitle" class="text-xs text-gray-600">{{ subtitle }}</p>
        </div>
        <div class="flex justify-between gap-2 items-center">
            <slot />
        </div>
    </div>
</template>