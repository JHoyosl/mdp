@component('mail::message')
# Bienvenido {{$user->names}} {{$user->last_names}}

Se ha creado una nueva cuenta, favor validar con:

Usuario: {{$user->email}}

@component('mail::button', ['url' => config('app.url').'#/verify/'.$user->verification_token ])
Verificar
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
