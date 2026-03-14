import type { Variants, Easing } from 'framer-motion';

// Easing functions
export const easings: Record<string, Easing> = {
  smooth: [0.4, 0, 0.2, 1],
  bounce: [0.68, -0.55, 0.265, 1.55],
  outExpo: [0.16, 1, 0.3, 1],
  inOutQuart: [0.76, 0, 0.24, 1],
};

// Page transition variants
export const pageTransition: Variants = {
  initial: { opacity: 0, y: 20 },
  animate: { 
    opacity: 1, 
    y: 0,
    transition: {
      duration: 0.3,
      ease: easings.smooth,
    }
  },
  exit: { 
    opacity: 0, 
    y: -20,
    transition: {
      duration: 0.2,
      ease: easings.smooth,
    }
  },
};

// Fade in variants
export const fadeIn: Variants = {
  initial: { opacity: 0 },
  animate: { 
    opacity: 1,
    transition: {
      duration: 0.3,
      ease: easings.smooth,
    }
  },
  exit: { 
    opacity: 0,
    transition: {
      duration: 0.2,
    }
  },
};

// Fade in up variants
export const fadeInUp: Variants = {
  initial: { opacity: 0, y: 20 },
  animate: { 
    opacity: 1, 
    y: 0,
    transition: {
      duration: 0.4,
      ease: easings.outExpo,
    }
  },
};

// Fade in down variants
export const fadeInDown: Variants = {
  initial: { opacity: 0, y: -20 },
  animate: { 
    opacity: 1, 
    y: 0,
    transition: {
      duration: 0.4,
      ease: easings.outExpo,
    }
  },
};

// Scale in variants
export const scaleIn: Variants = {
  initial: { opacity: 0, scale: 0.95 },
  animate: { 
    opacity: 1, 
    scale: 1,
    transition: {
      duration: 0.3,
      ease: easings.smooth,
    }
  },
  exit: { 
    opacity: 0, 
    scale: 0.95,
    transition: {
      duration: 0.2,
    }
  },
};

// Slide in from right variants
export const slideInRight: Variants = {
  initial: { x: '100%', opacity: 0 },
  animate: { 
    x: 0, 
    opacity: 1,
    transition: {
      duration: 0.3,
      ease: easings.outExpo,
    }
  },
  exit: { 
    x: '100%', 
    opacity: 0,
    transition: {
      duration: 0.2,
      ease: easings.smooth,
    }
  },
};

// Slide in from left variants
export const slideInLeft: Variants = {
  initial: { x: '-100%', opacity: 0 },
  animate: { 
    x: 0, 
    opacity: 1,
    transition: {
      duration: 0.3,
      ease: easings.outExpo,
    }
  },
  exit: { 
    x: '-100%', 
    opacity: 0,
    transition: {
      duration: 0.2,
      ease: easings.smooth,
    }
  },
};

// Stagger container variants
export const staggerContainer: Variants = {
  initial: {},
  animate: {
    transition: {
      staggerChildren: 0.1,
      delayChildren: 0.1,
    },
  },
};

// Stagger item variants
export const staggerItem: Variants = {
  initial: { opacity: 0, y: 20 },
  animate: { 
    opacity: 1, 
    y: 0,
    transition: {
      duration: 0.4,
      ease: easings.outExpo,
    }
  },
};

// Card hover animation
export const cardHover = {
  rest: { 
    y: 0, 
    boxShadow: '0 1px 3px rgba(0,0,0,0.1)',
    transition: {
      duration: 0.2,
      ease: easings.smooth,
    }
  },
  hover: { 
    y: -4, 
    boxShadow: '0 10px 15px -3px rgba(0,0,0,0.1)',
    transition: {
      duration: 0.2,
      ease: easings.smooth,
    }
  },
};

// Button tap animation
export const buttonTap = {
  scale: 0.98,
  transition: {
    duration: 0.1,
  },
};

// Modal backdrop variants
export const modalBackdrop: Variants = {
  initial: { opacity: 0 },
  animate: { 
    opacity: 1,
    transition: {
      duration: 0.2,
    }
  },
  exit: { 
    opacity: 0,
    transition: {
      duration: 0.2,
      delay: 0.1,
    }
  },
};

// Modal content variants
export const modalContent: Variants = {
  initial: { opacity: 0, scale: 0.95, y: 20 },
  animate: { 
    opacity: 1, 
    scale: 1, 
    y: 0,
    transition: {
      duration: 0.3,
      ease: easings.outExpo,
    }
  },
  exit: { 
    opacity: 0, 
    scale: 0.95, 
    y: 20,
    transition: {
      duration: 0.2,
      ease: easings.smooth,
    }
  },
};

// Toast notification variants
export const toastVariants: Variants = {
  initial: { 
    opacity: 0, 
    x: 100,
    transition: {
      duration: 0.3,
    }
  },
  animate: { 
    opacity: 1, 
    x: 0,
    transition: {
      duration: 0.3,
      ease: easings.outExpo,
    }
  },
  exit: { 
    opacity: 0, 
    x: 100,
    transition: {
      duration: 0.2,
    }
  },
};

// Sidebar variants
export const sidebarVariants: Variants = {
  expanded: { 
    width: 280,
    transition: {
      duration: 0.3,
      ease: easings.smooth,
    }
  },
  collapsed: { 
    width: 72,
    transition: {
      duration: 0.3,
      ease: easings.smooth,
    }
  },
};

// Dropdown menu variants
export const dropdownVariants: Variants = {
  initial: { 
    opacity: 0, 
    scale: 0.95,
    y: -10,
  },
  animate: { 
    opacity: 1, 
    scale: 1,
    y: 0,
    transition: {
      duration: 0.15,
      ease: easings.smooth,
    }
  },
  exit: { 
    opacity: 0, 
    scale: 0.95,
    y: -10,
    transition: {
      duration: 0.1,
    }
  },
};

// Accordion variants
export const accordionVariants: Variants = {
  collapsed: { 
    height: 0,
    opacity: 0,
    transition: {
      duration: 0.3,
      ease: easings.smooth,
    }
  },
  expanded: { 
    height: 'auto',
    opacity: 1,
    transition: {
      duration: 0.3,
      ease: easings.smooth,
    }
  },
};

// Tab content variants
export const tabContentVariants: Variants = {
  initial: { opacity: 0, x: -10 },
  animate: { 
    opacity: 1, 
    x: 0,
    transition: {
      duration: 0.2,
    }
  },
  exit: { 
    opacity: 0, 
    x: 10,
    transition: {
      duration: 0.2,
    }
  },
};

// List item stagger
export const listItemVariants: Variants = {
  initial: { opacity: 0, x: -20 },
  animate: { 
    opacity: 1, 
    x: 0,
    transition: {
      duration: 0.3,
      ease: easings.smooth,
    }
  },
  exit: { 
    opacity: 0, 
    x: 20,
    transition: {
      duration: 0.2,
    }
  },
};

// Shake animation for errors
export const shakeAnimation = {
  animate: {
    x: [0, -10, 10, -10, 10, 0],
    transition: {
      duration: 0.5,
    },
  },
};

// Bounce animation
export const bounceAnimation = {
  animate: {
    y: [0, -10, 0],
    transition: {
      duration: 0.6,
      repeat: Infinity,
      repeatType: 'reverse' as const,
      ease: easings.bounce,
    },
  },
};
