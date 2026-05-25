<script>
export default {
    name: "PersonnelRegistrationLink",
};
</script>
>
<template>
    <transition-container type="pop-in" :duration="1000">
        <nav-link v-show="!route().current('personnels.registrations.index')" :href="route('personnels.registrations.index')" class="flex items-center bg-amber-600 text-white px-2 py-1 rounded active:shadow-inner gap-1 text-sm active:scale-95 active:text-amber-600 hover:bg-amber-300 hover:text-amber-600">
            <LuUserCheck />
            Registration Approvals
        </nav-link>
    </transition-container>
</template>
