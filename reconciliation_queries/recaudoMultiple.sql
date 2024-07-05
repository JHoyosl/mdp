SELECT * FROM(
SELECT GROUP_CONCAT(localId) as Ids, externalId, SUM(vdLocal) as vdLocal, vcExternal as vcExternal FROM (SELECT
`localValues`.`id` AS `localId`,
reconciliation_external_values_2.`id` AS `externalId`,
`localValues`.`fecha_movimiento` AS `lFecha_mov`,
reconciliation_external_values_2.`fecha_movimiento` AS `eFecha_mov`,
`localValues`.`local_account` AS `lAccount`,
reconciliation_external_values_2.`local_account` AS `eAccount`,
`localValues`.`valor_debito` AS `vdLocal`,
reconciliation_external_values_2.`valor_credito` AS `vcExternal`,
`localValues`.`valor_credito` AS `vcLocal`,
reconciliation_external_values_2.`valor_debito` AS `vdExternal`,
reconciliation_external_values_2.`referencia_1` AS `eReferencia_1`,
`localValues`.`referencia_1` AS `lReferencia_1`,
reconciliation_external_values_2.`referencia_2` AS `eReferencia_2`,
`localValues`.`referencia_2` AS `lReferencia_2`,
`localValues`.`referencia_3` AS `eReferencia_3`,
`localValues`.`referencia_3` AS `lReferencia_3`
FROM (Select * from reconciliation_local_values_2
LEFT JOIN
reconciliation_pivot_2 ON
reconciliation_local_values_2.id = reconciliation_pivot_2.local_value
WHERE
reconciliation_pivot_2.local_value IS NULL) AS localValues
LEFT JOIN
reconciliation_external_values_2 ON
localValues.fecha_movimiento <= DATE_ADD(reconciliation_external_values_2.fecha_movimiento, INTERVAL 4 DAY) AND
    `localValues`.`local_account`=reconciliation_external_values_2.`local_account` AND (
    `localValues`.`referencia_1`=reconciliation_external_values_2.`referencia_1` OR
    `localValues`.`referencia_2`=reconciliation_external_values_2.`referencia_1` OR
    `localValues`.`referencia_3`=reconciliation_external_values_2.`referencia_1` ) WHERE `localValues`.`local_account`
    IN (11100501,11100502,11100506,11100507) AND `localValues`.`valor_debito`> 0
    AND reconciliation_external_values_2.`local_account` IS NOT NULL
    AND `localValues`.`fecha_movimiento` BETWEEN '2023-09-01' AND '2023-09-30') AS firstRun
    GROUP BY externalId) as T2
    WHERE vdLocal = vcExternal