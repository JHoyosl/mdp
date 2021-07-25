@component('mail::message')
# Recuperar Contraseña

Se ha solicitado cambio de contraseña, en caso que no lo haya solicitado, ignore este correo.
<br>

Usuario: {{$user->email}}

@component('mail::button', ['url' => config('app.url').'#/verify/'.$user->verification_token ])
Recuperar
@endcomponent

Gracias,<br>
{{ config('app.name') }}
<br>
@endcomponent
