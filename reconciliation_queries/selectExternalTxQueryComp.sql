SELECT 
	mdp.`external_tx_types`.id,
    mdp.`reconciliation_external_values_2`.id,
    mdp.`external_tx_types`.description,
    mdp.`reconciliation_external_values_2`.descripcion,
    mdp.`external_tx_types`.tx
FROM
    mdp.`reconciliation_external_values_2`
LEFT JOIN 
	mdp.external_tx_types ON 
    UPPER(mdp.`reconciliation_external_values_2`.descripcion) LIKE CONCAT(UPPER(mdp.external_tx_types.description),'%')
WHERE 
	mdp.`reconciliation_external_values_2`.local_account  IN ('11100501', '11100502','11100506','11100507') AND
	mdp.external_tx_types.type = 'COMPUESTO' 
    AND mdp.`reconciliation_external_values_2`.tx_type_id IS NULL;

