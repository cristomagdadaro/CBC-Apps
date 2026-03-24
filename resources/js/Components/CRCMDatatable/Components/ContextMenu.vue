<template>
    <transition-container>
        <div v-show="visible" :style="{ top: `${y}px`, left: `${x}px` }" class="context-menu rounded p-1">
            <slot />
        </div>
    </transition-container>
</template>
<script>
import TransitionContainer from "@/Components/CustomDropdown/Components/TransitionContainer.vue";

export default {
    name: "ContextMenu",
    components: {TransitionContainer},
    data() {
        return {
            visible: false,
            x: 0,
            y: 0
        };
    },
    methods: {
        showMenu(event) {
            event.preventDefault();
            let sidebarAdjustment = this.isSidebarOpen ? 0 : 200;

            this.x = event.clientX + sidebarAdjustment - 250;
            this.y = event.clientY - 240;
            this.visible = true;
        },
        hideMenu() {
            this.visible = false;
        },
        handleAction(action) {
            console.log(`Action: ${action}`);
            this.hideMenu();
        }
    },
    mounted() {
        document.addEventListener('contextmenu', this.showMenu);
        document.addEventListener('click', this.hideMenu);
    },
    beforeDestroy() {
        document.removeEventListener('contextmenu', this.showMenu);
        document.removeEventListener('click', this.hideMenu);
    },
    computed: {
        isSidebarOpen() {
            //return this.$store.state.isSidebarOpen;
            return true; // Placeholder, replace with actual logic to determine if sidebar is open
        }
    }
};
</script>
<style scoped>
.context-menu {
    position: fixed;
    background: white;
    border: 1px solid #ccc;
    box-shadow: 2px 1px 5px rgba(0, 0, 0, 0.02);
    z-index: 9999;
}
.context-menu ul {
    list-style: none;
    margin: 0;
    padding: 0;
}
.context-menu li {
    padding: 8px 12px;
    cursor: pointer;
}
.context-menu li:hover {
    background: #f0f0f0;
}
</style>
