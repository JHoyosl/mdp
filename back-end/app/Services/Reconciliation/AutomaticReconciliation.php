<?php

namespace App\Services\Reconciliation;

use Exception;
use App\Traits\TableNamming;
use Illuminate\Support\Facades\DB;

class AutomaticReconciliation
{
  use TableNamming;

  public function case2e($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE2E' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso2E para este procesos', '400');
    }

    $queryStr = "SELECT localId, externalId
       FROM
          (SELECT 
            localValues.id AS localId,
            GROUP_CONCAT(" . $externalValuesTable . ".id) AS externalId,
            COUNT(*) C
          FROM (SELECT * FROM
              " . $localValuesTable . "
          LEFT JOIN " . $pivotTable . " ON " . $localValuesTable . ".id = " . $pivotTable . ".local_value
          WHERE
            " . $pivotTable . ".local_value IS NULL) AS localValues
          LEFT JOIN " . $externalValuesTable . " ON 
            localValues.fecha_movimiento <= DATE_ADD(" . $externalValuesTable . ".fecha_movimiento, INTERVAL 4 DAY)
            AND localValues.local_account = " . $externalValuesTable . ".local_account
            AND localValues.valor_debito = " . $externalValuesTable . ".valor_credito
            AND localValues.tx_type_name = " . $externalValuesTable . ".tx_type_name
          WHERE
            localValues.local_account IN (" . implode(',', $accounts->toArray()) . ")
            AND localValues.valor_debito > 0
            AND " . $externalValuesTable . ".local_account IS NOT NULL
            AND localValues.fecha_movimiento BETWEEN '" . $startDate . "' AND '" . $endDate . "'
          GROUP BY localId
          HAVING C > 1) AS CASE2E";

    $result = DB::select($queryStr);

    $data = $this->groupConcatToInsertData($result, 'CASE2E', $process);

    return DB::table($pivotTable)->insert($data);
  }

  public function case2d($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE2D' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso2D para este procesos', '400');
    }

    $queryStr = "SELECT localId, externalId
       FROM
          (SELECT 
            localValues.id AS localId,
            GROUP_CONCAT(" . $externalValuesTable . ".id) AS externalId,
            COUNT(*) C
          FROM (SELECT * FROM
              " . $localValuesTable . "
          LEFT JOIN " . $pivotTable . " ON " . $localValuesTable . ".id = " . $pivotTable . ".local_value
          WHERE
            " . $pivotTable . ".local_value IS NULL) AS localValues
          LEFT JOIN " . $externalValuesTable . " ON 
            localValues.fecha_movimiento <= DATE_ADD(" . $externalValuesTable . ".fecha_movimiento, INTERVAL 4 DAY)
            AND localValues.local_account = " . $externalValuesTable . ".local_account
            AND localValues.valor_debito = " . $externalValuesTable . ".valor_credito
            AND localValues.tx_type_name = " . $externalValuesTable . ".tx_type_name
          WHERE
            localValues.local_account IN (" . implode(',', $accounts->toArray()) . ")
            AND localValues.valor_debito > 0
            AND " . $externalValuesTable . ".local_account IS NOT NULL
            AND localValues.fecha_movimiento BETWEEN '" . $startDate . "' AND '" . $endDate . "'
          GROUP BY localId
          HAVING C > 1) AS CASE2D";

    $result = DB::select($queryStr);

    $data = $this->groupConcatToInsertData($result, 'CASE2D', $process);

    return DB::table($pivotTable)->insert($data);
  }

  public function case2c($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE2C' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso2C para este procesos', '400');
    }

    $queryStr = "SELECT localId, externalId
       FROM
          (SELECT 
            localValues.id AS localId,
            GROUP_CONCAT(" . $externalValuesTable . ".id) AS externalId,
            COUNT(*) C
          FROM (SELECT * FROM
              " . $localValuesTable . "
          LEFT JOIN " . $pivotTable . " ON " . $localValuesTable . ".id = " . $pivotTable . ".local_value
          WHERE
            " . $pivotTable . ".local_value IS NULL) AS localValues
          LEFT JOIN " . $externalValuesTable . " ON 
            localValues.fecha_movimiento <= DATE_ADD(" . $externalValuesTable . ".fecha_movimiento, INTERVAL 4 DAY)
            AND localValues.local_account = " . $externalValuesTable . ".local_account
            AND localValues.valor_debito = " . $externalValuesTable . ".valor_credito
            AND localValues.tx_type_name = " . $externalValuesTable . ".tx_type_name
          WHERE
            localValues.local_account IN (" . implode(',', $accounts->toArray()) . ")
            AND localValues.valor_debito > 0
            AND " . $externalValuesTable . ".local_account IS NOT NULL
            AND localValues.fecha_movimiento BETWEEN '" . $startDate . "' AND '" . $endDate . "'
          GROUP BY localId
          HAVING C = 1) AS CASE2C";

    $result = DB::select($queryStr);

    $data = [];
    foreach ($result as $value) {
      $data[] = [
        'local_value' => $value->localId,
        'external_value' => $value->externalId,
        'case' => 'CASE2C',
        'process' => $process
      ];
    }

    return DB::table($pivotTable)->insert($data);
  }

  public function case2b($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE2B' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso2B para este procesos', '400');
    }

    $queryStr = "SELECT localId, externalId, 'CASE2B', '" . $process . "'
       FROM
          (SELECT 
            localValues.id AS localId,
            GROUP_CONCAT(" . $externalValuesTable . ".id) AS externalId,
            COUNT(*) C
          FROM (SELECT * FROM
              " . $localValuesTable . "
          LEFT JOIN " . $pivotTable . " ON " . $localValuesTable . ".id = " . $pivotTable . ".local_value
          WHERE
            " . $pivotTable . ".local_value IS NULL) AS localValues
          LEFT JOIN " . $externalValuesTable . " ON localValues.fecha_movimiento = " . $externalValuesTable . ".fecha_movimiento
            AND localValues.local_account = " . $externalValuesTable . ".local_account
            AND localValues.valor_debito = " . $externalValuesTable . ".valor_credito
            AND localValues.tx_type_name = " . $externalValuesTable . ".tx_type_name
          WHERE
            localValues.local_account IN (" . implode(',', $accounts->toArray()) . ")
            AND localValues.valor_debito > 0
            AND " . $externalValuesTable . ".local_account IS NOT NULL
            AND localValues.fecha_movimiento BETWEEN '" . $startDate . "' AND '" . $endDate . "'
          GROUP BY localId
          HAVING C > 1) AS CASE2B";

    $result = DB::select($queryStr);

    $data = $this->groupConcatToInsertData($result, 'CASE2B', $process);

    return DB::table($pivotTable)->insert($data);
  }

  public function case2($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE2' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso2 para este procesos', '400');
    }

    $queryStr = "INSERT INTO " . $pivotTable . " (local_value, external_value,`case`, process)
        SELECT localId, externalId, 'CASE2', '" . $process . "'
       FROM
          (SELECT 
            localValues.id AS localId,
            GROUP_CONCAT(" . $externalValuesTable . ".id) AS externalId,
            COUNT(*) C
          FROM (SELECT * FROM
              " . $localValuesTable . "
          LEFT JOIN " . $pivotTable . " ON " . $localValuesTable . ".id = " . $pivotTable . ".local_value
          WHERE
            " . $pivotTable . ".local_value IS NULL) AS localValues
          LEFT JOIN " . $externalValuesTable . " ON localValues.fecha_movimiento = " . $externalValuesTable . ".    fecha_movimiento
            AND localValues.local_account = " . $externalValuesTable . ".local_account
            AND localValues.valor_debito = " . $externalValuesTable . ".valor_credito
            AND localValues.tx_type_name = " . $externalValuesTable . ".tx_type_name
          WHERE
            localValues.local_account IN (" . implode(',', $accounts->toArray()) . ")
            AND localValues.valor_debito > 0
            AND " . $externalValuesTable . ".local_account IS NOT NULL
            AND localValues.fecha_movimiento BETWEEN '" . $startDate . "' AND '" . $endDate . "'
          GROUP BY localId
          HAVING C = 1) AS CASE2";

    return DB::select($queryStr);
  }

  public function case1c($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE1C' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso1C para este procesos', '400');
    }

    $queryStr = "SELECT localId, externalId, 'CASE1B', '" . $process . "'
            FROM
            (SELECT 
                localValues.id AS localId,
                GROUP_CONCAT(" . $externalValuesTable . ".id) AS externalId,
                COUNT(*) C
            FROM
                (SELECT *
            FROM
                " . $localValuesTable . "
            LEFT JOIN " . $pivotTable . " ON " . $localValuesTable . ".id = " . $pivotTable . ".local_value
            WHERE
                " . $pivotTable . ".local_value IS NULL) AS localValues
            LEFT JOIN 
                " . $externalValuesTable . " ON 
                localValues.fecha_movimiento <= DATE_ADD(" . $externalValuesTable . ".fecha_movimiento, INTERVAL 4 DAY)
                AND localValues.local_account = " . $externalValuesTable . ".local_account
                AND localValues.valor_debito = " . $externalValuesTable . ".valor_credito
                AND (localValues.referencia_1 = " . $externalValuesTable . ".referencia_1
                OR localValues.referencia_2 = " . $externalValuesTable . ".referencia_1
                OR localValues.referencia_3 = " . $externalValuesTable . ".referencia_1)
            WHERE
                localValues.local_account IN (" . implode(',', $accounts->toArray()) . ")
                    AND localValues.valor_debito > 0
                    AND " . $externalValuesTable . ".local_account IS NOT NULL
                    AND localValues.fecha_movimiento BETWEEN '" . $startDate . "' AND '" . $endDate . "' GROUP BY localId
            HAVING C > 1) AS CASE1C";

    $result = DB::select($queryStr);

    $data = $this->groupConcatToInsertData($result, 'CASE1C', $process);

    return DB::table($pivotTable)->insert($data);
  }

  public function case1b($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE1B' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso1B para este procesos', '400');
    }

    $queryStr = "INSERT INTO " . $pivotTable . " (local_value, external_value,`case`, process)
            SELECT 
                localId, externalId, 'CASE1B', '" . $process . "'
            FROM
            (SELECT 
                localValues.id AS localId,
                GROUP_CONCAT(" . $externalValuesTable . ".id) AS externalId,
                COUNT(*) C
            FROM
                (SELECT *
            FROM
                " . $localValuesTable . "
            LEFT JOIN " . $pivotTable . " ON " . $localValuesTable . ".id = " . $pivotTable . ".local_value
            WHERE
                " . $pivotTable . ".local_value IS NULL) AS localValues
            LEFT JOIN 
                " . $externalValuesTable . " ON 
                localValues.fecha_movimiento <= DATE_ADD(" . $externalValuesTable . ".fecha_movimiento, INTERVAL 4 DAY)
                AND localValues.local_account = " . $externalValuesTable . ".local_account
                AND localValues.valor_debito = " . $externalValuesTable . ".valor_credito
                AND (localValues.referencia_1 = " . $externalValuesTable . ".referencia_1
                OR localValues.referencia_2 = " . $externalValuesTable . ".referencia_1
                OR localValues.referencia_3 = " . $externalValuesTable . ".referencia_1)
            WHERE
                localValues.local_account IN (" . implode(',', $accounts->toArray()) . ")
                    AND localValues.valor_debito > 0
                    AND " . $externalValuesTable . ".local_account IS NOT NULL
                    AND localValues.fecha_movimiento BETWEEN '" . $startDate . "' AND '" . $endDate . "' GROUP BY localId
            HAVING C = 1) AS CASE1B";

    return DB::select($queryStr);
  }

  public function case1($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE1' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso 1 para este procesos', '400');
    }

    $queryStr = "INSERT INTO " . $pivotTable . " (`external_value`,`local_value`,`case`, process)
            SELECT externalId, localId, 'CASE1', '" . $process . "' FROM (SELECT 
                `localValues`.`id` AS `localId`,
                " . $externalValuesTable . ".`id` AS `externalId`
            FROM (Select * from " . $localValuesTable . "
            LEFT JOIN 
            " . $pivotTable . " ON 
            " . $localValuesTable . ".id = " . $pivotTable . ".local_value
            WHERE 
            " . $pivotTable . ".local_value IS NULL) AS localValues
            LEFT JOIN
                " . $externalValuesTable . " ON 
                    `localValues`.`fecha_movimiento` = " . $externalValuesTable . ".`fecha_movimiento` AND
                    `localValues`.`local_account` = " . $externalValuesTable . ".`local_account`
                    AND `localValues`.`valor_debito` = " . $externalValuesTable . ".`valor_credito`
                    AND ( `localValues`.`referencia_1` = " . $externalValuesTable . ".`referencia_1` 
                    OR `localValues`.`referencia_2` = " . $externalValuesTable . ".`referencia_1` 
                    OR `localValues`.`referencia_3` = " . $externalValuesTable . ".`referencia_1` )
                    
            WHERE
                `localValues`.`local_account` IN (" . implode(',', $accounts->toArray()) . ")
                AND `localValues`.`valor_debito` > 0
                AND " . $externalValuesTable . ".`local_account` IS NOT NULL
                AND `localValues`.`fecha_movimiento` BETWEEN '" . $startDate . "' AND '" . $endDate . "') AS CASE1";

    return DB::select($queryStr);
  }

  //HELPERS
  public function groupConcatToInsertData($queryResult, $case, $process)
  {
    $acc = [];
    $assoc = [];
    foreach ($queryResult as $value) {
      $externalsId = explode(',', $value->externalId);
      foreach ($externalsId as $id) {
        if (!in_array($id, $acc)) {
          $assoc[] = [
            'local_value' => $value->localId,
            'external_value' => $id,
            'case' => $case,
            'process' => $process
          ];
          $acc[] = $id;
          break;
        }
      }
    }

    return $assoc;
  }
}
