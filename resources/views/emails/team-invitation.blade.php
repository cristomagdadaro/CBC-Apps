@component('mail::message')
# Team Invitation

Hello! 

You have been invited to join the **{{ $invitation->team->name }}** team on OneCBC.

@if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::registration()))
If you don't have an account yet, please create one first by clicking the button below. Once your account is set up, you can accept this invitation to join the team.

@component('mail::button', ['url' => route('register')])
{{ __('Create Account') }}
@endcomponent

If you already have an account, you can accept the invitation right away:

@else
You can accept this invitation by clicking the button below:
@endif


@component('mail::button', ['url' => $acceptUrl])
{{ __('Accept Invitation') }}
@endcomponent

If you weren't expecting this invitation, feel free to ignore this email.

Best regards,<br>
DA-Crop Biotechnology Center
@endcomponent
