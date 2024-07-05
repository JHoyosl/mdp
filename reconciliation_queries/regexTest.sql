SELECT 
    *
FROM
    mdp.reconciliation_local_values_2
WHERE
mdp.reconciliation_local_values_2.descripcion REGEXP '\d*[[:space:]]-[[:space:]]\W*';


SELECT * FROM mdp.reconciliation_local_values_2
WHERE 
tipo_registro NOT IN ('TARJETA DEBITO','AJUSTES Y REVERSIONES') AND 
descripcion REGEXP '^([0-9])+[[:space:]]-[[:space:]](\W[[:space:]])*';