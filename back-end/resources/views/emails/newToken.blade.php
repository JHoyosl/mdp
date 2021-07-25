@component('mail::message')
# Nueva Verificación

Se ha creado una nueva llave de validación.
Usuario: {{$user->email}}


@component('mail::button', ['url' => config('app.url').'#/verify/'.$user->verification_token ])
Verificar
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
