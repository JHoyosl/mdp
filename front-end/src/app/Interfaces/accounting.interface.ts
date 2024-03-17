export interface AccountingHeader {
    'id': number;
    'companyId': number;
    'uploadedBy': number;
    'path': string;
    'fileName': string;
    'startDate': string;
    'endDate': string;
    'rows': number;
    'status': string;
    'createdAt': string;
    'updatedAt': string;
    'deletedAt': string
}

export interface AccountingHeaderResponse {
    data: AccountingHeader[];
    message: string;
    status: boolean
}

export interface AccountingUploadInfo {
    startDate: string,
    endDate: string, 
    file: File
}

export interface AccountingItem {
    id: number
    header_id: number
    matched: number
    item_id: number
    tx_type_id: any
    tx_type_name: any
    fecha_movimiento: string
    descripcion: string
    local_account: string
    cuenta_externa: string
    referencia_1: string
    referencia_2: any
    referencia_3: any
    otra_referencia: any
    saldo_actual: string
    valor_debito: string
    saldo_anterior: any
    valor_credito: string
    codigo_usuario: any
    nombre_agencia: any
    valor_debito_credito: any
    nombre_centro_costos: any
    codigo_centro_costo: any
    numero_comprobante: any
    nombre_usuario: string
    nombre_cuenta_contable: any
    numero_cuenta_contable: any
    nombre_tercero: any
    identificacion_tercero: string
    fecha_ingreso: string
    fecha_origen: any
    oficina_origen: any
    oficina_destino: string
    numero_lote: string
    consecutivo_lote: string
    tipo_registro: string
    ambiente_origen: string
    beneficiario: string
    created_at: any
    updated_at: any
  }
  