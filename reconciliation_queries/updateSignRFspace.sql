SELECT 
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
    AND CONCAT('',SUBSTRING_INDEX(reconciliation_external_values_2.descripcion,' ',-1)*1) > 0
    AND reconciliation_external_values_2.referencia_1 IS NULL;
