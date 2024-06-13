SELECT localId, externalId, 'CASE2', '7wu3DCENt'
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
    LEFT JOIN reconciliation_external_values_2 ON localValues.fecha_movimiento = reconciliation_external_values_2.fecha_movimiento
        AND localValues.local_account = reconciliation_external_values_2.local_account
        AND localValues.valor_debito = reconciliation_external_values_2.valor_credito
        AND localValues.tx_type_name = reconciliation_external_values_2.tx_type_name
    WHERE
        localValues.local_account IN (11100501 , 11100502, 11100506, 11100507)
            AND localValues.valor_debito > 0
            AND reconciliation_external_values_2.local_account IS NOT NULL
            AND localValues.fecha_movimiento BETWEEN '2023-09-01' AND '2023-09-30'
    GROUP BY localId
    HAVING C > 1) AS CASE2D