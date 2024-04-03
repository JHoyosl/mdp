import { ReconciliationItem, ReconciliationResume } from "src/app/Interfaces/reconciliation.interface";

export class ReconciliationHelper {

  static getBalanceSum(item: ReconciliationItem | ReconciliationResume): number {
    const sum = Number(item.externalBalance) + 
      Number(item.externalDebit) - 
      Number(item.externalCredit) - 
      Number(item.localCredit) + 
      Number(item.localDebit)

    return sum;

  }

  static balanceDifference(item: ReconciliationItem | ReconciliationResume ): number {

    const sum = this.getBalanceSum(item);
    const difference = sum - Number(item.localBalance);
    
    return parseFloat(difference.toFixed(2));
  }
}