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
                return ["slide-left", "slide-right", "slide-top", "slide-bottom", "fade", "pop-in", "pop-out"].includes(value);
            }
        }
    },
    data() {
        return {
            // Transition rules mapped by type
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
                    enterFrom: "transform translate-y-full opacity-0",
                    enterTo: "transform translate-y-0 opacity-100",
                    leaveActive: "transition ease-in duration-75",
                    leaveFrom: "transform translate-y-0 opacity-100",
                    leaveTo: "transform translate-y-full opacity-0"
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
                    enterActive: "transition ease-out duration-500",
                    enterFrom: "transform scale-90 opacity-0",
                    enterTo: "transform scale-100 opacity-100",
                    leaveActive: "transition ease-in duration-500",
                    leaveFrom: "transform scale-100 opacity-100",
                    leaveTo: "transform scale-90 opacity-0"
                },
                "pop-out": {
                    enterActive: "transition ease-out duration-500",
                    enterFrom: "transform scale-110 opacity-0",
                    enterTo: "transform scale-100 opacity-100",
                    leaveActive: "transition ease-in duration-75",
                    leaveFrom: "transform scale-100 opacity-500",
                    leaveTo: "transform scale-110 opacity-0"
                }
            },
            // Default transition if no matching type is found
            defaultTransition: {
                enterActive: "transition ease-out duration-200",
                enterFrom: "opacity-0",
                enterTo: "opacity-100",
                leaveActive: "transition ease-in duration-75",
                leaveFrom: "opacity-100",
                leaveTo: "opacity-0"
            }
        };
    }
};
</script>
