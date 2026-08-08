@component('mail::message')
# Verify Your Email Address

Hello {{ $registration->full_name ?: 'there' }},

Thank you for registering with OneCBC. To help us secure your account and proceed with your registration, please verify your email address.

@component('mail::button', ['url' => $verificationUrl])
Verify Email Address
@endcomponent

This link will remain active for 7 days. If you didn't request this registration, you can safely ignore this email.

Best regards,<br>
DA-Crop Biotechnology Center
@endcomponent
