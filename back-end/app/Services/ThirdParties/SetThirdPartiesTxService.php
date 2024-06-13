<?php

namespace App\Services\ThirdParties;

use App\Models\ExternalTxType;
use App\Traits\TableNamming;
use Illuminate\Support\Facades\DB;

class SetThirdPartiesTxService
{

  use TableNamming;

  public function updateCompuestoTx($companyId, $accounts)
  {
    $externalValuesTableName = $this->getReconciliationExternalValuesTableName($companyId);

    $queryStr = "UPDATE
      " . $externalValuesTableName . "
    LEFT JOIN 
      external_tx_types ON 
      " . $externalValuesTableName . ".descripcion LIKE CONCAT(external_tx_types.description,'%')
    SET
      " . $externalValuesTableName . ".tx_type_id = external_tx_types.id,
      " . $externalValuesTableName . ".tx_type_name = external_tx_types.tx
    WHERE 
      " . $externalValuesTableName . ".local_account IN (" . implode(',', $accounts) . ")
      AND external_tx_types.type = '" . ExternalTxType::COMPUESTO_TYPE . "'
      AND " . $externalValuesTableName . ".tx_type_id IS NULL";

    $result = DB::select($queryStr);

    return $result;
  }

  public function getCompuestoTxQuery($companyId, $accounts)
  {
    $externalValuesTableName = $this->getReconciliationExternalValuesTableName($companyId);

    $queryStr = "SELECT 
      " . $externalValuesTableName . ".id,
      " . $externalValuesTableName . ".tx_type_id,
      " . $externalValuesTableName . ".tx_type_name,
      external_tx_types.id,
      " . $externalValuesTableName . ".descripcion as txDescription,
      " . $externalValuesTableName . ".descripcion as valueDescription,
      external_tx_types.tx
    FROM 
      " . $externalValuesTableName . "
    LEFT JOIN 
      external_tx_types ON 
      " . $externalValuesTableName . ".descripcion LIKE CONCAT(external_tx_types.description,'%')
    WHERE 
      " . $externalValuesTableName . ".local_account IN (" . implode(',', $accounts) . ")
      AND external_tx_types.type = '" . ExternalTxType::COMPUESTO_TYPE . "'
      AND " . $externalValuesTableName . ".tx_type_id IS NULL";

    $result = DB::select($queryStr);

    return $result;
  }

  /**
   * Update values with corresponding txtypes
   */
  public function updateSimpleTx($companyId, $accounts)
  {
    $externalValuesTableName = $this->getReconciliationExternalValuesTableName($companyId);

    $queryStr = "UPDATE
      " . $externalValuesTableName . "
    LEFT JOIN 
      external_tx_types ON 
      " . $externalValuesTableName . ".descripcion = external_tx_types.description
    SET
      " . $externalValuesTableName . ".tx_type_id = external_tx_types.id,
      " . $externalValuesTableName . ".tx_type_name = external_tx_types.tx
    WHERE 
      " . $externalValuesTableName . ".local_account 
        IN (" . implode(',', $accounts) . ")
      AND external_tx_types.type = '" . ExternalTxType::SIMPLE_TYPE . "'
      AND " . $externalValuesTableName . ".tx_type_id IS NULL";

    $result = DB::select($queryStr);

    return $result;
  }


  public function getSimpleTxQuery($companyId, $accounts)
  {
    $externalValuesTableName = $this->getReconciliationExternalValuesTableName($companyId);

    $queryStr = "SELECT 
      " . $externalValuesTableName . ".id,
      " . $externalValuesTableName . ".tx_type_id,
      " . $externalValuesTableName . ".tx_type_name,
      external_tx_types.id,
      " . $externalValuesTableName . ".descripcion as txDescription,
      " . $externalValuesTableName . ".descripcion as valueDescription,
      external_tx_types.tx
    FROM 
      " . $externalValuesTableName . "
    LEFT JOIN 
      external_tx_types ON 
      " . $externalValuesTableName . ".descripcion = external_tx_types.description
    WHERE 
      " . $externalValuesTableName . ".local_account IN (" . implode(',', $accounts) . ")
      AND external_tx_types.type = '" . ExternalTxType::SIMPLE_TYPE . "'
      AND " . $externalValuesTableName . ".tx_type_id IS NULL";

    $result = DB::select($queryStr);
    return $result;
  }

  public function updateReferenceByRFGuionQuery($companyId, $accounts)
  {
    $externalValuesTableName = $this->getReconciliationExternalValuesTableName($companyId);

    $queryStr = "UPDATE 
        reconciliation_external_values_2
      INNER JOIN
        external_tx_types ON 
          reconciliation_external_values_2.tx_type_id = external_tx_types.id
      SET 
        reconciliation_external_values_2.referencia_1 =  SUBSTRING_INDEX(reconciliation_external_values_2.descripcion,' ',- 1),
        reconciliation_external_values_2.updated_at = now()
      WHERE
      external_tx_types.sign = 'RF'
      AND " . $externalValuesTableName . ".local_account IN (" . implode(',', $accounts) . ")
      AND reconciliation_external_values_2.referencia_1 IS NULL";

    $result = DB::select($queryStr);
    return $result;
  }

  public function getReferenceByRFSpaceQuery($companyId, $accounts)
  {
    $externalValuesTableName = $this->getReconciliationExternalValuesTableName($companyId);

    $queryStr = "SELECT 
      reconciliation_external_values_2.descripcion,
      reconciliation_external_values_2.referencia_1,
      SUBSTRING_INDEX(reconciliation_external_values_2.descripcion,' ',-1) AS matched
    FROM 
      external_tx_types 
    INNER JOIN 
      reconciliation_external_values_2 ON 
        external_tx_types.id = reconciliation_external_values_2.tx_type_id
    WHERE 
      external_tx_types.sign = 'RF' 
      AND " . $externalValuesTableName . ".local_account IN (" . implode(',', $accounts) . ")
      AND CONCAT('',SUBSTRING_INDEX(reconciliation_external_values_2.descripcion,' ',-1)*1) > 0
      AND reconciliation_external_values_2.referencia_1 IS NULL";

    $result = DB::select($queryStr);
    return $result;
  }
}
