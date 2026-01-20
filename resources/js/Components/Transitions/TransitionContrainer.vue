<template>
    <transition
        :enter-active-class="transitionClasses.enterActive"
        :enter-from-class="transitionClasses.enterFrom"
        :enter-to-class="transitionClasses.enterTo"
        :leave-active-class="transitionClasses.leaveActive"
        :leave-from-class="transitionClasses.leaveFrom"
        :leave-to-class="transitionClasses.leaveTo"
    >
        <slot />
    </transition>
</template>

<script>
const buildTransition = (config) => (duration) => ({
    enterActive: `transition ease-out duration-${duration}`,
    enterFrom: config.enterFrom,
    enterTo: config.enterTo,
    leaveActive: `transition ease-in duration-${duration}`,
    leaveFrom: config.leaveFrom,
    leaveTo: config.leaveTo,
});

const TRANSITION_BUILDERS = {
    "slide-left": buildTransition({
        enterFrom: "transform -translate-x-full opacity-0",
        enterTo: "transform translate-x-0 opacity-100",
        leaveFrom: "transform translate-x-0 opacity-100",
        leaveTo: "transform translate-x-full opacity-0",
    }),
    "slide-right": buildTransition({
        enterFrom: "transform translate-x-full opacity-0",
        enterTo: "transform translate-x-0 opacity-100",
        leaveFrom: "transform translate-x-0 opacity-100",
        leaveTo: "transform -translate-x-full opacity-0",
    }),
    "slide-top": buildTransition({
        enterFrom: "transform translate-y-full opacity-0",
        enterTo: "transform translate-y-0 opacity-100",
        leaveFrom: "transform translate-y-0 opacity-100",
        leaveTo: "transform -translate-y-full opacity-0",
    }),
    "slide-bottom": buildTransition({
        enterFrom: "transform -translate-y-full opacity-0",
        enterTo: "transform translate-y-0 opacity-100",
        leaveFrom: "transform translate-y-0 opacity-100",
        leaveTo: "transform translate-y-full opacity-0",
    }),
    fade: buildTransition({
        enterFrom: "opacity-0",
        enterTo: "opacity-100",
        leaveFrom: "opacity-100",
        leaveTo: "opacity-0",
    }),
    "pop-in": buildTransition({
        enterFrom: "transform scale-90 opacity-0",
        enterTo: "transform scale-100 opacity-100",
        leaveFrom: "transform scale-100 opacity-100",
        leaveTo: "transform scale-90 opacity-0",
    }),
    "pop-out": buildTransition({
        enterFrom: "transform scale-110 opacity-0",
        enterTo: "transform scale-100 opacity-100",
        leaveFrom: "transform scale-100 opacity-100",
        leaveTo: "transform scale-110 opacity-0",
    }),
};

const AVAILABLE_TYPES = Object.keys(TRANSITION_BUILDERS);

export default {
    name: "TransitionContainer",
    props: {
        type: {
            type: String,
            default: "fade",
            validator: (value) => AVAILABLE_TYPES.includes(value),
        },
        duration: {
            type: Number,
            default: 300,
        },
    },
    computed: {
        transitionClasses() {
            const builder = TRANSITION_BUILDERS[this.type] || TRANSITION_BUILDERS.fade;
            return builder(this.duration);
        },
    },
};
</script>
