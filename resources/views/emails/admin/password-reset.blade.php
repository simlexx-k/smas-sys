@component('mail::message')
# Password Reset

Your administrator password has been reset by the system administrator.

Your new password is: **{{ $password }}**

Please login with this password and change it immediately.

@component('mail::button', ['url' => route('login')])
Login Now
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent