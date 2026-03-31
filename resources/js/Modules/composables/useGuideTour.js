import { nextTick, onMounted, ref } from "vue";
import {
  resolveTourSteps,
  TOUR_REGISTRY,
  TOUR_STORAGE_KEYS,
} from "@/Modules/guides/tourRegistry";

function canUseStorage() {
  return typeof window !== "undefined" && !!window.localStorage;
}

function readStorage(key, fallback = null) {
  if (!canUseStorage()) {
    return fallback;
  }

  const value = window.localStorage.getItem(key);
  return value === null ? fallback : value;
}

function writeStorage(key, value) {
  if (!canUseStorage()) {
    return;
  }

  window.localStorage.setItem(key, value);
}

function hasPrivacyConsent() {
  return readStorage(TOUR_STORAGE_KEYS.privacyConsent, "false") === "true";
}

function hasSeenGuide(key) {
  return readStorage(`${TOUR_STORAGE_KEYS.seenPrefix}${key}`, "false") === "true";
}

function markGuideSeen(key) {
  writeStorage(`${TOUR_STORAGE_KEYS.seenPrefix}${key}`, "true");
}

function autoGuidesEnabled() {
  return readStorage(TOUR_STORAGE_KEYS.autoGuides, "true") !== "false";
}

function normalizeSteps(key) {
  return resolveTourSteps(key)
    .map((step) => {
      const selector = step?.element;
      const element = selector ? document.querySelector(selector) : null;

      if (!selector || element) {
        return {
          ...step,
          element: element ?? selector,
        };
      }

      return null;
    })
    .filter(Boolean);
}

export function useGuideTour(guideKey, options = {}) {
  const isRunning = ref(false);
  const autoEnabled = ref(autoGuidesEnabled());
  const consented = ref(hasPrivacyConsent());

  const setPrivacyConsent = (value) => {
    consented.value = !!value;
    writeStorage(TOUR_STORAGE_KEYS.privacyConsent, value ? "true" : "false");
  };

  const setAutoGuides = (value) => {
    autoEnabled.value = !!value;
    writeStorage(TOUR_STORAGE_KEYS.autoGuides, value ? "true" : "false");
  };

  const toggleAutoGuides = () => {
    setAutoGuides(!autoEnabled.value);
  };

  const startGuide = async (overrideGuideKey = null) => {
    const targetGuideKey = overrideGuideKey || guideKey;

    if (!targetGuideKey || typeof window === "undefined") {
      return false;
    }

    const steps = normalizeSteps(targetGuideKey);
    if (!steps.length) {
      return false;
    }

    const [{ driver }] = await Promise.all([import("driver.js"), nextTick()]);

    isRunning.value = true;

    const instance = driver({
      showProgress: true,
      allowClose: true,
      smoothScroll: true,
      steps,
      nextBtnText: "Next",
      prevBtnText: "Back",
      doneBtnText: "Done",
      onDestroyed: () => {
        isRunning.value = false;
        markGuideSeen(targetGuideKey);
      },
    });

    instance.drive();
    return true;
  };

  const maybeStartGuide = async () => {
    if (!guideKey || !consented.value || !autoEnabled.value || hasSeenGuide(guideKey)) {
      return false;
    }

    await nextTick();
    return startGuide();
  };

  if (options.autoStart !== false) {
    onMounted(async () => {
      const delay = Number(options.autoDelay ?? 450);
      window.setTimeout(() => {
        maybeStartGuide();
      }, delay);
    });
  }

  return {
    autoEnabled,
    consented,
    guideDefinition: TOUR_REGISTRY[guideKey] ?? TOUR_REGISTRY["guest-page"],
    hasPrivacyConsent,
    hasSeenGuide,
    isRunning,
    markGuideSeen,
    maybeStartGuide,
    setAutoGuides,
    setPrivacyConsent,
    startGuide,
    toggleAutoGuides,
  };
}
