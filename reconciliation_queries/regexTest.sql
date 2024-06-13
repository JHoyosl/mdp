SELECT 
    *
FROM
    mdp.reconciliation_local_values_2
WHERE
mdp.reconciliation_local_values_2.descripcion REGEXP '\d*[[:space:]]-[[:space:]]\W*'