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
        target: {
            type: String,
            required: false,
            default: '_self'
        },
    }
}
</script>

<template>
    <Link
        v-if="href !== '#'"
        :href="href"
        :target="target"
        :rel="target === '_blank' ? 'noopener noreferrer' : null"
        class="inline-flex items-center justify-center transition-colors duration-200"
    >
        <slot />
    </Link>
    <button v-else type="button" class="inline-flex items-center justify-center transition-colors duration-200">
        <slot />
    </button>
</template>