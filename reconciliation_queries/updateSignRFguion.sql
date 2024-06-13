SELECT 
	reconciliation_external_values_2.id as valId,
    T2.id as T2id,
    reconciliation_external_values_2.referencia_1,
    T2.matched
FROM 
-- UPDATE
	reconciliation_external_values_2
INNER JOIN 
(SELECT 
		reconciliation_external_values_2.id,
		CAST(SUBSTRING_INDEX(reconciliation_external_values_2.descripcion,'-',-1) AS CHAR) AS matched
	FROM 
		external_tx_types 
	INNER JOIN 
		reconciliation_external_values_2 ON 
		external_tx_types.id = reconciliation_external_values_2.tx_type_id
	WHERE 
		external_tx_types.sign = 'RF' 
		AND CONCAT('',SUBSTRING_INDEX(reconciliation_external_values_2.descripcion,'-',-1)*1) > 0
		AND reconciliation_external_values_2.referencia_1 IS NULl
) AS T2 ON reconciliation_external_values_2.id = T2.id
-- SET 
-- 	reconciliation_external_values_2.referencia_1 = T2.matched


-- Error Code: 1292. Truncated incorrect DOUBLE value: 'Abono por recaudo de Servicios por CNB'
