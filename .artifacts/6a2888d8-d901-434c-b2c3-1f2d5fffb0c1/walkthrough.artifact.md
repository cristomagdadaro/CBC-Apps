# Added Hardware and Data Permissions

I have updated the `AndroidManifest.xml` with the permissions required for notifications, camera, location, media, calendar, and microphone access.

## Changes Made

### [Android]

#### [AndroidManifest.xml](file:///D:/CBC-Apps/CBC-Apps/android/app/src/main/AndroidManifest.xml)
- Added **Camera** permissions and hardware feature declaration.
- Added **Location** (Fine and Coarse) permissions and GPS feature declaration.
- Added **Microphone** (Record Audio) permission.
- Added **Notifications** (Post Notifications) permission for Android 13+.
- Added **Calendar** (Read/Write) permissions.
- Added **Media/Storage** permissions, including legacy storage access and modern Android 13/14+ specific media permissions (`READ_MEDIA_IMAGES`, etc.).

## Verification Results

### Manifest Integrity
The manifest was successfully updated. Gradle warnings regarding deprecated storage permissions were noted; these are expected as we've included both legacy and modern permissions to ensure maximum device compatibility.

> [!IMPORTANT]
> **Runtime Permission Requests**: Simply adding these to the manifest allows the app to *request* them, but you must still trigger the actual permission dialog in your code (e.g., using Capacitor plugins like `@capacitor/camera` or `@capacitor/geolocation`) when the user attempts to use these features.
