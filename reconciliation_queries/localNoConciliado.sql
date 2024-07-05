SELECT * FROM reconciliation_local_values_2
LEFT JOIN reconciliation_pivot_2 ON 
reconciliation_pivot_2.local_value = reconciliation_local_values_2.id
WHERE 
reconciliation_pivot_2.local_value IS NULL
AND reconciliation_local_values_2.tx_type_name <> 'TD'
AND reconciliation_local_values_2.tipo_registro <> 'PSE COOP DOGITAL';