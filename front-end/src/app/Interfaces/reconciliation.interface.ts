import { Account } from "./account.interface";
import { GenericResponse } from "./shared.interfaces"

export interface ReconciliationItem{
    id: number;
    bankId: number;
    companyId: number;
    accType: string;
    bankAccount: string;
    localAccount: string;
    mapId: number;
    deletedAt: string;
    createdAt: string;
    updatedAt: string;
    accountId: string;
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
    status: string;
    step: string;
    type: string;
    codComp: string;
    name: string;
    nit: string;
    currency: string;
    portal: string;
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
    localBalance: string;
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
