INSERT INTO reconciliation_pivot_2 (local_value, external_value,`case`, process)
SELECT 
		localId,
		externalId,
    'CASE1B',
    '7wu3DCENt'
FROM
    (SELECT 
						localValues.id AS localId,
            GROUP_CONCAT(reconciliation_external_values_2.id) AS externalId,
						COUNT(*) C
    FROM
        (SELECT 
        *
    FROM
        reconciliation_local_values_2
    LEFT JOIN reconciliation_pivot_2 ON reconciliation_local_values_2.id = reconciliation_pivot_2.local_value
    WHERE
        reconciliation_pivot_2.local_value IS NULL) AS localValues
    LEFT JOIN reconciliation_external_values_2 ON localValues.fecha_movimiento <= DATE_ADD(reconciliation_external_values_2.fecha_movimiento, INTERVAL 4 DAY)
        AND localValues.fecha_movimiento > reconciliation_external_values_2.fecha_movimiento
        AND localValues.local_account = reconciliation_external_values_2.local_account
        AND localValues.valor_debito = reconciliation_external_values_2.valor_credito
        AND (localValues.referencia_1 = reconciliation_external_values_2.referencia_1
        OR localValues.referencia_2 = reconciliation_external_values_2.referencia_1
        OR localValues.referencia_3 = reconciliation_external_values_2.referencia_1)
    WHERE
        localValues.local_account IN ('11100501' , '11100502', '11100506', '11100507')
            AND localValues.valor_debito > 0
            AND reconciliation_external_values_2.local_account IS NOT NULL
            AND localValues.fecha_movimiento BETWEEN '2023-09-1' AND '2023-09-30' GROUP BY localId
HAVING C = 1) AS CASE1B
