<?php

namespace App\Services\TxType;

use App\Models\LocalTxType;
use App\Models\ExternalTxType;
use Illuminate\Support\Facades\Schema;
use App\Services\DataBase\TableServices;

class TxTypeService
{

  public function indexExternalTx()
  {
    return ExternalTxType::with('banks')->get();
  }

  public function indexLocalTx($companyId)
  {
    $tableName = $this->getLocalTxTableName($companyId);
    if (!Schema::hasTable($tableName)) {
      TableServices::createLocalTxTypeTable($companyId);
    }

    $txTypes = (new LocalTxType($tableName))->get();
    return $txTypes;
  }

  public function storeExternalTx($description, $tx, $reference, $type, $sign, $bankId)
  {
    $txTypeCheck = ExternalTxType::where('description', $description)
      ->where('tx', $tx)
      ->where('bank_id', $bankId)
      ->where('reference', $reference)
      ->where('type', $type)
      ->where('sign', $sign)
      ->first();

    if ($txTypeCheck) {
      throw  new \Exception('Ya existe el tipo de tx');
    }

    $newTx = ExternalTxType::create([
      'description' =>  $description,
      'tx'  => $tx,
      'reference' => $reference,
      'type' => $type,
      'sign' => $sign,
      'bank_id' => $bankId
    ]);

    return $newTx;
  }

  public function storeLocalTx($companyId, $description, $tx, $reference, $sign)
  {
    $tableName = $this->getLocalTxTableName($companyId);
    $table = new LocalTxType($tableName);
    $txTypeCheck = $table->where('description', $description)
      ->where('tx', $tx)
      ->where('company_id', $companyId)
      ->where('reference', $reference)
      ->where('sign', $sign)
      ->first();

    if ($txTypeCheck) {
      throw  new \Exception('Ya existe el tipo de tx');
    }
    $newTxTypes = new LocalTxType($tableName);
    $newTxTypes->description = $description;
    $newTxTypes->company_id = $companyId;
    $newTxTypes->tx = $tx;
    $newTxTypes->reference = $reference;
    $newTxTypes->sign = $sign;
    $newTxTypes->save();

    return  $newTxTypes;
  }


  public  function updateLocalTx($id, $companyId, $description, $tx, $reference, $type, $sign)
  {
    $tableName = $this->getLocalTxTableName($companyId);
    $txType = (new LocalTxType($tableName))->findOrFail($id);

    $txType->description = $description;
    $txType->tx = $tx;
    $txType->reference = $reference;
    $txType->sign = $sign;
    $txType->type = $type;
    $txType->save();

    return $txType;
  }

  public function updateExternalTx($id, $description, $tx, $bankId, $type, $sign, $reference)
  {
    $txType = ExternalTxType::findOrFail($id);
    $txType->description = $description;
    $txType->tx = $tx;
    $txType->bank_id = $bankId;
    $txType->type = $type;
    $txType->sign = $sign;
    $txType->reference = $reference;
    $txType->save();

    return $txType;
  }

  public function deleteLocalTx($companyId, $id)
  {
    $tableName = $this->getLocalTxTableName($companyId);
    $table = (new LocalTxType($tableName))->where('id', $id)->delete();

    return $table;
  }

  public function deleteExternalTx($id)
  {
    $deleted = ExternalTxType::where('id', $id)->delete();
    return $deleted;
  }

  //HELPERS
  private function getLocalTxTableName($companyId)
  {
    return 'local_tx_types_' . $companyId;
  }
}
