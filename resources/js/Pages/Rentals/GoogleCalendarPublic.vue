<script setup>
import GuideTourControls from "@/Components/GuideTourControls.vue";
</script>

<template>
        <Head title="Google Calendar View" />
        <GuideTourControls guide-key="google-calendar-public" compact />
        <div data-guide="google-calendar-embed" class="h-screen w-full bg-gray-700 overflow-hidden">
                <iframe src="https://calendar.google.com/calendar/embed?height=600&wkst=1&ctz=Asia%2FManila&showPrint=0&title=OneCBC%20Portal&mode=MONTH&showCalendars=0&showTabs=0&showNav=0&src=YzFkZTdiZmI4MTY3YTBkMDIwZDI4MjZiNzE1NjE3ZDA2YjdkMTJjNTYxZGQ0MmI0YmIzNTllNTdmMjA2ODc2ZkBncm91cC5jYWxlbmRhci5nb29nbGUuY29t&color=%23ad1457"
                        style="border: 0" width="100%" height="100%" frameborder="0" scrolling="no">
                </iframe>
        </div>
</template>

<style>
/* Important: If this is a standalone page, 
   ensure the root elements don't have default padding/margin 
*/
html,
body,
#app {
        margin: 0;
        padding: 0;
        height: 100%;
        width: 100%;
}
</style>
