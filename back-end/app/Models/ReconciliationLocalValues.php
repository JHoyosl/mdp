<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReconciliationLocalValues extends Model
{

    protected $table = "conciliar_values";
    protected $dates = ['deleted_at'];

    public function __construct($tableName)
    {

        $this->table = $tableName;
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'item_id', 'matched', 'matched_id', 'tx_type_id', 'tx_type_name',
        'descripcion', 'local_account', 'cuenta_externa', 'referencia_1',
        'referencia_2', 'referencia_3', 'otra_referencia', 'saldo_actual',
        'valor_debito', 'saldo_anterior', 'valor_credito', 'codigo_usuario',
        'nombre_agencia', 'valor_debito_credito', 'nombre_centro_costos',
        'codigo_centro_costo', 'numero_comprobante', 'nombre_usuario',
        'nombre_cuenta_contable', 'numero_cuenta_contable', 'nombre_tercero',
        'identificacion_tercero', 'fecha_ingreso', 'fecha_origen', 'oficina_origen',
        'oficina_destino', 'numero_lote', 'consecutivo_lote', 'tipo_registro',
        'ambiente_origen', 'beneficiario'
    ];
}
