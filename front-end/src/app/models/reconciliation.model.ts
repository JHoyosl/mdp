import { ReconciliationItem } from "../Interfaces/reconciliation.interface";

export class ReconciliationModel{

  static AccountProcessToInterface(object:any): ReconciliationItem{
    return {
        id: object.id,
        bankId: object.bank_id,
        companyId: object.company_id,
        accType: object.acc_type,
        bankAccount: object.bank_account,
        localAccount: object.local_account,
        mapId: object.map_id,
        deletedAt: object.deleted_at,
        createdAt: object.created_at,
        updatedAt: object.updated_at,
        accountId: object.account_id,
        process: object.process,
        startDate: object.start_date,
        endDate: object.end_date,
        externalDebit: object.external_debit,
        externalCredit: object.external_credit,
        localDebit: object.local_debit,
        localCredit: object.local_credit,
        externalBalance: object.external_balance,
        localBalance: object.local_balance,
        difference: object.difference,
        status: object.status,
        step: object.step,
        type: object.type,
        codComp: object.cod_comp,
        name: object.name,
        nit: object.nit,
        currency: object.currency,
        portal: object.portal,
    }
  }
}