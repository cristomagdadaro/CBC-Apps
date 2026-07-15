# Implementation Plan - Mobile App Download Buttons

Add download buttons for Android and iOS on the landing page (`Welcome.vue`). The Android APK will be served directly from its build directory, while the iOS button will remain in a "Coming Soon" state.

## User Review Required

> [!IMPORTANT]
> The Android APK will be served from `android/app/build/outputs/apk/debug/OneCBCPortal.apk`. Ensure this file exists and is readable by the web server in your environment.

## Proposed Changes

### [Backend] Routes

#### [MODIFY] [shared.php](file:///D:/CBC-Apps/CBC-Apps/routes/web/shared.php)
- Add a new route `GET /download/android` that returns the APK file using `response()->download()`.

### [Frontend] Landing Page

#### [MODIFY] [Welcome.vue](file:///D:/CBC-Apps/CBC-Apps/resources/js/Pages/Welcome.vue)
- Import `Smartphone`, `Download`, `Apple` (or similar) from `lucide-vue-next`.
- Add a "Download our Mobile App" section below the "Apps & Services" header or integrated into the hero section.
- Implement the Android download button linking to the `/download/android` route.
- Implement the iOS button as a disabled/placeholder state indicating it's coming soon.

## Verification Plan

### Manual Verification
- Verify the new route `/download/android` initiates the download of `OneCBCPortal.apk`.
- Verify the UI layout in `Welcome.vue` looks consistent with the existing theme.
- Verify the iOS button is clearly marked as unavailable.
