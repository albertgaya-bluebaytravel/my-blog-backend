@component('mail::message')

Hi {{ $user->name }},

Please click below link to verify account!

@component('mail::button', ['url' => $user->verifyAccountUrl ])
Verify Account
@endcomponent


Thanks,<br>
{{ config('app.name') }}

@endcomponent