<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ThirdPartiesItems extends Model
{
    use SoftDeletes;

    protected $table = "third_parties_items_";
    protected $dates = ['deleted_at'];

    public function __construct()
    {
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'header_id', 'matched', 'item_id', 'tx_type_id', 'tx_type_name',
        'fecha_movimiento', 'descripcion', 'local_account',
        'cuenta_externa', 'referencia_1', 'referencia_2',
        'referencia_3', 'otra_referencia', 'saldo_actual',
        'valor_debito', 'saldo_anterior', 'valor_credito',
        'codigo_usuario', 'nombre_agencia', 'nombre_centro_costos',
        'codigo_centro_costo', 'numero_comprobante', 'nombre_usuario',
        'nombre_cuenta_contable', 'numero_cuenta_contable', 'nombre_tercero',
        'identificacion_tercero', 'fecha_ingreso', 'fecha_origen',
        'oficina_origen', 'oficina_destino', 'numero_lote',
        'consecutivo_lote', 'tipo_registro', 'ambiente_origen',
        'beneficiario',

    ];
}
