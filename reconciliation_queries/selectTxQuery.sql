SELECT 
	mdp.reconciliation_local_tx_types_2.id,
    mdp.reconciliation_local_values_2.id,
    mdp.reconciliation_local_tx_types_2.description,
    mdp.reconciliation_local_values_2.descripcion,
    mdp.reconciliation_local_tx_types_2.tx
FROM
    mdp.reconciliation_local_values_2
LEFT JOIN 
	mdp.reconciliation_local_tx_types_2 ON 
    mdp.reconciliation_local_values_2.descripcion = mdp.reconciliation_local_tx_types_2.description
WHERE 
	mdp.reconciliation_local_values_2.local_account  IN ('11100501') AND
	mdp.reconciliation_local_tx_types_2.type = 'SIMPLE' AND 
    mdp.reconciliation_local_values_2.tx_type_id IS NULL
ORDER BY mdp.reconciliation_local_values_2.id