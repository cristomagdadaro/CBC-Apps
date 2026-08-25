<template>
    <tr id="dtRowProcessing">
        <td
            class="p-8 text-center"
            :colspan="colspan + 1">
            <div class="flex items-center justify-center gap-1">
                <loader-icon />
                <span>processing...</span>
            </div>
        </td>
    </tr>
</template>
<script setup>
import LoaderIcon from "@/Components/Icons/LoaderIcon.vue";
const props = defineProps({
    colspan: Number,
});
</script>
