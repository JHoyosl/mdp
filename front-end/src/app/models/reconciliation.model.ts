import { ReconciliationItem, ReconciliationResume, ReonciliationBalance } from '../Interfaces/reconciliation.interface';

export class ReconciliationModel{

  static AccountProcessToInterface(object:any): ReconciliationItem{
    return {
        id: object.id,
        accountId: object.account_id,
        bankId: object.bank_id,
        companyId: object.company_id,
        accType: object.acc_type,
        bankAccount: object.bank_account,
        localAccount: object.local_account,
        deletedAt: object.deleted_at,
        createdAt: object.created_at,
        updatedAt: object.updated_at,
        process: object.process,
        startDate: object.start_date,
        endDate: object.end_date,
        externalDebit: object.external_debit,
        externalCredit: object.external_credit,
        localDebit: object.local_debit,
        localCredit: object.local_credit,
        externalBalance: object.external_balance,
        prevExternalBalance: object.prev_external_balance,
        localBalance: object.local_balance,
        prevLocalBalance: object.prev_local_balance,
        difference: object.difference,
        status: object.status,
        step: object.step,
        type: object.type,
        name: object.name,
        nit: object.nit,
    }
  }

  static ReonciliationBalanceToInterface(object: any ): ReonciliationBalance {
    return {
      id: object.id,
      accountId: object.account_id,
      process: object.id,
      startDate: object.start_date,
      endDate: object.end_date,
      externalDebit: object.external_debit,
      externalCredit: object.external_credit,
      localDebit: object.local_debit,
      localCredit: object.local_credit,
      externalBalance: object.external_balance,
      prevExternalBalance: object.prev_external_balance,
      localBalance: object.local_balance,
      prevLocalBalance: object.prev_local_balanceprev_local_balance,
      difference: object.difference,
      status: object.status,
      step: object.step,
      type: object.type,
      deletedAt: object.deleted_at,
      createdAt: object.created_at,
      updatedAt: object.updated_at,
      account: object.account,
    }
  }  

  static ReconciliationResumeToInterfac( object: any ): ReconciliationResume {

    return {
      accountId: object.accountId,
      bankId: object.bank_id,
      bankAccount: object.bank_account,
      localAccount: object.local_account,
      name: object.name,
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
      type: object.type,
      status: object.status,
      step: object.step,
    }
  }
}