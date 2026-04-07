<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Invitation</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f7fb; font-family:Arial, Helvetica, sans-serif; color:#111827;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f7fb; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px; background-color:#ffffff; border-radius:12px; box-shadow:0 6px 18px rgba(15, 23, 42, 0.08); overflow:hidden;">
                    <tr>
                        <td style="padding:20px 24px; border-bottom:1px solid #eef2f7;">
                            <h2 style="margin:0; font-size:20px; font-weight:700; color:#0f172a;">Team Invitation</h2>
                            <p style="margin:6px 0 0; font-size:14px; color:#64748b;">You have been invited to join a team.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 24px;">
                            <p style="margin:0 0 16px; font-size:14px; color:#111827;">
                                {{ __('You have been invited to join the :team team!', ['team' => $invitation->team->name]) }}
                            </p>

                            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::registration()))
                                <p style="margin:0 0 16px; font-size:14px; color:#111827;">
                                    {{ __('If you do not have an account, you may create one by clicking the button below. After creating an account, you may click the invitation acceptance button in this email to accept the team invitation:') }}
                                </p>

                                <p style="margin:16px 0; text-align:center;">
                                    <a href="{{ route('register') }}" style="display:inline-block; padding:10px 20px; background-color:#0f172a; color:#ffffff; text-decoration:none; border-radius:6px; font-size:14px; font-weight:600;">{{ __('Create Account') }}</a>
                                </p>

                                <p style="margin:16px 0; font-size:14px; color:#111827;">
                                    {{ __('If you already have an account, you may accept this invitation by clicking the button below:') }}
                                </p>
                            @else
                                <p style="margin:0 0 16px; font-size:14px; color:#111827;">
                                    {{ __('You may accept this invitation by clicking the button below:') }}
                                </p>
                            @endif

                            <p style="margin:16px 0; text-align:center;">
                                <a href="{{ $acceptUrl }}" style="display:inline-block; padding:10px 20px; background-color:#0f172a; color:#ffffff; text-decoration:none; border-radius:6px; font-size:14px; font-weight:600;">{{ __('Accept Invitation') }}</a>
                            </p>

                            <p style="margin:16px 0 0; font-size:13px; color:#64748b;">
                                {{ __('If you did not expect to receive an invitation to this team, you may discard this email.') }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px; border-top:1px solid #eef2f7; font-size:12px; color:#94a3b8;">
                            This is an automated notification from {{ config('app.name') }}.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>