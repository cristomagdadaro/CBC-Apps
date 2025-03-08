<template>
    <transition
        :enter-active-class="transitions[type]?.enterActive"
        :enter-from-class="transitions[type]?.enterFrom"
        :enter-to-class="transitions[type]?.enterTo"
        :leave-active-class="transitions[type]?.leaveActive"
        :leave-from-class="transitions[type]?.leaveFrom"
        :leave-to-class="transitions[type]?.leaveTo"
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
                return [
                    "slide-left", "slide-right", "slide-top", "slide-bottom",
                    "fade", "pop-in", "pop-out"
                ].includes(value);
            }
        },
        duration: {
            type: Number,
            default: 300
        }
    },
    data() {
        return {
            // Transition rules mapped by type with opposite exit directions
            transitions: {
                "slide-left": {
                    enterActive: "transition ease-out duration-" + this.duration,
                    enterFrom: "transform -translate-x-full opacity-0",
                    enterTo: "transform translate-x-0 opacity-100",
                    leaveActive: "transition ease-in duration-" + this.duration,
                    leaveFrom: "transform translate-x-0 opacity-100",
                    leaveTo: "transform translate-x-full opacity-0" // Now exits to the right
                },
                "slide-right": {
                    enterActive: "transition ease-out duration-" + this.duration,
                    enterFrom: "transform translate-x-full opacity-0",
                    enterTo: "transform translate-x-0 opacity-100",
                    leaveActive: "transition ease-in duration-" + this.duration,
                    leaveFrom: "transform translate-x-0 opacity-100",
                    leaveTo: "transform -translate-x-full opacity-0" // Now exits to the left
                },
                "slide-top": {
                    enterActive: "transition ease-out duration-" + this.duration,
                    enterFrom: "transform translate-y-full opacity-0",  // Enter from bottom
                    enterTo: "transform translate-y-0 opacity-100",
                    leaveActive: "transition ease-in duration-" + this.duration,
                    leaveFrom: "transform translate-y-0 opacity-100",
                    leaveTo: "transform -translate-y-full opacity-0" // Exit to bottom
                },
                "slide-bottom": {
                    enterActive: "transition ease-out duration-" + this.duration,
                    enterFrom: "transform -translate-y-full opacity-0",  // Enter from top
                    enterTo: "transform translate-y-0 opacity-100",
                    leaveActive: "transition ease-in duration-" + this.duration,
                    leaveFrom: "transform translate-y-0 opacity-100",
                    leaveTo: "transform translate-y-full opacity-0" // Exit to top
                },
                "fade": {
                    enterActive: "transition ease-out duration-" + this.duration,
                    enterFrom: "opacity-0",
                    enterTo: "opacity-100",
                    leaveActive: "transition ease-in duration-" + this.duration,
                    leaveFrom: "opacity-100",
                    leaveTo: "opacity-0"
                },
                "pop-in": {
                    enterActive: "transition ease-out duration-" + this.duration,
                    enterFrom: "transform scale-90 opacity-0",
                    enterTo: "transform scale-100 opacity-100",
                    leaveActive: "transition ease-in duration-" + this.duration,
                    leaveFrom: "transform scale-100 opacity-100",
                    leaveTo: "transform scale-90 opacity-0"
                },
                "pop-out": {
                    enterActive: "transition ease-out duration-" + this.duration,
                    enterFrom: "transform scale-110 opacity-0",
                    enterTo: "transform scale-100 opacity-100",
                    leaveActive: "transition ease-in duration-" + this.duration,
                    leaveFrom: "transform scale-100 opacity-100",
                    leaveTo: "transform scale-110 opacity-0"
                }
            }
        };
    }
};
</script>
