import { Bank } from "./bank.interface";

export interface ExternalTxType {
  id: number,
  description: string,
  tx: string,
  reference: string,
  type: 'COMPUESTO' | 'SIMPLE',
  sign: string,
  deletedAt: string,
  createdAt: string,
  updatedAt: string,
  bank: Bank
}

export interface LocalTxType {
    id: number
    description: string
    tx: string
    companyId: number
    reference: string
    sign: string
    deletedAt: string
    createdAt: string
    updatedAt: string
}


export interface CreateLocalTxRequest {
  description: string;
  tx: string;
  reference: string;
  sign: string;
}

export interface CreateExternalTxRequest extends CreateLocalTxRequest{
  bankId: number;
  type: 'COMPUESTO' | 'SIMPLE';
}

export interface UpdateLocalTxRequest extends CreateLocalTxRequest{}
export interface UpdateExternalTxRequest extends CreateExternalTxRequest{}

export type TxType = 'local' | 'external';