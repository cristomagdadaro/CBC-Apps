<script>
export default {
    name: "PersonnelCard",
    props: {
        data: Object,
    },
};
</script>

<template>
    <div class="gap-2 rounded-md border border-gray-700 p-2 duration-200 motion-safe:hover:scale-[1.02]">
        <div class="w-full' flex flex-col text-center">
            <span class="border-b border-gray-700 font-bold">
                {{ `${data.fname} ${data.mname} ${data.lname} ${data.suffix}.` }}
            </span>
            <span class="text-normal leading-none">
                {{ data.position }}
            </span>
        </div>
        <div class="w-full' flex flex-col text-center">
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
</template>
p

<style scoped></style>
