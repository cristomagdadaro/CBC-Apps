<script>
export default {
    name: "GoLinksHeaderActions",
};
</script>

<template>
    <ActionHeaderLayout title="Go Link Manager" subtitle="Manage branded short links backed by the WordPress redirects table." :route-link="route('golinks.index')">
        <transition-container type="pop-in" :duration="1000">
            <nav-link v-show="!route().current('golinks.create')" :href="route('golinks.create')" class="flex items-center bg-emerald-600 text-white px-2 py-1 rounded active:shadow-inner gap-1 text-sm hover:bg-emerald-700">
                <LuPlus class="w-4 h-4" />
                <span>New Go Link</span>
            </nav-link>
        </transition-container>
    </ActionHeaderLayout>
</template>
