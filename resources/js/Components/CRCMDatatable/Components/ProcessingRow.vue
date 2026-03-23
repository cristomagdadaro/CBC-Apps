<template>
    <tr id="dtRowProcessing">
        <td class="text-center p-8 " :colspan="colspan+1">
            <div class="flex justify-center gap-1 items-center">
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
