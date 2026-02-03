<script>
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";
import NavLink from "@/Components/NavLink.vue";
import PrinterIcon from "@/Components/Icons/PrinterIcon.vue";

export default {
    name: "BarcodePrintingLink",
    components: {TransitionContainer, NavLink, PrinterIcon},
};
</script>
>
<template>
    <transition-container type="pop-in" :duration="1000">
        <nav-link v-show="!route().current('inventory.barcodes.print')" :href="route('inventory.barcodes.print')" class="flex items-center bg-AB text-white px-2 py-1 rounded active:shadow-inner gap-1 text-sm active:scale-95 active:text-red-600 hover:bg-gray-300 hover:text-red-600">
            <printer-icon class="w-4 h-4"/>
            <span>Print Barcode</span>
        </nav-link>
    </transition-container>
</template>