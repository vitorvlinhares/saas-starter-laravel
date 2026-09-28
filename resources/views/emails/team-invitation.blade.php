<x-mail::message>
# Join {{ $teamName }}

You have been invited to join **{{ $teamName }}** as **{{ $role }}**.

<x-mail::button :url="$acceptUrl">
Accept invitation
</x-mail::button>

This invitation expires on {{ $expiresAt }}. If you don't have an account yet, create one with this e-mail address and open the link again.

If you were not expecting this invitation, you can ignore this e-mail.

{{ config('app.name') }}
</x-mail::message>
