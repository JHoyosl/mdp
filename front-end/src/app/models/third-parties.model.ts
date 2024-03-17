import { ThirdPartyHeaderInfo, ThirdPartyHeaderItems } from "../Interfaces/thirdParties.interface";

export class thirdPartiesModel {

    static thirdPartiesToInterface( object: any ): ThirdPartyHeaderInfo {
        return {
            id: object.id,
            accountId: object.account_id,
            companyId: object.company_id,
            uploadedBy: object.uploaded_by,
            path: object.path,
            fileName: object.file_name,
            startDate: object.start_date,
            endDate: object.end_date,
            rows: object.rows,
            status: object.status,
            createdAt: object.created_at,
            updatedAt: object.updated_at,
            deletedAt: object.deleted_at
        }
    }

    static thirdPartyHeaderItemsToInterface( object: any ): ThirdPartyHeaderItems {
        return {
            id: object.id,
            headerId: object.header_id,
            matched: object.matched,
            txTypeId: object.tx_type_id,
            txTypeName: object.tx_type_name,
            itemId: object.item_id,
            descripcion: object.descripcion,
            operador: object.operador,
            valorCredito: object.valor_credito,
            valorDebito: object.valor_debito,
            valorDebitoCredito: object.valor_debito_credito,
            fechaMovimiento: object.fecha_movimiento,
            fechaArchivo: object.fecha_archivo,
            codigoTx: object.codigo_tx,
            referencia1: object.referencia_1,
            referencia2: object.referencia_2,
            referencia3: object.referencia_3,
            nombreTitular: object.nombre_titular,
            identificacionTitular: object.identificacion_titular,
            numeroCuenta: object.numero_cuenta,
            nombreTransaccion: object.nombre_transaccion,
            consecutivoRegistro: object.consecutivo_registro,
            nombreOficina: object.nombre_oficina,
            codigoOficina: object.codigo_oficina,
            canal: object.canal,
            nombreProveedor: object.nombre_proveedor,
            idProveedor: object.id_proveedor,
            bancoDestino: object.banco_destino,
            fechaRechazo: object.fecha_rechazo,
            motivoRechazo: object.motivo_rechazo,
            ciudad: object.ciudad,
            tipoCuenta: object.tipo_cuenta,
            numeroDocumento: object.numero_documento,
            createdAt: object.created_at,
            updatedAt: object.updated_at,
        }
    }
}