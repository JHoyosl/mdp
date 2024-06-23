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

  public function getBalanceSheetHeadersTableName($companyId)
  {
    return 'balance_general_headers_' . $companyId;
  }

  public function getBalanceSheetItemsTableName($companyId)
  {
    return 'balance_general_items_' . $companyId;
  }

  public function getAgreemenetsHeadersTableName($companyId)
  {
    return 'agreements_headers_' . $companyId;
  }

  public function getAgreemenetsItemsTableName($companyId)
  {
    return 'agreements_items_' . $companyId;
  }

  public function getAgreementsMasterTableName($companyId)
  {
    return 'agreements_master_' . $companyId;
  }

  public function getMasterOperational($companyId)
  {
    return 'operativo_cuentas_' . $companyId;
  }
}
