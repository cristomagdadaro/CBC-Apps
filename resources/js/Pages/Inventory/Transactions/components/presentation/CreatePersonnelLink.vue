<script>
import { UserPlus } from "lucide-vue-next";

export default {
    name: "CreatePersonnelLink",
    components: { UserPlus },
};
</script>

<template>
    <transition-container
        type="pop-in"
        :duration="500">
        <nav-link
            v-show="!route().current('personnels.create')"
            :href="route('personnels.create')"
            class="shadow-xs inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition-all hover:bg-slate-50 active:scale-95 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
            <UserPlus class="h-4 w-4 text-indigo-500" />
            <span>New Personnel</span>
        </nav-link>
    </transition-container>
</template>
