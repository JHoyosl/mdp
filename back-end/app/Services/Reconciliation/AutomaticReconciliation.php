<?php

namespace App\Services\Reconciliation;

use Exception;
use Illuminate\Support\Arr;
use App\Traits\TableNamming;
use Illuminate\Support\Facades\DB;

class AutomaticReconciliation
{
  use TableNamming;


  public function case6($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE6' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso6 para este procesos', '400');
    }

    $queryStr = "SELECT * FROM(
      SELECT GROUP_CONCAT(localId) as Ids, externalId, SUM(vdLocal) as vdLocal, vcExternal as vcExternal  FROM (SELECT 
        `localValues`.`id` AS `localId`,
          `reconciliation_external_values_2`.`id` AS `externalId`,
          `localValues`.`fecha_movimiento` AS `lFecha_mov`,
          `reconciliation_external_values_2`.`fecha_movimiento` AS `eFecha_mov`,
          `localValues`.`local_account` AS `lAccount`,
          `reconciliation_external_values_2`.`local_account` AS `eAccount`,
          `localValues`.`valor_debito` AS `vdLocal`,
          `reconciliation_external_values_2`.`valor_credito` AS `vcExternal`,
          `localValues`.`valor_credito` AS `vcLocal`,
          `reconciliation_external_values_2`.`valor_debito` AS `vdExternal`,
          `reconciliation_external_values_2`.`referencia_1` AS `eReferencia_1`,
          `localValues`.`referencia_1` AS `lReferencia_1`,
          `reconciliation_external_values_2`.`referencia_2` AS `eReferencia_2`,
          `localValues`.`referencia_2` AS `lReferencia_2`,
          `localValues`.`referencia_3` AS `eReferencia_3`,
          `localValues`.`referencia_3` AS `lReferencia_3`
      FROM (Select * from `reconciliation_local_values_2`
      LEFT JOIN 
      `reconciliation_pivot_2` ON 
      `reconciliation_local_values_2`.id = `reconciliation_pivot_2`.local_value
      WHERE 
      `reconciliation_pivot_2`.local_value IS NULL) AS localValues
      LEFT JOIN
          `reconciliation_external_values_2` ON 
          localValues.fecha_movimiento <= DATE_ADD(reconciliation_external_values_2.fecha_movimiento, INTERVAL 4 DAY) AND
          `localValues`.`local_account` = `reconciliation_external_values_2`.`local_account`
               AND ( `localValues`.`referencia_1` = `reconciliation_external_values_2`.`referencia_1` 
               OR `localValues`.`referencia_2` = `reconciliation_external_values_2`.`referencia_1` 
               OR `localValues`.`referencia_3` = `reconciliation_external_values_2`.`referencia_1` )
              
      WHERE
          `localValues`.`local_account`  IN ('11100501','11100502','11100506','11100507')
          AND `localValues`.`valor_debito` > 0
          AND `reconciliation_external_values_2`.`local_account` IS NOT NULL
        AND `localValues`.`fecha_movimiento` <= '2023-09-30') AS firstRun
        GROUP BY externalId) as T2
        WHERE vdLocal = vcExternal";

    return $queryStr;
    $result = DB::select($queryStr);
  }

  public function case5C($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE5C' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso5C para este procesos', '400');
    }

    $queryStr = "SELECT COUNT(*) C, localId as localId, GROUP_CONCAT(externalId) as externalId, 'CASE5B', '" . $process . "' FROM
      (SELECT
      localValues.id AS localId,
      " . $externalValuesTable . ".id AS externalId,
      localValues.fecha_movimiento AS lFecha_mov,
      " . $externalValuesTable . ".fecha_movimiento AS eFecha_mov,
      localValues.local_account AS lAccount,
      " . $externalValuesTable . ".local_account AS eAccount,
      localValues.valor_debito AS vdLocal,
      " . $externalValuesTable . ".valor_credito AS vcExternal,
      localValues.valor_credito AS vcLocal,
      " . $externalValuesTable . ".valor_debito AS vdExternal,
      " . $externalValuesTable . ".referencia_1 AS eReferencia_1,
      localValues.referencia_1 AS lReferencia_1,
      " . $externalValuesTable . ".referencia_2 AS eReferencia_2,
      localValues.referencia_2 AS lReferencia_2,
      localValues.referencia_3 AS eReferencia_3,
      localValues.referencia_3 AS lReferencia_3
      FROM
      (SELECT * FROM
      " . $localValuesTable . "
      LEFT JOIN " . $pivotTable . " ON " . $localValuesTable . ".id = " . $pivotTable . ".local_value
      WHERE
      " . $pivotTable . ".local_value IS NULL) AS localValues
      LEFT JOIN " . $externalValuesTable . " ON
      localValues.fecha_movimiento = " . $externalValuesTable . ".fecha_movimiento
      AND localValues.local_account = " . $externalValuesTable . ".local_account
      AND localValues.valor_credito = " . $externalValuesTable . ".valor_debito
      AND " . $externalValuesTable . ".referencia_2 IS NOT NULL
      WHERE
      localValues.local_account IN (" . implode(',', $accounts->toArray()) . ")
      AND localValues.valor_credito > 0
      AND " . $externalValuesTable . ".local_account IS NOT NULL
      AND localValues.fecha_movimiento BETWEEN '" . $startDate . "' AND '" . $endDate . "'
      ORDER BY localId, externalId) AS CASE1PAGOS
      GROUP BY localId
      HAVING C > 1";

    $result = DB::select($queryStr);

    $data = $this->reduceExternalToOneLocalId($result, 'CASE5C', $process);

    return DB::table($pivotTable)->insert($data);
  }

  public function case5B($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE5B' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso5B para este procesos', '400');
    }

    $queryStr = "INSERT INTO " . $pivotTable . " (`external_value`,`local_value`,`case`, process)
    SELECT externalId, localId , 'CASE5B', '" . $process . "' FROM(
    SELECT COUNT(*) C, localId as localId, GROUP_CONCAT(externalId) as externalId, 'CASE5B', '" . $process . "' FROM
      (SELECT
      localValues.id AS localId,
      " . $externalValuesTable . ".id AS externalId,
      localValues.fecha_movimiento AS lFecha_mov,
      " . $externalValuesTable . ".fecha_movimiento AS eFecha_mov,
      localValues.local_account AS lAccount,
      " . $externalValuesTable . ".local_account AS eAccount,
      localValues.valor_debito AS vdLocal,
      " . $externalValuesTable . ".valor_credito AS vcExternal,
      localValues.valor_credito AS vcLocal,
      " . $externalValuesTable . ".valor_debito AS vdExternal,
      " . $externalValuesTable . ".referencia_1 AS eReferencia_1,
      localValues.referencia_1 AS lReferencia_1,
      " . $externalValuesTable . ".referencia_2 AS eReferencia_2,
      localValues.referencia_2 AS lReferencia_2,
      localValues.referencia_3 AS eReferencia_3,
      localValues.referencia_3 AS lReferencia_3
      FROM
      (SELECT * FROM
      " . $localValuesTable . "
      LEFT JOIN " . $pivotTable . " ON " . $localValuesTable . ".id = " . $pivotTable . ".local_value
      WHERE
      " . $pivotTable . ".local_value IS NULL) AS localValues
      LEFT JOIN " . $externalValuesTable . " ON
      localValues.fecha_movimiento = " . $externalValuesTable . ".fecha_movimiento
      AND localValues.local_account = " . $externalValuesTable . ".local_account
      AND localValues.valor_credito = " . $externalValuesTable . ".valor_debito
      AND " . $externalValuesTable . ".referencia_2 IS NOT NULL
      WHERE
      localValues.local_account IN (" . implode(',', $accounts->toArray()) . ")
      AND localValues.valor_credito > 0
      AND " . $externalValuesTable . ".local_account IS NOT NULL
      AND localValues.fecha_movimiento BETWEEN '" . $startDate . "' AND '" . $endDate . "'
      ORDER BY localId, externalId) AS CASE1PAGOS
      GROUP BY localId
      HAVING C = 1) AS result";

    $result = DB::select($queryStr);

    return $result;
  }

  public function case5($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE5' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso5 para este procesos', '400');
    }

    $queryStr = "INSERT INTO " . $pivotTable . " (`external_value`,`local_value`,`case`, process)
      SELECT externalId, localId , 'CASE5', '" . $process . "' FROM
      (SELECT 
        localValues.id AS localId,
            " . $externalValuesTable . ".id AS externalId,
            localValues.fecha_movimiento AS lFecha_mov,
            " . $externalValuesTable . ".fecha_movimiento AS eFecha_mov,
            localValues.local_account AS lAccount,
            " . $externalValuesTable . ".local_account AS eAccount,
            localValues.valor_debito AS vdLocal,
            " . $externalValuesTable . ".valor_credito AS vcExternal,
            localValues.valor_credito AS vcLocal,
            " . $externalValuesTable . ".valor_debito AS vdExternal,
            " . $externalValuesTable . ".referencia_1 AS eReferencia_1,
            localValues.referencia_1 AS lReferencia_1,
            " . $externalValuesTable . ".referencia_2 AS eReferencia_2,
            localValues.referencia_2 AS lReferencia_2,
            localValues.referencia_3 AS eReferencia_3,
            localValues.referencia_3 AS lReferencia_3
      FROM
        (SELECT * FROM
        " . $localValuesTable . "
      LEFT JOIN " . $pivotTable . " ON " . $localValuesTable . ".id = " . $pivotTable . ".local_value
      WHERE
        " . $pivotTable . ".local_value IS NULL) AS localValues
      LEFT JOIN " . $externalValuesTable . " ON 
        localValues.fecha_movimiento = " . $externalValuesTable . ".fecha_movimiento
        AND localValues.local_account = " . $externalValuesTable . ".local_account
        AND localValues.valor_credito = " . $externalValuesTable . ".valor_debito
        AND (localValues.referencia_1 = " . $externalValuesTable . ".referencia_2
        OR localValues.referencia_2 = " . $externalValuesTable . ".referencia_2
        OR localValues.referencia_3 = " . $externalValuesTable . ".referencia_2)
      WHERE
        localValues.local_account IN (" . implode(',', $accounts->toArray()) . ")
            AND localValues.valor_credito > 0
            AND " . $externalValuesTable . ".local_account IS NOT NULL
            AND localValues.fecha_movimiento BETWEEN '" . $startDate . "' AND '" . $endDate . "') AS CASE1PAGOS";

    $result = DB::select($queryStr);

    return $result;
  }

  public function case4($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE4' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un CASE4 para este procesos', '400');
    }

    $queryStr = "SELECT 
        GROUP_CONCAT(localValues.id) AS localId,
        " . $externalValuesTable . ".id AS externalId,
        SUM(localValues.valor_debito) AS vdLocal,
        " . $externalValuesTable . ".valor_credito AS vcExternal
      FROM
        (SELECT 
          " . $localValuesTable . ".id,
          " . $localValuesTable . ".valor_debito,
          " . $localValuesTable . ".local_account,
          " . $localValuesTable . ".referencia_1,
          " . $localValuesTable . ".referencia_2,
          " . $localValuesTable . ".referencia_3,
          " . $localValuesTable . ".fecha_movimiento
        FROM
            " . $localValuesTable . "
        LEFT JOIN " . $pivotTable . " ON " . $localValuesTable . ".id = " . $pivotTable . ".local_value
        WHERE
          " . $localValuesTable . ".valor_debito > 0
          AND " . $localValuesTable . ".fecha_movimiento <= '" . $endDate . "'
          AND " . $localValuesTable . ".local_account IN (" . implode(',', $accounts->toArray()) . ")
          AND " . $pivotTable . ".local_value IS NULL) AS localValues
        LEFT JOIN
          " . $externalValuesTable . " ON 
          localValues.fecha_movimiento <= DATE_ADD(" . $externalValuesTable . ".fecha_movimiento,INTERVAL 4 DAY)
          AND localValues.local_account = " . $externalValuesTable . ".local_account
          AND (localValues.referencia_1 = " . $externalValuesTable . ".referencia_1
          OR localValues.referencia_2 = " . $externalValuesTable . ".referencia_1
          OR localValues.referencia_3 = " . $externalValuesTable . ".referencia_1)
      WHERE
          " . $externalValuesTable . ".local_account IS NOT NULL
      GROUP BY externalId
      HAVING vdLocal = vcExternal";

    $result = DB::select($queryStr);

    if (count($result) == 0) {
      $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE4' AND process = '" . $process . "'");
      if (count($pivot) > 0) {
        throw new Exception('Ya existe un Caso 4 para este procesos', '400');
      }
      return false;
    }

    $data = $this->oneExternalToManyLocalInsert($result, 'CASE4', $process);

    DB::table($pivotTable)->insert($data);

    return true;
  }

  public function caseNomina($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'NOMINA' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un NOMINA para este procesos', '400');
    }

    $queryStr = "INSERT INTO " . $pivotTable . " (`external_value`,`local_value`,`case`, process)
      SELECT 
      " . $externalValuesTable . ".id AS externalId,
      localValues.id AS localId,
      'NOMINA',
      '" . $process . "'
      FROM
        (SELECT 
          " . $localValuesTable . ".id,
          " . $localValuesTable . ".fecha_movimiento,
          " . $localValuesTable . ".local_account,
          " . $localValuesTable . ".tx_type_name,
          " . $localValuesTable . ".valor_debito
      FROM
        " . $localValuesTable . "
      LEFT JOIN " . $pivotTable . " ON " . $localValuesTable . ".id = " . $pivotTable . ".local_value
      WHERE
        " . $localValuesTable . ".valor_debito > 0
        AND " . $localValuesTable . ".fecha_movimiento <= '" . $endDate . "'
        AND " . $localValuesTable . ".local_account IN (" . implode(',', $accounts->toArray()) . ")
        AND " . $localValuesTable . ".tx_type_name IN ('NOMINA')
        AND " . $pivotTable . ".local_value IS NULL) AS localValues
      LEFT JOIN
        " . $externalValuesTable . " ON localValues.fecha_movimiento <= DATE_ADD(" . $externalValuesTable . ".fecha_movimiento,
          INTERVAL 4 DAY)
          AND localValues.local_account = " . $externalValuesTable . ".local_account
          AND localValues.tx_type_name = " . $externalValuesTable . ".tx_type_name
      GROUP BY localValues.id , externalId
      ORDER BY localId , externalId";

    return DB::select($queryStr);
  }

  public function case3($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE3' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso3 para este procesos', '400');
    }

    $queryStr = "SELECT * FROM(
      SELECT localId, GROUP_CONCAT(externalId) as externalId, vdLocal, SUM(vcExternal) as vcExternal  FROM (SELECT 
        localValues.id AS localId,
        " . $externalValuesTable . ".id AS externalId,
        localValues.valor_debito AS vdLocal,
        " . $externalValuesTable . ".valor_credito AS vcExternal
      FROM (Select * from " . $localValuesTable . "
      LEFT JOIN 
        " . $pivotTable . " ON 
        " . $localValuesTable . ".id = " . $pivotTable . ".local_value
      WHERE 
        " . $pivotTable . ".local_value IS NULL) AS localValues
      LEFT JOIN
        " . $externalValuesTable . " ON 
        localValues.fecha_movimiento = " . $externalValuesTable . ".fecha_movimiento AND
        localValues.local_account = " . $externalValuesTable . ".local_account
        AND ( localValues.referencia_1 = " . $externalValuesTable . ".referencia_1 
          OR localValues.referencia_2 = " . $externalValuesTable . ".referencia_1 
          OR localValues.referencia_3 = " . $externalValuesTable . ".referencia_1 )
      WHERE
        localValues.local_account  IN (" . implode(',', $accounts->toArray()) . ")
        AND localValues.valor_debito > 0
        AND " . $externalValuesTable . ".local_account IS NOT NULL
        AND localValues.fecha_movimiento BETWEEN '" . $startDate . "' AND '" . $endDate . "') AS firstRun
      GROUP BY localId, vdLocal) AS cond2
      WHERE vdLocal = vcExternal";

    $result = DB::select($queryStr);

    $data = $this->oneLocalToManyExternalInsert($result, 'CASE3', $process);
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

    $data = $this->reduceExternalToOneLocalId($result, 'CASE2D', $process);

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

    $data = $this->reduceExternalToOneLocalId($result, 'CASE2B', $process);

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

    $data = $this->reduceExternalToOneLocalId($result, 'CASE1C', $process);

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
  public function reduceExternalToOneLocalId($queryResult, $case, $process)
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

  public function oneExternalToManyLocalInsert($queryResult, $case, $process)
  {
    $assoc = [];

    foreach ($queryResult as $value) {
      $localIds = explode(',', $value->localId);
      foreach ($localIds as $id) {
        $assoc[] = [
          'local_value' => $id,
          'external_value' => $value->externalId,
          'case' => $case,
          'process' => $process
        ];
        $acc[] = $id;
      }
    }
    return $assoc;
  }
  public function oneLocalToManyExternalInsert($queryResult, $case, $process)
  {
    $assoc = [];

    foreach ($queryResult as $value) {
      $externalsId = explode(',', $value->externalId);
      foreach ($externalsId as $id) {
        $assoc[] = [
          'local_value' => $value->localId,
          'external_value' => $id,
          'case' => $case,
          'process' => $process
        ];
        $acc[] = $id;
      }
    }
    return $assoc;
  }

  public function reduceExternalToOneLocalIdCase3($queryResult, $case, $process)
  {
    $acc = [];
    $assoc = [];
    foreach ($queryResult as $value) {
      $externalsId = explode(',', $value->externalId);
      foreach ($externalsId as $id) {
        $assoc[] = [
          'local_value' => $value->localId,
          'external_value' => $id,
          'case' => $case,
          'process' => $process
        ];
        $acc[] = $id;
      }
    }
    return $assoc;
  }
}
