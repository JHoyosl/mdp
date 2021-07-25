@component('mail::message')
# Nueva Empresa asociada

Se ha Asociado a la empresa {{$user->names}}, cuando ingrese podrá seleccionar esta empresa.



@component('mail::button', ['url' => config('app.url') ])
Ingresar
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
