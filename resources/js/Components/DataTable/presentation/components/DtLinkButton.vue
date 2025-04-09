<script>
import { Link } from '@inertiajs/vue3';

export default {
    name: "DtLinkButton",
    components: {Link},
    props: {
        href: {
            type: String,
            required: false,
            default: '#'
        },
    }
}
</script>

<template>
    <Link v-if="href !== '#'" :href="href" class="text-blue-500 hover:text-blue-700 flex gap-1 items-center justify-center w-fit">
        <slot />
    </Link>
    <button v-else class="text-blue-500 hover:text-blue-700 flex gap-1 items-center justify-center w-fit">
        <slot />
    </button>
</template>

<style scoped>

</style>
