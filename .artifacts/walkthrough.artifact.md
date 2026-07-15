# Walkthrough - Mobile App Download Buttons

I have added download buttons for the Android app and a placeholder for the iOS app on the landing page.

## Changes Made

### Backend
- Added a new route `GET /download/android` in `routes/web/shared.php`.
- This route serves the Android APK directly from its build output: `android/app/build/outputs/apk/debug/OneCBCPortal.apk`.
- Added basic error handling to return a 404 if the APK file is missing.

### Frontend
- Modified `Welcome.vue` to include a "Download our Mobile App" section.
- Added an Android download button styled like a standard app store button, linking to the new route.
- Added a disabled iOS button with a "Coming Soon" label to manage user expectations.
- Used the project's existing `LuSmartphone` icon for both buttons, following the established design patterns.

## Verification Results

### Code Review
- [shared.php](file:///D:/CBC-Apps/CBC-Apps/routes/web/shared.php): Added route `download.android`.
- [Welcome.vue](file:///D:/CBC-Apps/CBC-Apps/resources/js/Pages/Welcome.vue): Added the UI section with buttons.

### Manual Verification Required
- Visit the landing page and click the "Android" button to ensure the download starts.
- Note that the iOS button is currently non-functional as requested.
