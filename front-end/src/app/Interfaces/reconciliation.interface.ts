import { Account } from "./account.interface";
import { GenericResponse } from "./shared.interfaces"

export interface ReconciliationItem{
    id: number;
    bankId: number;
    companyId: number;
    accType: string;
    bankAccount: string;
    localAccount: string;
    deletedAt: string;
    createdAt: string;
    updatedAt: string;
    accountId: string;
    process: string;
    startDate: string;
    endDate: string;
    externalDebit: number;
    externalCredit: number;
    localDebit: number;
    localCredit: number;
    externalBalance: number;
    prevExternalBalance: number;
    localBalance: number;
    prevLocalBalance: number;
    difference: number;
    status: string;
    step: string;
    type: string;
    name: string;
    nit: string;
}

export interface ReconciliationItemResponse extends GenericResponse{
    data: ReconciliationItem[];
}

export interface ReconciliationIniUpload {
    file: File;
    date: string;
}

export interface ReonciliationBalance {
    id: number;
    accountId: number;
    process: string;
    startDate: string;
    endDate: string;
    externalDebit: string;
    externalCredit: string;
    localDebit: string;
    localCredit: string;
    externalBalance: string;
    prevExternalBalance: string;
    localBalance: string;
    prevLocalBalance: string;
    difference: string;
    status: string;
    step: string;
    type: string;
    deletedAt: string;
    createdAt: string;
    updatedAt: string;
    account: Account;
}

export interface ReconciliationIniUploadResponse extends GenericResponse {
    data: ReonciliationBalance[]
}

export interface ReconciliationBalanceUpload {
    localAccount: string;
    localBalance: number;
    externalBalance: number;
}

export interface ReconciliationResume {
    accountId: number;
    bankId: number;
    bankAccount: string;
    localAccount: string;
    name: string;
    process: string;
    startDate: string;
    endDate: string;
    externalDebit: string;
    externalCredit: string;
    localDebit: string;
    localCredit: string;
    externalBalance: string;
    localBalance: string;
    difference: string;
    type: string;
    status: string;
    step: string;
  }


export interface ReconciliationResumeResponse extends GenericResponse {
    data: ReconciliationResume[];
}
  
export interface DetailInfo {
    type: string;
    prevBalance: number;
    credit: number;
    debit: number;
    balanceSum: number;
    newBalance: number;
    difference: number;
} 

export interface DetailedBalance {
    itemId: number;
    bank: string;
    localAccount: string;
    externalAccount: string;
    startDate: string;
    endDate: string;
    detailInfo: DetailInfo[];
}