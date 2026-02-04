<template>
    <div class="space-y-6">
        <section>
            <h3 class="text-lg font-bold mb-3">For All Users</h3>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Accessing Your Profile:</h4>
                <ol class="list-decimal list-inside space-y-2 text-sm">
                    <li>Click on your <strong>user avatar or name</strong> in the top right corner</li>
                    <li>Select <strong>Profile</strong> from the dropdown menu</li>
                    <li>You will see your profile page with various update options</li>
                </ol>
            </div>

            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Updating Profile Information:</h4>
                <ol class="list-decimal list-inside space-y-2 text-sm">
                    <li>On your profile page, find the <strong>"Update Profile Information"</strong> section</li>
                    <li>Update the following fields as needed:
                        <ul class="list-disc list-inside ml-4 mt-1">
                            <li>Name</li>
                            <li>Email address</li>
                            <li>Photo / Avatar</li>
                        </ul>
                    </li>
                    <li>Click <strong>Save</strong> to apply changes</li>
                    <li>You will see a confirmation message when successfully updated</li>
                </ol>
            </div>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Updating Your Password:</h4>
                <ol class="list-decimal list-inside space-y-2 text-sm">
                    <li>On your profile page, find the <strong>"Update Password"</strong> section</li>
                    <li>Enter your <strong>current password</strong></li>
                    <li>Enter your <strong>new password</strong> (must be at least 8 characters)</li>
                    <li>Confirm your new password</li>
                    <li>Click <strong>Save</strong> to update your password</li>
                    <li>Your password will be updated immediately</li>
                </ol>
            </div>

            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">⚠️ Password Security Tips:</h4>
                <ul class="list-disc list-inside space-y-2 text-sm">
                    <li>Use a strong password with uppercase, lowercase, numbers, and symbols</li>
                    <li>Never share your password with anyone</li>
                    <li>Change your password regularly (at least every 3 months)</li>
                    <li>Do not reuse old passwords</li>
                    <li>If you suspect unauthorized access, change your password immediately</li>
                </ul>
            </div>

            <div class="bg-purple-50 border-l-4 border-purple-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Other Account Options:</h4>
                <ul class="list-disc list-inside space-y-2 text-sm">
                    <li><strong>Two-Factor Authentication:</strong> Enable for extra security</li>
                    <li><strong>Logout Other Sessions:</strong> Sign out from other devices</li>
                    <li><strong>Delete Account:</strong> Permanently remove your account (careful!)</li>
                </ul>
            </div>
        </section>

        <hr class="my-6">

        <section>
            <h3 class="text-lg font-bold mb-3">For Programmers</h3>
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Profile Management Components:</h4>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li><code class="bg-gray-100 px-1">resources/js/Pages/Profile/Show.vue</code> – Main profile page</li>
                    <li><code class="bg-gray-100 px-1">resources/js/Pages/Profile/Partials/UpdateProfileInformationForm.vue</code> – Profile update form</li>
                    <li><code class="bg-gray-100 px-1">resources/js/Pages/Profile/Partials/UpdatePasswordForm.vue</code> – Password update form</li>
                </ul>
            </div>

            <div class="bg-purple-50 border-l-4 border-purple-400 p-4">
                <h4 class="font-semibold mb-2">User Model & Authentication:</h4>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li><code class="bg-gray-100 px-1">app/Models/User.php</code> – User model with profile fields</li>
                    <li><code class="bg-gray-100 px-1">Laravel Fortify</code> – Built-in authentication system</li>
                    <li>Passwords are hashed using <code class="bg-gray-100 px-1">bcrypt</code> algorithm</li>
                </ul>
            </div>
        </section>
    </div>
</template>

<script>
export default {
    name: 'ProfilePasswordTopic',
}
</script>
