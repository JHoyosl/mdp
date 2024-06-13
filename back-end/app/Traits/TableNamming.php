<?php

namespace App\Traits;


trait TableNamming
{
  public function getReconciliationItemTableName($companyId): string
  {
    return 'reconciliation_items_' . $companyId;
  }
  public function getReconciliationLocalValuesTableName($companyId): string
  {
    return 'reconciliation_local_values_' . $companyId;
  }
  public function getReconciliationExternalValuesTableName($companyId): string
  {
    return 'reconciliation_external_values_' . $companyId;
  }
  public function getReconciliationPivotTableName($companyId): string
  {
    return 'reconciliation_pivot_' . $companyId;
  }
  public function getLocalTxTypeTableName($companyId): string
  {
    return 'reconciliation_local_tx_types_' . $companyId;
  }

  public function getThirdPartyItemsTableName($companyId)
  {
    return 'third_parties_items_' . $companyId;
  }

  public function getAccountingItemsTableName($companyId)
  {
    return 'accounting_items_' . $companyId;
  }
}
