SET SQL_SAFE_UPDATES = 0;
UPDATE
    mdp.reconciliation_local_values_2
LEFT JOIN 
	mdp.reconciliation_local_tx_types_2 ON 
    mdp.reconciliation_local_values_2.tipo_registro = mdp.reconciliation_local_tx_types_2.reference
SET 
	mdp.reconciliation_local_values_2.tx_type_id =  reconciliation_local_tx_types_2.id,
    mdp.reconciliation_local_values_2.tx_type_name =  reconciliation_local_tx_types_2.tx
WHERE 
	mdp.reconciliation_local_values_2.local_account  IN ('11100501', '11100502','11100506','11100507') AND
	mdp.reconciliation_local_tx_types_2.type = 'SIMPLE' AND 
    mdp.reconciliation_local_values_2.fecha_movimiento <= '2023-09-08' AND 
    mdp.reconciliation_local_values_2.tx_type_id IS NULL;
SET SQL_SAFE_UPDATES = 1;
