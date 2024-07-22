<?php

namespace App\Services\Reconciliation;

use Exception;
use Illuminate\Support\Arr;
use App\Traits\TableNamming;
use Illuminate\Support\Facades\DB;

class AutomaticReconciliation
{
  use TableNamming;

  public function case20($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $queryStr = "SELECT
      localId,
      GROUP_CONCAT(reconciliation_external_values_2.id) AS externalId
      FROM
      (SELECT
      GROUP_CONCAT(reconciliation_local_values_2.id) AS localId,
      reconciliation_local_values_2.referencia_1,
      reconciliation_local_values_2.fecha_movimiento,
      reconciliation_local_values_2.local_account,
      SUM(valor_debito) AS valor_debito
      FROM
      reconciliation_local_values_2
      LEFT JOIN reconciliation_pivot_2 ON reconciliation_local_values_2.id = reconciliation_pivot_2.local_value
      WHERE
      reconciliation_pivot_2.local_value IS NULL
      AND tx_type_name IN ('REC')
      AND valor_debito > 0
      GROUP BY reconciliation_local_values_2.referencia_1 , reconciliation_local_values_2.fecha_movimiento,
      reconciliation_local_values_2.local_account) AS localValues
      LEFT JOIN
      reconciliation_external_values_2 ON localValues.fecha_movimiento = reconciliation_external_values_2.fecha_movimiento
      AND localValues.local_account = reconciliation_external_values_2.local_account
      AND localValues.valor_debito = reconciliation_external_values_2.valor_credito
      WHERE reconciliation_external_values_2.id IS NOT NULL
      GROUP BY localId";

    return $queryStr;
    $result = DB::select($queryStr);
    $data = $this->reduceMulitpleExternalToMultipleInternal($result, 'CASE7', $process);

    return DB::table($pivotTable)->insert($data);
  }



  public function case0c($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE5D' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso5D para este procesos', '400');
    }

    $queryStr = "SELECT
        reconciliation_local_pending.id AS localId,
        GROUP_CONCAT(reconciliation_external_pending.id) AS externalId,
        'CASE5C',
        '" . $process . "'
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
        reconciliation_local_pending.fecha_movimiento BETWEEN reconciliation_external_pending.fecha_movimiento
        AND DATE_ADD(reconciliation_external_pending.fecha_movimiento, INTERVAL 4 DAY)
        AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
        AND reconciliation_local_pending.valor_credito = reconciliation_external_pending.valor_debito
      WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
        AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
        AND reconciliation_local_pending.valor_credito > 0
      GROUP BY reconciliation_local_pending.id
      HAVING COUNT(*) > 1
      ORDER BY reconciliation_local_pending.id";

    $result = DB::select($queryStr);

    $data = $this->reduceExternalToOneLocalId($result, 'CASE5D', $process);

    return DB::table($pivotTable)->insert($data);
  }

  public function case0b($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE5C' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso5C para este procesos', '400');
    }

    $queryStr = "SELECT
        reconciliation_local_pending.id AS localId,
        GROUP_CONCAT(reconciliation_external_pending.id) AS externalId,
        'CASE5C',
        '" . $process . "'
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
        reconciliation_local_pending.fecha_movimiento BETWEEN reconciliation_external_pending.fecha_movimiento 
          AND DATE_ADD(reconciliation_external_pending.fecha_movimiento, INTERVAL 4 DAY)
        AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
        AND reconciliation_local_pending.valor_credito = reconciliation_external_pending.valor_debito
      WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
        AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
        AND reconciliation_local_pending.valor_credito > 0
      GROUP BY reconciliation_local_pending.id
      HAVING COUNT(*) > 1
      ORDER BY reconciliation_local_pending.id";

    $result = DB::select($queryStr);

    $data = $this->reduceExternalToOneLocalId($result, 'CASE5C', $process);

    return DB::table($pivotTable)->insert($data);
  }

  public function case0($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE5B' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso5B para este procesos', '400');
    }

    $queryStr = "INSERT INTO " . $pivotTable . " (local_value, external_value,`case`, process)
      SELECT
        reconciliation_local_pending.id AS localId,
        GROUP_CONCAT(reconciliation_external_pending.id) AS externalId,
        'CASE5B',
        '" . $process . "'
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
        reconciliation_local_pending.fecha_movimiento = reconciliation_external_pending.fecha_movimiento
        AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
        AND reconciliation_local_pending.valor_credito = reconciliation_external_pending.valor_debito
      WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
        AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
        AND reconciliation_local_pending.valor_credito > 0
      GROUP BY reconciliation_local_pending.id
      HAVING COUNT(*) = 1
      ORDER BY reconciliation_local_pending.id";

    $result = DB::select($queryStr);

    return $result;
  }

  public function case13b($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE13B' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso13B para este procesos', '400');
    }

    $queryStr = "SELECT
        reconciliation_local_pending.id AS localId,
        GROUP_CONCAT(reconciliation_external_pending.id) AS externalId
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
        reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
        AND reconciliation_local_pending.valor_credito = reconciliation_external_pending.valor_debito
        AND reconciliation_local_pending.tx_type_name = 'COM'
      WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
        AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
        AND reconciliation_local_pending.valor_credito > 0
        GROUP BY localId
        HAVING COUNT(*) > 1";
    $result = DB::select($queryStr);

    $data = $this->reduceExternalToLocal($result, ReconciliationService::TYPE_LE, 'CASE13B', $process);

    return DB::table($pivotTable)->insert($data);
  }

  public function case13($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE13' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso13 para este procesos', '400');
    }

    $queryStr = "INSERT INTO " . $pivotTable . " (source_id, target_id,`type`,`case`, process)
      SELECT
        reconciliation_local_pending.id AS localId,
        GROUP_CONCAT(reconciliation_external_pending.id) AS externalId,
        '" . ReconciliationService::TYPE_LE . "',
        'CASE13',
        '" . $process . "'
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
        reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
        AND reconciliation_local_pending.valor_credito = reconciliation_external_pending.valor_debito
        AND reconciliation_local_pending.tx_type_name = 'COM'
      WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
        AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
        AND reconciliation_local_pending.valor_credito > 0
        GROUP BY localId
        HAVING COUNT(*) = 1";

    return DB::select($queryStr);
  }

  // EXTERNO CONTRA EXTERNO
  public function case11d($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE11D' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso11D para este procesos', '400');
    }

    $queryStr = " SELECT 
    t1.id AS localId,
    GROUP_CONCAT(t2.id) AS externalId
    FROM reconciliation_external_pending  t1
    JOIN reconciliation_external_pending  t2 ON
    t1.fecha_movimiento BETWEEN DATE_SUB(t2.fecha_movimiento, INTERVAL 4 DAY)
      AND DATE_ADD(t2.fecha_movimiento, INTERVAL 4 DAY)
    AND t1.valor_credito = t2.valor_debito
    AND t2.tx_type_name = 'RECHADEB'
    AND t1.tx_type_name = 'REC'
    GROUP BY localId
    HAVING COUNT(*) > 1";


    $result = DB::select($queryStr);
    $data = $this->reduceExternalToLocal($result, ReconciliationService::TYPE_EE, 'CASE11D', $process);

    return DB::table($pivotTable)->insert($data);
  }
  // EXTERNO CONTRA EXTERNO
  public function case11c($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE11C' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso11C para este procesos', '400');
    }

    $queryStr = "INSERT INTO " . $pivotTable . " (source_id, target_id,`type`,`case`, process)
        SELECT 
          t1.id AS localId,
          GROUP_CONCAT(t2.id) AS externalId,
          '" . ReconciliationService::TYPE_EE . "',
          'CASE11C',
          '" . $process . "'
          FROM reconciliation_external_pending  t1
          JOIN reconciliation_external_pending  t2 ON
          t1.fecha_movimiento BETWEEN DATE_SUB(t2.fecha_movimiento, INTERVAL 4 DAY)
            AND DATE_ADD(t2.fecha_movimiento, INTERVAL 4 DAY)
          AND t1.valor_credito = t2.valor_debito
          AND t2.tx_type_name = 'RECHADEB'
          AND t1.tx_type_name = 'REC'
          GROUP BY localId
          HAVING COUNT(*) = 1";

    return DB::select($queryStr);
  }
  // EXTERNO CONTRA EXTERNO
  public function case11b($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE11B' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso11B para este procesos', '400');
    }

    $queryStr = "SELECT
      t1.id AS localId,
      GROUP_CONCAT(t2.id) AS externalId
      FROM reconciliation_external_pending t1
      JOIN reconciliation_external_pending t2 ON
      t1.valor_credito = t2.valor_debito
      AND t1.fecha_movimiento = t2.fecha_movimiento
      AND t2.tx_type_name = 'RECHADEB'
      AND t1.tx_type_name = 'REC'
      GROUP BY localId
      HAVING COUNT(*) > 1";


    $result = DB::select($queryStr);
    $data = $this->reduceExternalToLocal($result, ReconciliationService::TYPE_EE, 'CASE11B', $process);

    return DB::table($pivotTable)->insert($data);
  }
  // EXTERNO CONTRA EXTERNO
  public function case11($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE11' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso11 para este procesos', '400');
    }

    $queryStr = "INSERT INTO " . $pivotTable . " (source_id, target_id,`type`,`case`, process)
    SELECT 
          t1.id AS creditId,
          GROUP_CONCAT(t2.id) AS debitId,
          '" . ReconciliationService::TYPE_EE . "',
          'CASE11',
          '" . $process . "'
          FROM reconciliation_external_pending  t1
          JOIN reconciliation_external_pending  t2 ON
          t1.valor_credito = t2.valor_debito
          AND t1.fecha_movimiento = t2.fecha_movimiento
          AND t2.tx_type_name = 'RECHADEB'
          AND t1.tx_type_name = 'REC'
          GROUP BY creditId
          HAVING COUNT(*) = 1";

    return DB::select($queryStr);
  }

  public function case10b($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE10b' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso10b para este procesos', '400');
    }

    $queryStr = "
    SELECT 
          t1.id AS localId,
          GROUP_CONCAT(t2.id) AS externalId,
          'CASE10',
          '" . $process . "'
          FROM reconciliation_external_pending  t1
          JOIN reconciliation_external_pending  t2 ON
          t1.valor_credito = t2.valor_debito
          AND t1.fecha_movimiento = t2.fecha_movimiento
          AND t2.tx_type_name = 'RECHADEB'
          AND t1.tx_type_name = 'REC'
          GROUP BY localId
          HAVING COUNT(*) > 1";

    // 1 A * 
    // $result = DB::select($queryStr);
    // $data = $this->reduceExternalToOneLocalId($result, 'CASE7B', $process);

    // return DB::table($pivotTable)->insert($data);
  }
  public function case10($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE10' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso10 para este procesos', '400');
    }

    $queryStr = "INSERT INTO " . $pivotTable . " (local_value, external_value,`case`, process)
    SELECT 
          t1.id AS localId,
          GROUP_CONCAT(t2.id) AS externalId,
          'CASE10',
          '" . $process . "'
          FROM reconciliation_external_pending  t1
          JOIN reconciliation_external_pending  t2 ON
          t1.valor_credito = t2.valor_debito
          AND t1.fecha_movimiento = t2.fecha_movimiento
          AND t2.tx_type_name = 'RECHADEB'
          AND t1.tx_type_name = 'REC'
          GROUP BY localId
          HAVING COUNT(*) = 1";

    // 1 A 1 
    // $result = DB::select($queryStr);
    // $data = $this->reduceExternalToOneLocalId($result, 'CASE7B', $process);

    // return DB::table($pivotTable)->insert($data);
  }

  public function case12b($accounts, $companyId, $startDate, $endDate, $process)
  {
    // TODO REVISAR E INSERTAR
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $queryStr = "SELECT
        GROUP_CONCAT(reconciliation_local_pending.id) AS localId,
        reconciliation_external_pending.id AS externalId,
        SUM(reconciliation_local_pending.valor_debito) as vdLocal,
        reconciliation_external_pending.valor_credito as vcExternal
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
        reconciliation_local_pending.fecha_movimiento = reconciliation_external_pending.fecha_movimiento
        AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
        AND (
          reconciliation_local_pending.referencia_1 = reconciliation_external_pending.referencia_1
          OR reconciliation_local_pending.referencia_2 = reconciliation_external_pending.referencia_1
          OR reconciliation_local_pending.referencia_3 = reconciliation_external_pending.referencia_1)
      WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
        AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
        AND reconciliation_local_pending.valor_debito > 0
        GROUP BY reconciliation_external_pending.id
        HAVING vdLocal = vcExternal";

    $result = DB::select($queryStr);

    $data = $this->oneExternalToManyLocal($result, ReconciliationService::TYPE_LE, 'CASE12B', $process);

    DB::table($pivotTable)->insert($data);
  }

  public function case12($accounts, $companyId, $startDate, $endDate, $process)
  {
    // TODO REVISAR E INSERTAR
    // SUMATORIA DEB LOCAL, SUM CRED EXTERNO, GRUOP BY REFERENCIA 
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $queryStr = "SELECT
        GROUP_CONCAT(reconciliation_local_pending.id) AS localId,
        reconciliation_external_pending.id AS externalId,
        SUM(reconciliation_local_pending.valor_debito) as vdLocal,
        reconciliation_external_pending.valor_credito as vcExternal
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
        reconciliation_local_pending.fecha_movimiento = reconciliation_external_pending.fecha_movimiento
        AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
        AND (
          reconciliation_local_pending.referencia_1 = reconciliation_external_pending.referencia_1
          OR reconciliation_local_pending.referencia_2 = reconciliation_external_pending.referencia_1
          OR reconciliation_local_pending.referencia_3 = reconciliation_external_pending.referencia_1)
      WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
        AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
        AND reconciliation_local_pending.valor_debito > 0
        GROUP BY reconciliation_external_pending.id
        HAVING vdLocal = vcExternal";

    $result = DB::select($queryStr);

    $data = $this->oneExternalToManyLocal($result, ReconciliationService::TYPE_LE, 'CASE12', $process);

    DB::table($pivotTable)->insert($data);
  }

  public function case9d($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $queryStr = "SELECT 
        reconciliation_local_pending.id AS localId,
        GROUP_CONCAT(reconciliation_external_pending.id) AS externalId,
        reconciliation_local_pending.valor_debito AS vdLocal,
        reconciliation_external_pending.valor_credito AS vcExternal
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
        reconciliation_external_pending.fecha_movimiento BETWEEN reconciliation_local_pending.fecha_movimiento
          AND DATE_ADD(reconciliation_local_pending.fecha_movimiento, INTERVAL 4 DAY)
        AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
        AND reconciliation_local_pending.valor_debito = reconciliation_external_pending.valor_credito
        AND reconciliation_local_pending.tx_type_name = reconciliation_external_pending.tx_type_name
      WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
        AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
        AND reconciliation_local_pending.valor_debito > 0
      GROUP BY reconciliation_local_pending.id
      HAVING count(*) > 1";

    $result = DB::select($queryStr);

    $data = $this->oneLocalToManyExternal($result, ReconciliationService::TYPE_LE, 'CASE9D', $process);

    return DB::table($pivotTable)->insert($data);
  }

  public function case9c($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $queryStr = "INSERT INTO " . $pivotTable . " (source_id, target_id,`type`,`case`, process)
      SELECT 
        reconciliation_local_pending.id AS localId,
        GROUP_CONCAT(reconciliation_external_pending.id) AS externalId,
        '" . ReconciliationService::TYPE_LE . "',
        'CASE9C',
        '" . $process . "'
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
        reconciliation_external_pending.fecha_movimiento BETWEEN reconciliation_local_pending.fecha_movimiento
          AND DATE_ADD(reconciliation_local_pending.fecha_movimiento, INTERVAL 4 DAY)
        AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
        AND reconciliation_local_pending.valor_debito = reconciliation_external_pending.valor_credito
        AND reconciliation_local_pending.tx_type_name = reconciliation_external_pending.tx_type_name
      WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
        AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
        AND reconciliation_local_pending.valor_debito > 0
      GROUP BY reconciliation_local_pending.id
      HAVING count(*) = 1";

    return DB::select($queryStr);
  }

  public function case9b($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $queryStr = "SELECT 
        reconciliation_local_pending.id AS localId,
        GROUP_CONCAT(reconciliation_external_pending.id) AS externalId,
        reconciliation_local_pending.valor_debito AS vdLocal,
        reconciliation_external_pending.valor_credito AS vcExternal
        FROM reconciliation_local_pending
        JOIN reconciliation_external_pending ON
          reconciliation_local_pending.fecha_movimiento = reconciliation_external_pending.fecha_movimiento
        AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
        AND reconciliation_local_pending.valor_debito = reconciliation_external_pending.valor_credito
        AND reconciliation_local_pending.tx_type_name = reconciliation_external_pending.tx_type_name
      WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
        AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
        AND reconciliation_local_pending.valor_debito > 0
      GROUP BY reconciliation_local_pending.id
      HAVING count(*) > 1";

    $result = DB::select($queryStr);

    $data = $this->reduceExternalToLocal($result, ReconciliationService::TYPE_LE, 'CASE9B', $process);

    return DB::table($pivotTable)->insert($data);
  }

  public function case9($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $queryStr = "INSERT INTO " . $pivotTable . " (source_id, target_id,`type`,`case`, process)
      SELECT 
        reconciliation_local_pending.id AS localId,
        GROUP_CONCAT(reconciliation_external_pending.id) AS externalId,
        '" . ReconciliationService::TYPE_LE . "',
        'CASE9',
        '" . $process . "'
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
        reconciliation_local_pending.fecha_movimiento = reconciliation_external_pending.fecha_movimiento
        AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
        AND reconciliation_local_pending.valor_debito = reconciliation_external_pending.valor_credito
        AND reconciliation_local_pending.tx_type_name = reconciliation_external_pending.tx_type_name
      WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
        AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
        AND reconciliation_local_pending.valor_debito > 0
      GROUP BY reconciliation_local_pending.id
      HAVING count(*) = 1";

    return DB::select($queryStr);
  }

  public function case8b($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $queryStr = "SELECT
        GROUP_CONCAT(reconciliation_local_pending.id) AS localId,
        reconciliation_external_pending.id AS externalId,
        SUM(reconciliation_local_pending.valor_debito) as vdLocal,
        reconciliation_external_pending.valor_credito as vcExternal
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
        reconciliation_external_pending.fecha_movimiento BETWEEN DATE_SUB(reconciliation_local_pending.fecha_movimiento, INTERVAL 4 DAY)
        AND DATE_ADD(reconciliation_local_pending.fecha_movimiento, INTERVAL 4 DAY)
        AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
        AND reconciliation_local_pending.tx_type_name = reconciliation_external_pending.tx_type_name
      WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
        AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
        AND reconciliation_local_pending.valor_debito > 0
        GROUP BY reconciliation_local_pending.referencia_1, reconciliation_external_pending.id
        HAVING vdLocal = vcExternal";

    $result = DB::select($queryStr);

    $data = $this->oneExternalToManyLocal($result, ReconciliationService::TYPE_LE, 'CASE8B', $process);

    DB::table($pivotTable)->insert($data);
  }

  public function case8($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $queryStr = "SELECT
        GROUP_CONCAT(reconciliation_local_pending.id) AS localId,
        reconciliation_external_pending.id AS externalId,
        SUM(reconciliation_local_pending.valor_debito) as vdLocal,
        reconciliation_external_pending.valor_credito as vcExternal
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
        reconciliation_local_pending.fecha_movimiento = reconciliation_external_pending.fecha_movimiento
        AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
        AND reconciliation_local_pending.tx_type_name = reconciliation_external_pending.tx_type_name
      WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
        AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
        AND reconciliation_local_pending.valor_debito > 0
        GROUP BY reconciliation_local_pending.referencia_1, reconciliation_external_pending.id
        HAVING vdLocal = vcExternal";

    $result = DB::select($queryStr);

    $data = $this->oneExternalToManyLocal($result, ReconciliationService::TYPE_LE, 'CASE8', $process);

    DB::table($pivotTable)->insert($data);
  }


  public function case7b($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE7b' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso7b para este procesos', '400');
    }

    $queryStr = "SELECT 
        reconciliation_local_pending.id AS localId,
        GROUP_CONCAT(reconciliation_external_pending.id) AS externalId
        FROM reconciliation_local_pending
        JOIN reconciliation_external_pending ON
        reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
        AND reconciliation_local_pending.valor_credito = reconciliation_external_pending.valor_debito
        AND reconciliation_local_pending.tx_type_name = reconciliation_external_pending.tx_type_name
        AND reconciliation_local_pending.tx_type_name = 'COM'
        WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
        AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
        AND reconciliation_local_pending.valor_credito > 0
        GROUP BY 
        reconciliation_local_pending.id
        HAVING COUNT(*) > 1
        ORDER BY reconciliation_local_pending.id";

    $result = DB::select($queryStr);
    $data = $this->reduceExternalToLocal($result, ReconciliationService::TYPE_LE, 'CASE7B', $process);

    return DB::table($pivotTable)->insert($data);
  }

  public function case7($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE7' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso7 para este procesos', '400');
    }

    $queryStr = "INSERT INTO " . $pivotTable . " (source_id, target_id,`type`,`case`, process)
        SELECT 
        reconciliation_local_pending.id AS localId,
        GROUP_CONCAT(reconciliation_external_pending.id) AS externalId,
        '" . ReconciliationService::TYPE_LE . "',
        'CASE7',
        '" . $process . "'
        FROM reconciliation_local_pending
        JOIN reconciliation_external_pending ON
        reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
        AND reconciliation_local_pending.valor_credito = reconciliation_external_pending.valor_debito
        AND reconciliation_local_pending.tx_type_name = reconciliation_external_pending.tx_type_name
        AND reconciliation_local_pending.tx_type_name = 'COM'
        WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
        AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
        AND reconciliation_local_pending.valor_credito > 0
        GROUP BY 
        reconciliation_local_pending.id
        HAVING COUNT(*) = 1
        ORDER BY reconciliation_local_pending.id";

    return DB::select($queryStr);
  }

  public function case6d($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE6D' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso6D para este procesos', '400');
    }

    $queryStr = "SELECT
        reconciliation_local_pending.id AS localId,
        GROUP_CONCAT(reconciliation_external_pending.id) AS externalId
        FROM reconciliation_local_pending
        JOIN reconciliation_external_pending ON
        reconciliation_local_pending.fecha_movimiento BETWEEN reconciliation_external_pending.fecha_movimiento
        AND DATE_ADD(reconciliation_external_pending.fecha_movimiento, INTERVAL 4 DAY)
        AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
        AND reconciliation_local_pending.valor_credito = reconciliation_external_pending.valor_debito
        AND reconciliation_local_pending.tx_type_name = reconciliation_external_pending.tx_type_name
        WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
        AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
        AND reconciliation_local_pending.valor_credito > 0
        GROUP BY reconciliation_local_pending.id";

    $result = DB::select($queryStr);

    $data = $this->reduceExternalToLocal($result, ReconciliationService::TYPE_LE, 'CASE6D', $process);

    return DB::table($pivotTable)->insert($data);
  }

  public function case6c($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE6C' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso6C para este procesos', '400');
    }
    // deja reciduo
    $queryStr = "INSERT INTO " . $pivotTable . " (source_id, target_id,`type`,`case`, process)
        SELECT
          reconciliation_local_pending.id AS localId,
          GROUP_CONCAT(reconciliation_external_pending.id) AS externalId,
          '" . ReconciliationService::TYPE_LE . "',
          'CASE6C',
          '" . $process . "'
        FROM reconciliation_local_pending
        JOIN reconciliation_external_pending ON
          reconciliation_local_pending.fecha_movimiento BETWEEN reconciliation_external_pending.fecha_movimiento 
            AND DATE_ADD(reconciliation_external_pending.fecha_movimiento, INTERVAL 4 DAY)
          AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
          AND reconciliation_local_pending.valor_credito = reconciliation_external_pending.valor_debito
          AND reconciliation_local_pending.tx_type_name = reconciliation_external_pending.tx_type_name
        WHERE
          reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
          AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
          AND reconciliation_local_pending.valor_credito > 0
        GROUP BY reconciliation_local_pending.id
        HAVING COUNT(*) = 1";

    return DB::select($queryStr);
  }

  public function case6b($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE6B' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso6B para este procesos', '400');
    }
    // deja reciduo
    $queryStr = "SELECT 
      reconciliation_local_pending.id AS localId,
      GROUP_CONCAT(reconciliation_external_pending.id) AS externalId
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
      reconciliation_local_pending.fecha_movimiento = reconciliation_external_pending.fecha_movimiento
      AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
      AND reconciliation_local_pending.valor_credito = reconciliation_external_pending.valor_debito
      AND reconciliation_local_pending.tx_type_name = reconciliation_external_pending.tx_type_name
      WHERE
      reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
      AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
      AND reconciliation_local_pending.valor_credito > 0
      GROUP BY 
      reconciliation_local_pending.id
      HAVING COUNT(*) > 1";

    $result = DB::select($queryStr);

    $data = $this->reduceExternalToLocal($result, ReconciliationService::TYPE_LE, 'CASE6B', $process);

    return DB::table($pivotTable)->insert($data);
  }

  public function case6($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE6' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso6 para este procesos', '400');
    }

    $queryStr = "INSERT INTO " . $pivotTable . " (source_id, target_id,`type`,`case`, process)
      SELECT 
      reconciliation_local_pending.id AS localId,
      GROUP_CONCAT(reconciliation_external_pending.id) AS externalId,
      '" . ReconciliationService::TYPE_LE . "',
      'CASE6',
      '" . $process . "'
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
      reconciliation_local_pending.fecha_movimiento = reconciliation_external_pending.fecha_movimiento
      AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
      AND reconciliation_local_pending.valor_credito = reconciliation_external_pending.valor_debito
      AND reconciliation_local_pending.tx_type_name = reconciliation_external_pending.tx_type_name
      WHERE
      reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
      AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
      AND reconciliation_local_pending.valor_credito > 0
      GROUP BY 
      reconciliation_local_pending.id
      HAVING COUNT(*) = 1";

    return DB::select($queryStr);
  }

  // proveedores
  public function case5d($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE5D' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso5D para este procesos', '400');
    }

    $queryStr = "SELECT
        GROUP_CONCAT(reconciliation_local_pending.id) AS localId,
        reconciliation_external_pending.id AS externalId,
        SUM(reconciliation_local_pending.valor_credito) AS vcLocal,
        reconciliation_external_pending.valor_debito AS vdExternal
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
      reconciliation_local_pending.fecha_movimiento BETWEEN reconciliation_external_pending.fecha_movimiento 
          AND DATE_ADD(reconciliation_external_pending.fecha_movimiento, INTERVAL 4 DAY)
      AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
      AND reconciliation_local_pending.tx_type_name = reconciliation_external_pending.tx_type_name
      WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
      AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
      AND reconciliation_local_pending.valor_credito > 0
      AND reconciliation_external_pending.valor_debito > 0
      GROUP BY reconciliation_local_pending.referencia_1, reconciliation_external_pending.id
      HAVING vcLocal = vdExternal";

    $result = DB::select($queryStr);
    $data = $this->oneExternalToManyLocal($result, ReconciliationService::TYPE_LE, 'CASE8B', $process);
    DB::table($pivotTable)->insert($data);
  }

  public function case5c($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE5C' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso5C para este procesos', '400');
    }

    $queryStr = "SELECT
        GROUP_CONCAT(reconciliation_local_pending.id) AS localId,
        reconciliation_external_pending.id AS externalId,
        SUM(reconciliation_local_pending.valor_credito) AS vcLocal,
        reconciliation_external_pending.valor_debito AS vdExternal
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
        reconciliation_local_pending.fecha_movimiento = reconciliation_external_pending.fecha_movimiento
      AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
      AND reconciliation_local_pending.tx_type_name = reconciliation_external_pending.tx_type_name
      WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
      AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
      AND reconciliation_local_pending.valor_credito > 0
      AND reconciliation_external_pending.valor_debito > 0
      GROUP BY reconciliation_local_pending.referencia_1, reconciliation_external_pending.id
      HAVING vcLocal = vdExternal";

    $result = DB::select($queryStr);
    $data = $this->oneExternalToManyLocal($result, ReconciliationService::TYPE_LE, 'CASE8B', $process);
    DB::table($pivotTable)->insert($data);
  }

  public function case5b($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE5B' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso5B para este procesos', '400');
    }

    $queryStr = "INSERT INTO " . $pivotTable . " (source_id, target_id,`type`,`case`, process)
      SELECT
        reconciliation_local_pending.id AS localId,
        reconciliation_external_pending.id AS externalId,
        '" . ReconciliationService::TYPE_LE . "',
        'CASE5B',
        '" . $process . "'
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
      reconciliation_local_pending.fecha_movimiento BETWEEN reconciliation_external_pending.fecha_movimiento 
          AND DATE_ADD(reconciliation_external_pending.fecha_movimiento, INTERVAL 4 DAY)
      AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
      AND reconciliation_local_pending.valor_credito = reconciliation_external_pending.valor_debito
      AND (reconciliation_local_pending.referencia_1 = reconciliation_external_pending.referencia_2
      OR reconciliation_local_pending.referencia_2 = reconciliation_external_pending.referencia_2
      OR reconciliation_local_pending.referencia_3 = reconciliation_external_pending.referencia_2)
      WHERE
      reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
      AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
      AND reconciliation_local_pending.valor_credito > 0";

    return DB::select($queryStr);
  }
  // proveedores
  public function case5($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE5' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso5 para este procesos', '400');
    }

    $queryStr = "INSERT INTO " . $pivotTable . " (source_id, target_id,`type`,`case`, process)
      SELECT
        reconciliation_local_pending.id AS localId,
        reconciliation_external_pending.id AS externalId,
        '" . ReconciliationService::TYPE_LE . "',
        'CASE5',
        '" . $process . "'
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
      reconciliation_local_pending.fecha_movimiento = reconciliation_external_pending.fecha_movimiento
      AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
      AND reconciliation_local_pending.valor_credito = reconciliation_external_pending.valor_debito
      AND (reconciliation_local_pending.referencia_1 = reconciliation_external_pending.referencia_2
      OR reconciliation_local_pending.referencia_2 = reconciliation_external_pending.referencia_2
      OR reconciliation_local_pending.referencia_3 = reconciliation_external_pending.referencia_2)
      WHERE
      reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
      AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
      AND reconciliation_local_pending.valor_credito > 0";

    return DB::select($queryStr);
  }

  public function case4b($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $queryStr = "c";

    $result = DB::select($queryStr);

    $data = $this->oneExternalToManyLocal($result, ReconciliationService::TYPE_LE, 'CASE4B', $process);

    DB::table($pivotTable)->insert($data);
  }

  public function case4($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $queryStr = "SELECT
        GROUP_CONCAT(reconciliation_local_pending.id) AS localId,
        reconciliation_external_pending.id AS externalId,
        SUM(reconciliation_local_pending.valor_debito) as vdLocal,
        reconciliation_external_pending.valor_credito as vcExternal
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
        reconciliation_local_pending.fecha_movimiento = reconciliation_external_pending.fecha_movimiento
        AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
        AND (
          reconciliation_local_pending.referencia_1 = reconciliation_external_pending.referencia_1
          OR reconciliation_local_pending.referencia_2 = reconciliation_external_pending.referencia_1
          OR reconciliation_local_pending.referencia_3 = reconciliation_external_pending.referencia_1)
      WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
        AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
        AND reconciliation_local_pending.valor_debito > 0
        GROUP BY reconciliation_external_pending.id
        HAVING vdLocal = vcExternal";

    $result = DB::select($queryStr);

    $data = $this->oneExternalToManyLocal($result, ReconciliationService::TYPE_LE, 'CASE4', $process);

    DB::table($pivotTable)->insert($data);
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

    $queryStr = "INSERT INTO " . $pivotTable . " (`source_id`,`target_id`,`type`,`case`, process)
        SELECT
        reconciliation_local_pending.id AS localId,
        reconciliation_external_pending.id AS externalId,
        '" . ReconciliationService::TYPE_LE . "',
        'NOMINA',
        '" . $process . "'
        FROM reconciliation_local_pending
        JOIN reconciliation_external_pending ON
          reconciliation_local_pending.fecha_movimiento BETWEEN reconciliation_external_pending.fecha_movimiento 
            AND DATE_ADD(reconciliation_external_pending.fecha_movimiento, INTERVAL 4 DAY)
          AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
          AND reconciliation_local_pending.tx_type_name = reconciliation_external_pending.tx_type_name
          AND reconciliation_local_pending.tx_type_name = 'NOMINA'
        WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
        AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
        AND reconciliation_local_pending.valor_debito > 0
        GROUP BY reconciliation_local_pending.id, reconciliation_external_pending.id";

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

    $queryStr = "SELECT
      reconciliation_local_pending.id AS localId,
      GROUP_CONCAT(reconciliation_external_pending.id) AS externalId,
      reconciliation_local_pending.valor_debito as vdLocal,
      SUM(reconciliation_external_pending.valor_credito) as vcExternal,
      reconciliation_external_pending.referencia_1
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
      reconciliation_local_pending.fecha_movimiento = reconciliation_external_pending.fecha_movimiento
      AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
      AND (
      reconciliation_local_pending.referencia_1 = reconciliation_external_pending.referencia_1
      OR reconciliation_local_pending.referencia_2 = reconciliation_external_pending.referencia_1
      OR reconciliation_local_pending.referencia_3 = reconciliation_external_pending.referencia_1)
      WHERE
      reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
      AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
      AND reconciliation_local_pending.valor_debito > 0
      GROUP BY reconciliation_local_pending.id, reconciliation_external_pending.referencia_1
      HAVING vdLocal = vcExternal";

    $result = DB::select($queryStr);

    $data = $this->oneLocalToManyExternal($result, ReconciliationService::TYPE_LE, 'CASE3', $process);

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

    $queryStr = "SELECT
        reconciliation_local_pending.id AS localId,
        GROUP_CONCAT(reconciliation_external_pending.id) AS externalId
        FROM reconciliation_local_pending
        JOIN reconciliation_external_pending ON
        reconciliation_local_pending.fecha_movimiento BETWEEN reconciliation_external_pending.fecha_movimiento
        AND DATE_ADD(reconciliation_external_pending.fecha_movimiento, INTERVAL 4 DAY)
        AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
        AND reconciliation_local_pending.valor_debito = reconciliation_external_pending.valor_credito
        AND reconciliation_local_pending.tx_type_name = reconciliation_external_pending.tx_type_name
        WHERE
        reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
        AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
        AND reconciliation_local_pending.valor_debito > 0
        GROUP BY reconciliation_local_pending.id";

    $result = DB::select($queryStr);

    $data = $this->reduceExternalToLocal($result, ReconciliationService::TYPE_LE, 'CASE2D', $process);

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

    $queryStr = "INSERT INTO " . $pivotTable . " (source_id, target_id,`type`,`case`, process)
        SELECT
          reconciliation_local_pending.id AS localId,
          GROUP_CONCAT(reconciliation_external_pending.id) AS externalId,
          '" . ReconciliationService::TYPE_LE . "',
          'CASE2C',
          '" . $process . "'
        FROM reconciliation_local_pending
        JOIN reconciliation_external_pending ON
          reconciliation_local_pending.fecha_movimiento BETWEEN reconciliation_external_pending.fecha_movimiento 
            AND DATE_ADD(reconciliation_external_pending.fecha_movimiento, INTERVAL 4 DAY)
          AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
          AND reconciliation_local_pending.valor_debito = reconciliation_external_pending.valor_credito
          AND reconciliation_local_pending.tx_type_name = reconciliation_external_pending.tx_type_name
        WHERE
          reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
          AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
          AND reconciliation_local_pending.valor_debito > 0
        GROUP BY reconciliation_local_pending.id
        HAVING COUNT(*) = 1";

    return DB::select($queryStr);
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

    $queryStr = "SELECT 
      reconciliation_local_pending.id AS localId,
      GROUP_CONCAT(reconciliation_external_pending.id) AS externalId,
      'CASE2B',
      '" . $process . "'
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
      reconciliation_local_pending.fecha_movimiento = reconciliation_external_pending.fecha_movimiento
      AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
      AND reconciliation_local_pending.valor_debito = reconciliation_external_pending.valor_credito
      AND reconciliation_local_pending.tx_type_name = reconciliation_external_pending.tx_type_name
      WHERE
      reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
      AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
      AND reconciliation_local_pending.valor_debito > 0
      GROUP BY 
      reconciliation_local_pending.id
      HAVING COUNT(*) > 1";

    $result = DB::select($queryStr);

    $data = $this->reduceExternalToLocal($result, ReconciliationService::TYPE_LE, 'CASE2B', $process);

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

    $queryStr = "INSERT INTO " . $pivotTable . " (source_id, target_id,`type`,`case`, process)
      SELECT 
      reconciliation_local_pending.id AS localId,
      GROUP_CONCAT(reconciliation_external_pending.id) AS externalId,
      '" . ReconciliationService::TYPE_LE . "',
      'CASE2',
      '" . $process . "'
      FROM reconciliation_local_pending
      JOIN reconciliation_external_pending ON
      reconciliation_local_pending.fecha_movimiento = reconciliation_external_pending.fecha_movimiento
      AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
      AND reconciliation_local_pending.valor_debito = reconciliation_external_pending.valor_credito
      AND reconciliation_local_pending.tx_type_name = reconciliation_external_pending.tx_type_name
      WHERE
      reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
      AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
      AND reconciliation_local_pending.valor_debito > 0
      GROUP BY 
      reconciliation_local_pending.id
      HAVING COUNT(*) = 1";

    return DB::select($queryStr);
  }
  // REFERENCIA, FECHA 4D,  VALOR, PRIMERO EN ORDEN
  public function case1c($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE1C' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso1C para este procesos', '400');
    }

    $queryStr = "SELECT
          reconciliation_local_pending.id AS localId,
          GROUP_CONCAT(reconciliation_external_pending.id) AS externalId
        FROM reconciliation_local_pending
        JOIN reconciliation_external_pending ON
          reconciliation_local_pending.fecha_movimiento BETWEEN reconciliation_external_pending.fecha_movimiento
          AND DATE_ADD(reconciliation_external_pending.fecha_movimiento, INTERVAL 4 DAY)
          AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
          AND reconciliation_local_pending.valor_debito = reconciliation_external_pending.valor_credito
          AND (
            reconciliation_local_pending.referencia_1 = reconciliation_external_pending.referencia_1
            OR reconciliation_local_pending.referencia_2 = reconciliation_external_pending.referencia_1
            OR reconciliation_local_pending.referencia_3 = reconciliation_external_pending.referencia_1
          )
        WHERE
          reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
          AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
          AND reconciliation_local_pending.valor_debito > 0
          group by localId
          HAVING COUNT(*) > 1";

    $result = DB::select($queryStr);

    $data = $this->reduceExternalToLocal($result, ReconciliationService::TYPE_LE, 'CASE1C', $process);

    return DB::table($pivotTable)->insert($data);
  }

  // REFERENCIA, FECHA 4D,  VALOR, 1 A 1
  public function case1b($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE1B' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso1B para este procesos', '400');
    }

    $queryStr = "INSERT INTO " . $pivotTable . " (`source_id`,`target_id`,`type`,`case`, process)
          SELECT 
          reconciliation_local_pending.id AS localId,
          GROUP_CONCAT(reconciliation_external_pending.id) AS externalId,
          '" . ReconciliationService::TYPE_LE . "',
          'CASE1',
          '" . $process . "'
        FROM reconciliation_local_pending
        JOIN reconciliation_external_pending ON
        reconciliation_local_pending.fecha_movimiento BETWEEN reconciliation_external_pending.fecha_movimiento 
          AND DATE_ADD(reconciliation_external_pending.fecha_movimiento, INTERVAL 4 DAY)
        AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
        AND reconciliation_local_pending.valor_debito = reconciliation_external_pending.valor_credito
        AND (
          reconciliation_local_pending.referencia_1 = reconciliation_external_pending.referencia_1
          OR reconciliation_local_pending.referencia_2 = reconciliation_external_pending.referencia_1
          OR reconciliation_local_pending.referencia_3 = reconciliation_external_pending.referencia_1
        )
        WHERE 
          reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
          AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
          AND reconciliation_local_pending.valor_debito > 0
          group by localId
          HAVING COUNT(*) = 1";

    return DB::select($queryStr);
  }

  // REFERENCIA, FECHA,  VALOR
  public function case1($accounts, $companyId, $startDate, $endDate, $process)
  {
    $externalValuesTable = $this->getReconciliationExternalValuesTableName($companyId);
    $localValuesTable = $this->getReconciliationLocalValuesTableName($companyId);
    $pivotTable = $this->getReconciliationPivotTableName($companyId);

    $pivot = DB::select("SELECT * FROM " . $pivotTable . " WHERE `case` = 'CASE1' AND process = '" . $process . "'");
    if (count($pivot) > 0) {
      throw new Exception('Ya existe un caso 1 para este procesos', '400');
    }

    $this->prepareViews($accounts, $companyId, $startDate, $endDate);

    $queryStr = "INSERT INTO " . $pivotTable . " (`source_id`,`target_id`,`type`,`case`, process)
          SELECT 
            reconciliation_local_pending.id AS localId,
            reconciliation_external_pending.id AS externalId,
            '" . ReconciliationService::TYPE_LE . "',
            'CASE1',
            '" . $process . "'
          FROM reconciliation_local_pending
          JOIN reconciliation_external_pending ON
          reconciliation_local_pending.fecha_movimiento = reconciliation_external_pending.fecha_movimiento
          AND reconciliation_local_pending.local_account = reconciliation_external_pending.local_account
          AND reconciliation_local_pending.valor_debito = reconciliation_external_pending.valor_credito
          AND (
            reconciliation_local_pending.referencia_1 = reconciliation_external_pending.referencia_1
            OR reconciliation_local_pending.referencia_2 = reconciliation_external_pending.referencia_1
            OR reconciliation_local_pending.referencia_3 = reconciliation_external_pending.referencia_1)
          WHERE 
            reconciliation_local_pending.local_account IN (11100501 , 11100502, 11100506, 11100507)
            AND reconciliation_local_pending.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30'
            AND reconciliation_local_pending.valor_debito > 0";

    return DB::select($queryStr);
  }

  private function prepareViews($accounts, $companyId, $startDate, $endDate)
  {
    $localViewQuery = "CREATE OR REPLACE VIEW `reconciliation_local_pending` AS 
      SELECT 
        `reconciliation_local_values_2`.`id` AS `id`,
        `reconciliation_local_values_2`.`item_id` AS `item_id`,
        `reconciliation_local_values_2`.`tx_type_id` AS `tx_type_id`,
        `reconciliation_local_values_2`.`tx_type_name` AS `tx_type_name`,
        `reconciliation_local_values_2`.`fecha_movimiento` AS `fecha_movimiento`,
        `reconciliation_local_values_2`.`descripcion` AS `descripcion`,
        `reconciliation_local_values_2`.`local_account` AS `local_account`,
        `reconciliation_local_values_2`.`referencia_1` AS `referencia_1`,
        `reconciliation_local_values_2`.`referencia_2` AS `referencia_2`,
        `reconciliation_local_values_2`.`referencia_3` AS `referencia_3`,
        `reconciliation_local_values_2`.`valor_debito` AS `valor_debito`,
        `reconciliation_local_values_2`.`valor_credito` AS `valor_credito`,
        `reconciliation_local_values_2`.`valor_debito_credito` AS `valor_debito_credito`,
        `reconciliation_local_values_2`.`tipo_registro` AS `tipo_registro`
      FROM `reconciliation_local_values_2`
      WHERE NOT EXISTS (SELECT 1 FROM (SELECT `reconciliation_pivot_2`.`source_id`
        FROM `mdp`.`reconciliation_pivot_2`
        WHERE TYPE IN ('LE','LL')
        UNION 
        SELECT `reconciliation_pivot_2`.`target_id`
        FROM `mdp`.`reconciliation_pivot_2`
        WHERE TYPE IN ('LL','EL')) as R1 where R1.source_id = reconciliation_local_values_2.id)";

    $externalViewQuery = "CREATE OR REPLACE VIEW `reconciliation_external_pending` AS 
      SELECT 
        `reconciliation_external_values_2`.`id` AS `id`,
        `reconciliation_external_values_2`.`item_id` AS `item_id`,
        `reconciliation_external_values_2`.`tx_type_id` AS `tx_type_id`,
        `reconciliation_external_values_2`.`tx_type_name` AS `tx_type_name`,
        `reconciliation_external_values_2`.`descripcion` AS `descripcion`,
        `reconciliation_external_values_2`.`local_account` AS `local_account`,
        `reconciliation_external_values_2`.`valor_credito` AS `valor_credito`,
        `reconciliation_external_values_2`.`valor_debito` AS `valor_debito`,
        `reconciliation_external_values_2`.`valor_debito_credito` AS `valor_debito_credito`,
        `reconciliation_external_values_2`.`fecha_movimiento` AS `fecha_movimiento`,
        `reconciliation_external_values_2`.`referencia_1` AS `referencia_1`,
        `reconciliation_external_values_2`.`referencia_2` AS `referencia_2`,
        `reconciliation_external_values_2`.`referencia_3` AS `referencia_3`
      FROM
        `reconciliation_external_values_2`
      WHERE NOT EXISTS (SELECT 1 FROM (SELECT `reconciliation_pivot_2`.`source_id`
        FROM `mdp`.`reconciliation_pivot_2`
        WHERE TYPE IN ('EL','EE')
        UNION 
        SELECT `reconciliation_pivot_2`.`target_id`
        FROM `mdp`.`reconciliation_pivot_2`
        WHERE TYPE IN ('EE','LE')) as R1 where R1.source_id = reconciliation_external_values_2.id)";

    DB::select($localViewQuery);
    DB::select($externalViewQuery);
  }

  //HELPERS
  // public function oneExternalToManyLocal($queryResult, $case, $process)
  // {
  //   $accLocal = [];
  //   $accExternal = [];
  //   $assoc = [];
  //   foreach ($queryResult as $value) {
  //     if (in_array($value->localId, $accLocal) || in_array($value->externalId, $accExternal)) {
  //       continue;
  //     }
  //     $localIds = explode(',', $value->localId);
  //     foreach ($localIds as $id) {
  //       $assoc[] = [
  //         'local_value' => $id,
  //         'external_value' => $value->externalId,
  //         'case' => $case,
  //         'process' => $process
  //       ];
  //     }
  //     $accLocal[] = $value->localId;
  //     $accExternal[] = $value->externalId;
  //   }
  //   return $assoc;
  // }


  public function reduceManyLocalOneToOne($queryResult, $case, $process)
  {
    $acc = [];
    $assoc = [];
    foreach ($queryResult as $value) {
      $localIds = explode(',', $value->localId);
      foreach ($localIds as $id) {
        if (!in_array($id, $acc)) {
          $assoc[] = [
            'local_value' => $id,
            'external_value' => $value->externalId,
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

  public function reduceMulitpleExternalToMultipleInternal($queryResult, $case, $process)
  {
    $acc = [];
    $assoc = [];
    foreach ($queryResult as $value) {
      $localIds = explode(',', $value->localId);
      $externalIds = explode(',', $value->externalId);
      foreach ($externalIds as $eId) {
        if (!in_array($eId, $acc)) {
          foreach ($localIds as $lId) {
            $assoc[] = [
              'local_value' => $lId,
              'external_value' => $eId,
              'case' => $case,
              'process' => $process
            ];
          }
          $acc[] = $eId;
        }
      }
    }
    return $assoc;
  }

  public function reduceExternalToLocal($queryResult, $type, $case, $process)
  {
    $acc = [];
    $assoc = [];
    foreach ($queryResult as $value) {
      $externalsId = explode(',', $value->externalId);
      foreach ($externalsId as $id) {
        if (!in_array($id, $acc)) {
          $assoc[] = [
            'source_id' => $value->localId,
            'target_id' => $id,
            'type' => $type,
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


  public function oneExternalToManyLocal($queryResult, $type, $case, $process)
  {
    $accLocal = [];
    $accExternal = [];
    $assoc = [];
    foreach ($queryResult as $value) {
      if (in_array($value->localId, $accLocal) || in_array($value->externalId, $accExternal)) {
        continue;
      }
      $localIds = explode(',', $value->localId);
      foreach ($localIds as $id) {
        $assoc[] = [
          'source_id' => $id,
          'target_id' => $value->externalId,
          'type' => $type,
          'case' => $case,
          'process' => $process
        ];
      }
      $accLocal[] = $value->localId;
      $accExternal[] = $value->externalId;
    }
    return $assoc;
  }
  public function oneLocalToManyExternal($queryResult, $type, $case, $process)
  {
    $assoc = [];

    foreach ($queryResult as $value) {
      $externalIds = explode(',', $value->externalId);
      foreach ($externalIds as $id) {
        $assoc[] = [
          'source_id' => $value->localId,
          'target_id' => $id,
          'type' => $type,
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
