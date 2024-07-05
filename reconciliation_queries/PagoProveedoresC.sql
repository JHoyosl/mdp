SELECT 
    COUNT(*) C,
    localId AS localId,
    GROUP_CONCAT(externalId) AS externalId,
    'CASE5B',
    '7wu3DCENt'
FROM
    (SELECT 
        localValues.id AS localId,
            reconciliation_external_values_2.id AS externalId,
            localValues.fecha_movimiento AS lFecha_mov,
            reconciliation_external_values_2.fecha_movimiento AS eFecha_mov,
            localValues.local_account AS lAccount,
            reconciliation_external_values_2.local_account AS eAccount,
            localValues.valor_debito AS vdLocal,
            reconciliation_external_values_2.valor_credito AS vcExternal,
            localValues.valor_credito AS vcLocal,
            reconciliation_external_values_2.valor_debito AS vdExternal,
            reconciliation_external_values_2.referencia_1 AS eReferencia_1,
            localValues.referencia_1 AS lReferencia_1,
            reconciliation_external_values_2.referencia_2 AS eReferencia_2,
            localValues.referencia_2 AS lReferencia_2,
            localValues.referencia_3 AS eReferencia_3,
            localValues.referencia_3 AS lReferencia_3
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
        AND localValues.valor_credito = reconciliation_external_values_2.valor_debito
        AND reconciliation_external_values_2.referencia_2 IS NOT NULL
    WHERE
        localValues.local_account IN (11100501 , 11100502, 11100506, 11100507)
            AND localValues.valor_credito > 0
            AND reconciliation_external_values_2.local_account IS NOT NULL
            AND localValues.fecha_movimiento BETWEEN '2023-09-01' AND '2023-09-30'
    ORDER BY localId , externalId) AS CASE1PAGOS
GROUP BY localId
HAVING C > 1