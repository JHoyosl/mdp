-- INSERT INTO reconciliation_pivot_2 (`external_value`,`local_value`,`case`, process)
SELECT 
    reconciliation_external_values_2.id AS externalId,
    localValues.id AS localId,
    'NOMINA',
    '7wu3DCENt'
FROM
    (SELECT 
        reconciliation_local_values_2.id,
            reconciliation_local_values_2.fecha_movimiento,
            reconciliation_local_values_2.local_account,
            reconciliation_local_values_2.tx_type_name,
            reconciliation_local_values_2.valor_debito
    FROM
        reconciliation_local_values_2
    LEFT JOIN reconciliation_pivot_2 ON reconciliation_local_values_2.id = reconciliation_pivot_2.local_value
    WHERE
        reconciliation_local_values_2.valor_debito > 0
            AND reconciliation_local_values_2.fecha_movimiento <= '2023-09-30'
            AND reconciliation_local_values_2.local_account IN (11100501 , 11100502, 11100506, 11100507)
            AND reconciliation_local_values_2.tx_type_name IN ('NOMINA')
            AND reconciliation_pivot_2.local_value IS NULL) AS localValues
        LEFT JOIN
    reconciliation_external_values_2 ON localValues.fecha_movimiento <= DATE_ADD(reconciliation_external_values_2.fecha_movimiento,
        INTERVAL 4 DAY)
        AND localValues.local_account = reconciliation_external_values_2.local_account
        AND localValues.tx_type_name = reconciliation_external_values_2.tx_type_name
GROUP BY localValues.id , externalId
ORDER BY localId , externalId