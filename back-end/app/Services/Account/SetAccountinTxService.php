<?php

namespace App\Services\Account;

use App\Models\LocalTxType;
use App\Traits\TableNamming;
use Illuminate\Support\Facades\DB;

class SetAccountinTxService
{

  use TableNamming;

  public function setAllTx($companyId, $localAccountsArray)
  {
    // order mandatory
    $this->updateTrasladosRecTxQuery($companyId, $localAccountsArray);
    $this->updateSimpleTrTx($companyId, $localAccountsArray);
    $this->updateSimpleTx($companyId, $localAccountsArray);
    $this->updateTxByReference($companyId, $localAccountsArray);
    $this->updateCompuestoTx($companyId, $localAccountsArray);
  }

  public function updateTrasladosRecTxQuery($companyId, $localAccountsArray)
  {
    $locaTxTypeTableName = $this->getLocalTxTypeTableName($companyId);
    $locaValuesTableName = $this->getReconciliationLocalValuesTableName($companyId);

    $localTx = (new LocalTxType())->setTable($locaTxTypeTableName)
      ->where('reference', 'TRASLADO NOMBRE CEDULA')->first();

    $queryStr = "UPDATE " . $locaValuesTableName . "
      SET 
        " . $locaValuesTableName . ".tx_type_id = " . $localTx->id . ",
        " . $locaValuesTableName . ".tx_type_name = '" . $localTx->tx . "'
      WHERE
        tipo_registro = 'TRASLADOS'
        AND " . $locaValuesTableName . ".tx_type_id IS NULL
        AND " . $locaValuesTableName . ".nombre_tercero NOT LIKE '%COMPENSAR%'
        AND " . $locaValuesTableName . ".descripcion REGEXP '\d*[[:space:]]-[[:space:]]\W*'
        AND " . $locaValuesTableName . ".local_account  IN (" . implode(',', $localAccountsArray) . ")";

    $result = DB::select($queryStr);
    return $result;
  }

  /**
   * Return case one result
   */
  public function updateTxByReference($companyId, $localAccountsArray)
  {
    $locaTxTypeTableName = $this->getLocalTxTypeTableName($companyId);
    $locaValuesTableName = $this->getReconciliationLocalValuesTableName($companyId);

    $queryStr = "UPDATE
        mdp." . $locaValuesTableName . "
      LEFT JOIN 
        mdp." . $locaTxTypeTableName . " ON 
        mdp." . $locaValuesTableName . ".tipo_registro = mdp." . $locaTxTypeTableName . ".reference
      SET
        mdp." . $locaValuesTableName . ".tx_type_id = mdp." . $locaTxTypeTableName . ".id,
        mdp." . $locaValuesTableName . ".tx_type_name = mdp." . $locaTxTypeTableName . ".tx
      WHERE 
        mdp." . $locaValuesTableName . ".local_account  IN (" . implode(',', $localAccountsArray) . ") AND
        mdp." . $locaTxTypeTableName . ".type = '" . LocalTxType::SIMPLE_TYPE . "' AND 
        mdp." . $locaValuesTableName . ".tx_type_id IS NULL";

    $result = DB::select($queryStr);

    return $result;
  }

  /**
   * Update values with corresponding txtypes
   */
  public function updateCompuestoTx($companyId, $localAccountsArray)
  {
    $locaTxTypeTableName = $this->getLocalTxTypeTableName($companyId);
    $locaValuesTableName = $this->getReconciliationLocalValuesTableName($companyId);

    $queryStr = "UPDATE
          mdp." . $locaValuesTableName . "
      LEFT JOIN 
        mdp." . $locaTxTypeTableName . " ON 
        " . $locaValuesTableName . ".descripcion LIKE CONCAT(" . $locaTxTypeTableName . ".description,'%')
      SET 
        mdp." . $locaValuesTableName . ".tx_type_id = mdp." . $locaTxTypeTableName . ".id,
        mdp." . $locaValuesTableName . ".tx_type_name = mdp." . $locaTxTypeTableName . ".tx
      WHERE 
        mdp." . $locaValuesTableName . ".local_account  IN (" . implode(',', $localAccountsArray) . ") AND
        mdp." . $locaTxTypeTableName . ".type = '" . LocalTxType::COMPUESTO_TYPE . "' AND 
        mdp." . $locaValuesTableName . ".tx_type_id IS NULL";

    $result = DB::select($queryStr);

    return $result;
  }

  /**
   * Update values with corresponding txtypes
   */
  public function updateSimpleTx($companyId, $localAccountsArray)
  {
    $locaTxTypeTableName = $this->getLocalTxTypeTableName($companyId);
    $locaValuesTableName = $this->getReconciliationLocalValuesTableName($companyId);

    $queryStr = "UPDATE
        mdp." . $locaValuesTableName . "
      LEFT JOIN 
        mdp." . $locaTxTypeTableName . " ON 
        mdp." . $locaValuesTableName . ".descripcion = mdp." . $locaTxTypeTableName . ".description
      SET 
        mdp." . $locaValuesTableName . ".tx_type_id = mdp." . $locaTxTypeTableName . ".id,
        mdp." . $locaValuesTableName . ".tx_type_name = mdp." . $locaTxTypeTableName . ".tx
      WHERE
        mdp." . $locaValuesTableName . ".local_account  IN (" . implode(',', $localAccountsArray) . ") AND
        mdp." . $locaValuesTableName . ".tx_type_id IS NULL";

    $result = DB::select($queryStr);

    return $result;
  }

  /**
   * Update values with corresponding txtypes
   */
  public function updateSimpleTrTx($companyId, $localAccountsArray)
  {
    $locaTxTypeTableName = $this->getLocalTxTypeTableName($companyId);
    $locaValuesTableName = $this->getReconciliationLocalValuesTableName($companyId);

    $queryStr = "UPDATE
        mdp." . $locaValuesTableName . "
      LEFT JOIN 
        mdp." . $locaTxTypeTableName . " ON 
        mdp." . $locaTxTypeTableName . ".description = mdp." . $locaValuesTableName . ".tipo_registro
      SET 
      " . $locaValuesTableName . ".tx_type_id = " . $locaTxTypeTableName . ".id,
      " . $locaValuesTableName . ".tx_type_name = " . $locaTxTypeTableName . ".tx
      WHERE 
        mdp." . $locaValuesTableName . ".local_account  IN (" . implode(',', $localAccountsArray) . ") AND
        mdp." . $locaTxTypeTableName . ".type = '" . LocalTxType::SIMPLE_TYPE . "' AND 
        mdp." . $locaValuesTableName . ".tx_type_id IS NULL";

    $result = DB::select($queryStr);

    return $result;
  }
  /**
   * Return COMPUESTO result
   */
  public function getCompuestoTxQuery($companyId, $localAccountsArray)
  {
    $locaTxTypeTableName = $this->getLocalTxTypeTableName($companyId);
    $locaValuesTableName = $this->getReconciliationLocalValuesTableName($companyId);

    $queryStr = "SELECT 
        mdp." . $locaTxTypeTableName . ".id,
        mdp." . $locaValuesTableName . ".id,
        mdp." . $locaTxTypeTableName . ".description as txDescription,
        mdp." . $locaValuesTableName . ".descripcion as valueDescription,
        mdp." . $locaTxTypeTableName . ".tx
      FROM
          mdp." . $locaValuesTableName . "
      LEFT JOIN 
        mdp." . $locaTxTypeTableName . " ON 
        " . $locaValuesTableName . ".descripcion LIKE CONCAT(" . $locaTxTypeTableName . ".description,'%')
      WHERE 
        mdp." . $locaValuesTableName . ".local_account  IN (" . implode(',', $localAccountsArray) . ") AND
        mdp." . $locaTxTypeTableName . ".type = '" . LocalTxType::COMPUESTO_TYPE . "' AND 
        mdp." . $locaValuesTableName . ".tx_type_id IS NULL
      ORDER BY mdp." . $locaValuesTableName . ".id";

    $result = DB::select($queryStr);

    return $result;
  }

  /**
   * Return REFERENCE result
   */
  public function getTxQueryByReference($companyId, $localAccountsArray)
  {
    $locaTxTypeTableName = $this->getLocalTxTypeTableName($companyId);
    $locaValuesTableName = $this->getReconciliationLocalValuesTableName($companyId);

    $queryStr = "SELECT 
        mdp." . $locaTxTypeTableName . ".id,
        mdp." . $locaValuesTableName . ".id,
        mdp." . $locaTxTypeTableName . ".reference,
        mdp." . $locaValuesTableName . ".tipo_registro,
        mdp." . $locaTxTypeTableName . ".tx
      FROM
          mdp." . $locaValuesTableName . "
      LEFT JOIN 
        mdp." . $locaTxTypeTableName . " ON 
          mdp." . $locaValuesTableName . ".tipo_registro = mdp." . $locaTxTypeTableName . ".reference
      WHERE 
        mdp." . $locaValuesTableName . ".local_account  IN (" . implode(',', $localAccountsArray) . ") AND
        mdp." . $locaValuesTableName . ".tx_type_id IS NULL
      ORDER BY mdp." . $locaValuesTableName . ".id";

    $result = DB::select($queryStr);

    return $result;
  }

  /**
   * Return SIMPLE result
   */
  public function getSimpleTxQuery($companyId, $localAccountsArray)
  {
    $locaTxTypeTableName = $this->getLocalTxTypeTableName($companyId);
    $locaValuesTableName = $this->getReconciliationLocalValuesTableName($companyId);

    $queryStr = "SELECT 
        mdp." . $locaTxTypeTableName . ".id,
        mdp." . $locaValuesTableName . ".id,
        mdp." . $locaTxTypeTableName . ".description as txDescription,
        mdp." . $locaValuesTableName . ".descripcion as valueDescription,
        mdp." . $locaTxTypeTableName . ".tx
      FROM
          mdp." . $locaValuesTableName . "
      LEFT JOIN 
        mdp." . $locaTxTypeTableName . " ON 
          mdp." . $locaValuesTableName . ".descripcion = mdp." . $locaTxTypeTableName . ".description
      WHERE 
        mdp." . $locaValuesTableName . ".local_account  IN (" . implode(',', $localAccountsArray) . ") AND
        mdp." . $locaTxTypeTableName . ".type = '" . LocalTxType::SIMPLE_TYPE . "' AND 
        mdp." . $locaValuesTableName . ".tx_type_id IS NULL
      ORDER BY mdp." . $locaValuesTableName . ".id";

    $result = DB::select($queryStr);

    return $result;
  }

  /**
   * Return TR result
   */
  public function getSimpleTrTxQuery($companyId, $localAccountsArray)
  {
    $locaTxTypeTableName = $this->getLocalTxTypeTableName($companyId);
    $locaValuesTableName = $this->getReconciliationLocalValuesTableName($companyId);

    $queryStr = "SELECT 
        mdp." . $locaTxTypeTableName . ".id,
        mdp." . $locaValuesTableName . ".id,
        mdp." . $locaTxTypeTableName . ".description as txDescription,
        mdp." . $locaValuesTableName . ".descripcion as valueDescription,
        mdp." . $locaTxTypeTableName . ".tx
      FROM
          mdp." . $locaValuesTableName . "
      LEFT JOIN 
        mdp." . $locaTxTypeTableName . " ON 
          mdp." . $locaTxTypeTableName . ".description = mdp." . $locaValuesTableName . ".tipo_registro
      WHERE 
        mdp." . $locaValuesTableName . ".local_account  IN (" . implode(',', $localAccountsArray) . ") AND
        mdp." . $locaTxTypeTableName . ".type = '" . LocalTxType::SIMPLE_TYPE . "' AND 
        mdp." . $locaValuesTableName . ".tx_type_id IS NULL
      ORDER BY mdp." . $locaValuesTableName . ".id";

    $result = DB::select($queryStr);

    return $result;
  }

  /**
   * Return NUMERO CEDULA TRASLADO
   */
  public function getTrasladosRecTxQuery($companyId, $localAccountsArray)
  {
    $locaTxTypeTableName = $this->getLocalTxTypeTableName($companyId);
    $locaValuesTableName = $this->getReconciliationLocalValuesTableName($companyId);

    $queryStr = "SELECT 
        *
      FROM
          " . $locaValuesTableName . "
      WHERE
      tipo_registro = 'TRASLADOS'
      AND " . $locaValuesTableName . ".tx_type_id IS NULL
      AND " . $locaValuesTableName . ".nombre_tercero NOT LIKE '%COMPENSAR%'
      AND " . $locaValuesTableName . ".descripcion REGEXP '\d*[[:space:]]-[[:space:]]\W*'
      AND " . $locaValuesTableName . ".local_account  IN (" . implode(',', $localAccountsArray) . ")";

    $result = DB::select($queryStr);

    return $result;
  }
}
