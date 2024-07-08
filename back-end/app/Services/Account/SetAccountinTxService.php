<?php

namespace App\Services\Account;

use App\Models\LocalTxType;
use App\Traits\TableNamming;
use Illuminate\Support\Facades\DB;

class SetAccountinTxService
{

  use TableNamming;

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
   * Return RegType result
   */
  public function getTxQueryByRegType($companyId)
  {
    $locaTxTypeTableName = $this->getLocalTxTypeTableName($companyId);
    $locaValuesTableName = $this->getReconciliationLocalValuesTableName($companyId);

    $queryStr =  "SELECT * FROM mdp.reconciliation_local_values_2
    WHERE 
    tipo_registro IN ('ABONOS POR NOMINA','TRASLADOS') AND 
    descripcion REGEXP '^([0-9])+[[:space:]]-[[:space:]](\W[[:space:]])*'";
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
    return $queryStr;
    $result = DB::select($queryStr);

    return $result;
  }
}
