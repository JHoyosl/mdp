SET SQL_SAFE_UPDATES = 0;
UPDATE
    mdp.`reconciliation_external_values_2`
LEFT JOIN 
	mdp.external_tx_types ON 
    mdp.`reconciliation_external_values_2`.descripcion = mdp.external_tx_types.description
SET 
	mdp.reconciliation_external_values_2.tx_type_id = mdp.external_tx_types.id,
    mdp.reconciliation_external_values_2.tx_type_name = mdp.external_tx_types.tx
WHERE 
	mdp.`reconciliation_external_values_2`.local_account  IN ('11100501', '11100502','11100506','11100507') AND
	mdp.external_tx_types.type = 'SIMPLE' 
    AND mdp.`reconciliation_external_values_2`.tx_type_id IS NULL;
SET SQL_SAFE_UPDATES = 1;