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
    <div class="flex justify-between items-center py-2 select-none text-gray-100 drop-shadow-md">
        <div class="leading-tight flex flex-col">
          <Link v-if="routeLink" :href="routeLink" class="font-medium uppercase hover:underline">
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