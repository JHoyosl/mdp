export interface BalanceList{
  id: number
  date: string
  fileName: string
  filePath: string
  status: string
  user: string
  deletedAt: string
  createdAt: string
  updatedAt: string
}

export interface BalanceResultResponse {
  balance: Balance
  nautralezaContable: NautralezaContable[]
  nautralezaOperativa: NautralezaOperativa[]
}

export interface Balance {
  header: Header
  items: Item[]
}

export interface Header {
  id: number
  fecha: string
  file_name: string
  file_path: string
  status: string
  user: string
  deleted_at: any
  created_at: any
  updated_at: string
}

export interface Item {
  cuenta: string
  nombre_cuenta: string
  saldo_actual: string
}

export interface NautralezaContable {
  cuenta_maestro: string
  saldo_actual: string
  header_id: number
  cuenta_balance: string
  area: string
  descripcion: string
  naturaleza: string
  tipo_saldo: string
}

export interface NautralezaOperativa {
  cuenta_maestro: string
  saldo_actual: string
  header_id: number
  cuenta_balance: string
  area: string
  descripcion: string
  naturaleza: string
  tipo_saldo: string
}

export interface UploadCuadreDialog {
  title: string;
  source: string;
}

export interface uploadCuadreDialogResponse{
  source: string;
  date: string;
}

export interface uploadCuadreRequest{
  date:string;
  file: File;
  source: 'balance'
}

//AGREEMENTS
export interface AgreementsHeader{
  balanceId: number;
  agreementId: number;
  balanceDate: string;
  agreementDate: string;
  status: string;
  user: number;
}

export interface AgreementsRequestUpload{
  date: string;
  file: File;
}

export interface AgreeementsResult {
  account: string;
  line: string;
  name: string;
  saldoActual: string;
  sumSalcuo: string;
  difference: string;
}