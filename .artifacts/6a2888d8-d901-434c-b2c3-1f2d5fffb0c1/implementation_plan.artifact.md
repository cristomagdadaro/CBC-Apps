# Add Required App Permissions

The OneCBC app requires access to several device features. I will add the necessary permissions to the `AndroidManifest.xml` to enable these capabilities.

## User Review Required

> [!IMPORTANT]
> Adding permissions to the manifest is the first step. However, for these features to work in a Capacitor app, you will also need to:
> 1.  Install the corresponding Capacitor plugins (e.g., `@capacitor/camera`, `@capacitor/geolocation`, etc.) if your app logic needs to interact with these features from JavaScript.
> 2.  Request permissions at runtime within your app's code.
>
> If you are just wrapping a website that uses these features, the WebView might handle some requests, but explicit manifest declarations are still mandatory.

## Proposed Changes

### [Android]

#### [MODIFY] [AndroidManifest.xml](file:///D:/CBC-Apps/CBC-Apps/android/app/src/main/AndroidManifest.xml)
Add the following permissions:
- **Camera**: `CAMERA`
- **Location**: `ACCESS_FINE_LOCATION`, `ACCESS_COARSE_LOCATION`
- **Notifications**: `POST_NOTIFICATIONS`
- **Microphone**: `RECORD_AUDIO`
- **Photos & Videos**: `READ_MEDIA_IMAGES`, `READ_MEDIA_VIDEO`, `READ_MEDIA_VISUAL_USER_SELECTED` (Android 13/14+), and `READ_EXTERNAL_STORAGE` (for compatibility).
- **Calendar**: `READ_CALENDAR`, `WRITE_CALENDAR`

I will also add `<uses-feature>` tags for hardware components like the camera, marking them as optional so the app remains compatible with devices that lack specific hardware.

## Verification Plan

### Automated Tests
- None applicable for manifest changes, but I will verify the XML structure is valid.

### Manual Verification
- Deploy the app and check if it now requests or allows access to these features when triggered by the app logic or website.
