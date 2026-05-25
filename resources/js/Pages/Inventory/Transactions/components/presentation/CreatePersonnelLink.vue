<script>
export default {
    name: "CreatePersonnelLink",
};
</script>
>
<template>
    <transition-container type="pop-in" :duration="1000">
        <nav-link v-show="!route().current('personnels.create')" :href="route('personnels.create')" class="flex items-center bg-AB text-white px-2 py-1 rounded active:shadow-inner gap-1 text-sm active:scale-95 active:text-red-600 hover:bg-gray-300 hover:text-red-600">
            <LuUserPlus />
            <span>New Personnel</span>
        </nav-link>
    </transition-container>
</template>