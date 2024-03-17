import { Bank } from "./bank.interface"
import { GenericResponse } from "./shared.interfaces"

export interface ThirdPartyAccount {
    id: number
    bank_id: number
    company_id: number
    acc_type: string
    bank_account: string
    local_account: string
    map_id: number
    deleted_at: any
    created_at: any
    updated_at: string
    banks: Bank
}

export interface ThirdPartyHeaderInfo {
    id: number
    accountId: number
    companyId: number
    uploadedBy: number
    path: string
    fileName: string
    startDate: string
    endDate: string
    rows: number
    status: string
    createdAt: string
    updatedAt: string
    deletedAt: any
}

export interface ThirdPartyHeaderItems {

    id: number
    headerId: number
    matched: number
    txTypeId: number
    txTypeName: string
    itemId: number
    descripcion: string
    operador: string
    valorCredito: string
    valorDebito: string
    valorDebitoCredito: any
    fechaMovimiento: string
    fechaArchivo: any
    codigoTx: string
    referencia1: string
    referencia2: string
    referencia3: string
    nombreTitular: string
    identificacionTitular: string
    numeroCuenta: string
    nombreTransaccion: string
    consecutivoRegistro: string
    nombreOficina: string
    codigoOficina: string
    canal: string
    nombreProveedor: string
    idProveedor: string
    bancoDestino: string
    fechaRechazo: string
    motivoRechazo: string
    ciudad: string
    tipoCuenta: string
    numeroDocumento: string
    createdAt: string
    updatedAt: string
  }
  
export interface ThirdPartyHeaderItemsResponose extends GenericResponse {
    data: ThirdPartyHeaderItems[];
}

export interface ThirdPartyHeaderInfoResponse extends GenericResponse{
    data: ThirdPartyHeaderInfo[];
}

export interface ThirdPartyAccountResponse extends GenericResponse {
    data: ThirdPartyAccount[];
}

export interface ThirdPartyAccountInfoUpload {
    accountId: string,
    startDate: string,
    endDate: string, 
    file: File
}