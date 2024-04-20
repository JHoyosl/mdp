<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReconciliationExternalValues extends Model
{
    use SoftDeletes;

    protected $table = "conciliar_values";
    protected $dates = ['deleted_at'];
    protected $pivotTable = null;

    public function __construct($tableName, $pivotTable = null)
    {

        $this->table = $tableName;
        $this->pivotTable = $pivotTable;
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'item_id', 'matched', 'matched_id', 'tx_type_id', 'tx_type_name',
        'descripcion', 'local_account', 'cuenta_externa', 'operador', 'valor_credito',
        'valor_debito', 'valor_debito_credito', 'fecha_movimiento',
        'fecha_archivo', 'codigo_tx', 'referencia_1', 'referencia_2',
        'referencia_3', 'nombre_titular', 'identificacion_titular',
        'numero_cuenta', 'nombre_transaccion', 'consecutivo_registro',
        'nombre_oficina', 'codigo_oficina', 'canal', 'nombre_proveedor',
        'id_proveedor', 'banco_destino', 'fecha_rechazo', 'motivo_rechazo',
        'ciudad', 'tipo_cuenta', 'numero_documento'

    ];

    function localValues()
    {
        return $this->belongsToMany(ReconciliationExternalValues::class, $this->pivotTable, 'external_value', 'local_value');
    }
}
