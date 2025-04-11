<script>
export default {
    name: "PersonnelCard",
    props: {
        data: Object,
    }
}
</script>

<template>
    <div class="border border-gray-700 p-2 rounded-md gap-2 motion-safe:hover:scale-[1.02] duration-200">
        <div class="flex flex-col text-center w-full'">
            <span class="font-bold border-b border-gray-700">
                {{ `${data.fname} ${data.mname} ${data.lname} ${data.suffix}.` }}
            </span>
            <span class="text-normal leading-none">
                {{ data.position }}
            </span>
        </div>
        <div class="flex flex-col text-center w-full'">
            <span class="text-xs leading-tight">
                {{ data.email }}
            </span>
            <span class="text-xs leading-tight">
                {{ data.phone }}
            </span>
            <span class="text-xs leading-tight">
                {{ data.address }}
            </span>
        </div>

    </div>
</template>p

<style scoped>

</style>
