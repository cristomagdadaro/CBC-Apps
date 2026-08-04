<template>
    <transition
        :enter-active-class="currentTransition.enterActive"
        :enter-from-class="currentTransition.enterFrom"
        :enter-to-class="currentTransition.enterTo"
        :leave-active-class="currentTransition.leaveActive"
        :leave-from-class="currentTransition.leaveFrom"
        :leave-to-class="currentTransition.leaveTo"
    >
        <slot />
    </transition>
</template>

<script>
export default {
    name: "TransitionContainer",
    props: {
        type: {
            type: String,
            default: "fade",
            validator: function(value) {
                return ["slide-left", "slide-right", "slide-top", "slide-bottom", "fade", "pop-in", "pop-out"].includes(value);
            }
        }
    },
    computed: {
        currentTransition() {
            return this.transitions[this.type] || this.transitions["fade"];
        }
    },
    data() {
        return {
            transitions: {
                "slide-left": {
                    enterActive: "transition ease-out duration-200",
                    enterFrom: "transform -translate-x-full opacity-0",
                    enterTo: "transform translate-x-0 opacity-100",
                    leaveActive: "transition ease-in duration-75",
                    leaveFrom: "transform translate-x-0 opacity-100",
                    leaveTo: "transform -translate-x-full opacity-0"
                },
                "slide-right": {
                    enterActive: "transition ease-out duration-200",
                    enterFrom: "transform translate-x-full opacity-0",
                    enterTo: "transform translate-x-0 opacity-100",
                    leaveActive: "transition ease-in duration-75",
                    leaveFrom: "transform translate-x-0 opacity-100",
                    leaveTo: "transform translate-x-full opacity-0"
                },
                "slide-top": {
                    enterActive: "transition ease-out duration-200",
                    enterFrom: "transform -translate-y-full opacity-0",
                    enterTo: "transform translate-y-0 opacity-100",
                    leaveActive: "transition ease-in duration-75",
                    leaveFrom: "transform translate-y-0 opacity-100",
                    leaveTo: "transform -translate-y-full opacity-0"
                },
                "slide-bottom": {
                    enterActive: "transition ease-out duration-200",
                    enterFrom: "transform translate-y-2 opacity-0",
                    enterTo: "transform translate-y-0 opacity-100",
                    leaveActive: "transition ease-in duration-100",
                    leaveFrom: "transform translate-y-0 opacity-100",
                    leaveTo: "transform translate-y-2 opacity-0"
                },
                "fade": {
                    enterActive: "transition ease-out duration-200",
                    enterFrom: "opacity-0",
                    enterTo: "opacity-100",
                    leaveActive: "transition ease-in duration-75",
                    leaveFrom: "opacity-100",
                    leaveTo: "opacity-0"
                },
                "pop-in": {
                    enterActive: "transition ease-out duration-200",
                    enterFrom: "transform scale-95 opacity-0",
                    enterTo: "transform scale-100 opacity-100",
                    leaveActive: "transition ease-in duration-150",
                    leaveFrom: "transform scale-100 opacity-100",
                    leaveTo: "transform scale-95 opacity-0"
                },
                "pop-out": {
                    enterActive: "transition ease-out duration-200",
                    enterFrom: "transform scale-105 opacity-0",
                    enterTo: "transform scale-100 opacity-100",
                    leaveActive: "transition ease-in duration-150",
                    leaveFrom: "transform scale-100 opacity-100",
                    leaveTo: "transform scale-105 opacity-0"
                }
            }
        };
    }
};
</script>
