<?php

use Illuminate\Database\Seeder;

class LocalTxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('local_tx_types')->delete();

        $local_tx = array(

           array('description' =>'Ab ajuste grav mov financieors', 'tx' =>'DGMF', 'company_id'=>1, 'reference' =>'Ab ajuste gr', 'sign' =>'D'),
        array('description' =>'ABONO A CREDITO ORD 353217645', 'tx' =>'EGRES', 'company_id'=>1, 'reference' =>'ABONO A CRED', 'sign' =>'C'),
        array('description' =>'Ajuste Devolución de cheque', 'tx' =>'DEVC', 'company_id'=>1, 'reference' =>'Ajuste Devol', 'sign' =>'C'),
        array('description' =>'Aprovisionamiento AV VILLAS', 'tx' =>'NCC', 'company_id'=>1, 'reference' =>'Aprovisionam', 'sign' =>'C'),
        array('description' =>'Billete falso  envio a brinks Feb 22', 'tx' =>'NCC', 'company_id'=>1, 'reference' =>'Billete fals', 'sign' =>'C'),
        array('description' =>'BILLETE FALSO EN ENVIO A BRINNS ENERO 28', 'tx' =>'NCC', 'company_id'=>1, 'reference' =>'BILLETE FALS', 'sign' =>'C'),
        array('description' =>'CANCELACION FORMULARIO 3509633542864 RET', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'CANCELACION ', 'sign' =>'C'),
        array('description' =>'Cargo comision Giros Empresariales', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'Cargo comisi', 'sign' =>'C'),
        array('description' =>'Cargo Iva', 'tx' =>'IVAC', 'company_id'=>1, 'reference' =>'Cargo Iva', 'sign' =>'C'),
        array('description' =>'Cheques Consignados en el dia', 'tx' =>'DEPOS', 'company_id'=>1, 'reference' =>'Cheques Cons', 'sign' =>'D'),
        array('description' =>'COBRO DE COMISION CENIT', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'COBRO DE COM', 'sign' =>'C'),
        array('description' =>'COBRO IVA PAGOS AUTOMATICOS', 'tx' =>'IVAC', 'company_id'=>1, 'reference' =>'COBRO IVA PA', 'sign' =>'C'),
        array('description' =>'Cobro Pago Proveedores Davivienda', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'Cobro Pago P', 'sign' =>'C'),
        array('description' =>'Cobro Plazas Especiales', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'Cobro Plazas', 'sign' =>'C'),
        array('description' =>'Cobro Recaudo Con Codigo De Barras', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'Cobro Recaud', 'sign' =>'C'),
        array('description' =>'COBRO SERV RECAUDO NACIONAL', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'COBRO SERV R', 'sign' =>'C'),
        array('description' =>'Cobro Servicio Recaudo Nacional.', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'Cobro Servic', 'sign' =>'C'),
        array('description' =>'COBRO TRANSF OCCIRED', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'COBRO TRANSF', 'sign' =>'C'),
        array('description' =>'COMIS IVA PAGOS SUC VIRT EMP', 'tx' =>'IVAC', 'company_id'=>1, 'reference' =>'COMIS IVA PA', 'sign' =>'C'),
        array('description' =>'COMIS PAGOS SUC VIRT EMPRESAS', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'COMIS PAGOS ', 'sign' =>'C'),
        array('description' =>'COMIS RECAUDOS CAJA NACIONAL', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'COMIS RECAUD', 'sign' =>'C'),
        array('description' =>'COMIS SERVICIOS DE RECAUDO', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'COMIS SERVIC', 'sign' =>'C'),
        array('description' =>'COMISION CAJA CODIGO DE BARRAS', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'COMISION CAJ', 'sign' =>'C'),
        array('description' =>'COMISION CB CODIGO DE BARRAS', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'COMISION CB ', 'sign' =>'C'),
        array('description' =>'Comision disfon proveedores interno', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'Comision dis', 'sign' =>'C'),
        array('description' =>'COMISION PAGO A PROVEEDORES', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'COMISION PAG', 'sign' =>'C'),
        array('description' =>'COMISION POR RECAUDO ELECTR', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'COMISION POR', 'sign' =>'C'),
        array('description' =>'COMISION PSE', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'COMISION PSE', 'sign' =>'C'),
        array('description' =>'COMISION RECAUDO CAJERO NACIONAL', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'COMISION REC', 'sign' =>'C'),
        array('description' =>'COMISION SERVICIO RECAUDO', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'COMISION SER', 'sign' =>'C'),
        array('description' =>'Comisión solicitud de copias', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'Comisión sol', 'sign' =>'C'),
        array('description' =>'COMISION TRANSFERENCIA DE FONDO', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'COMISION TRA', 'sign' =>'C'),
        array('description' =>'Consignación AV VILLAS', 'tx' =>'DEPOS', 'company_id'=>1, 'reference' =>'Consignación', 'sign' =>'D'),
        array('description' =>'CUOTA CRED ORD 11060636', 'tx' =>'EGRES', 'company_id'=>1, 'reference' =>'CUOTA CRED O', 'sign' =>'C'),
        array('description' =>'CUOTA DE MANEJO RED PUBLICA INTERNET', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'CUOTA DE MAN', 'sign' =>'C'),
        array('description' =>'CUOTA MANEJO CUPO ROTATIVO', 'tx' =>'COMC', 'company_id'=>1, 'reference' =>'CUOTA MANEJO', 'sign' =>'C'),
        array('description' =>'Emision de Cheque x Reposicion', 'tx' =>'CHC', 'company_id'=>1, 'reference' =>'Emision de C', 'sign' =>'C'),
        array('description' =>'Faltante envio brinks  feb 12 y marzo 10', 'tx' =>'NCC', 'company_id'=>1, 'reference' =>'Faltante env', 'sign' =>'C'),
        array('description' =>'GMF SEMANA 13 DEL (26/03/16 AL 01/04/16)', 'tx' =>'GMFC', 'company_id'=>1, 'reference' =>'GMF SEMANA 1', 'sign' =>'C'),
        array('description' =>'Gravamen a los Movimientos Financieros', 'tx' =>'GMFC', 'company_id'=>1, 'reference' =>'Gravamen a l', 'sign' =>'C'),
        array('description' =>'Gravamen movimiento financiero', 'tx' =>'GMFC', 'company_id'=>1, 'reference' =>'Gravamen mov', 'sign' =>'C'),
        array('description' =>'GRAVAMEN MVTO. FINAN', 'tx' =>'DGMF', 'company_id'=>1, 'reference' =>'GRAVAMEN MVT', 'sign' =>'D'),
        array('description' =>'Interes de sobregiro', 'tx' =>'INSC', 'company_id'=>1, 'reference' =>'Interes de s', 'sign' =>'C'),
        array('description' =>'Interes ganado', 'tx' =>'INTC', 'company_id'=>1, 'reference' =>'Interes gana', 'sign' =>'D'),
        array('description' =>'INTERES SOBREGIRO', 'tx' =>'INSC', 'company_id'=>1, 'reference' =>'INTERES SOBR', 'sign' =>'C'),
        array('description' =>'INTERESES DE SOBREGIRO', 'tx' =>'INSC', 'company_id'=>1, 'reference' =>'INTERESES DE', 'sign' =>'C'),
        array('description' =>'INTERESES POR SOREGIRO', 'tx' =>'INSC', 'company_id'=>1, 'reference' =>'INTERESES PO', 'sign' =>'C'),
        array('description' =>'Intereses sobregiro', 'tx' =>'INSC', 'company_id'=>1, 'reference' =>'Intereses so', 'sign' =>'C'),
        array('description' =>'IVA', 'tx' =>'IVAC', 'company_id'=>1, 'reference' =>'IVA', 'sign' =>'C'),
        array('description' =>'IVA COBRADO', 'tx' =>'IVAC', 'company_id'=>1, 'reference' =>'IVA COBRADO', 'sign' =>'C'),
        array('description' =>'IVA COMISION PSE', 'tx' =>'IVAC', 'company_id'=>1, 'reference' =>'IVA COMISION', 'sign' =>'C'),
        array('description' =>'IVA CUOTA MANEJO CUPO ROTATIVO', 'tx' =>'IVAC', 'company_id'=>1, 'reference' =>'IVA CUOTA MA', 'sign' =>'C'),
        array('description' =>'IVA POR SERVICIO DE RECAUDO', 'tx' =>'IVAC', 'company_id'=>1, 'reference' =>'IVA POR SERV', 'sign' =>'C'),
        array('description' =>'N.D. GMF AUTOMA', 'tx' =>'GMFC', 'company_id'=>1, 'reference' =>'N.D. GMF AUT', 'sign' =>'C'),
        array('description' =>'NC Trans. A Cta 111005100001 BANBOGOTA', 'tx' =>'TRASC', 'company_id'=>1, 'reference' =>'NC Trans. A ', 'sign' =>'D'),
        array('description' =>'NC Trans. Recib. Cta 111005100001 BANBOG', 'tx' =>'TRASC', 'company_id'=>1, 'reference' =>'NC Trans. Re', 'sign' =>'D'),
        array('description' =>'ND IMPUESTO FINANCIERO 4X10', 'tx' =>'DGMF', 'company_id'=>1, 'reference' =>'ND IMPUESTO ', 'sign' =>'D'),
        array('description' =>'ND Trans. A Cta 111005100001 BANBOGOTA', 'tx' =>'TRASC', 'company_id'=>1, 'reference' =>'ND Trans. A ', 'sign' =>'D'),
        array('description' =>'ND Trans. Recib. Cta 111005100001 BANBOG', 'tx' =>'NCC', 'company_id'=>1, 'reference' =>'ND Trans. Re', 'sign' =>'C'),
        array('description' =>'ND Trans. Recib. Cta 111005100002 BCOLOM', 'tx' =>'NCC', 'company_id'=>1, 'reference' =>'ND Trans. Re', 'sign' =>'C'),
        array('description' =>'ND Trans. Recib. Cta 111005100010 BANBOG', 'tx' =>'TRASC', 'company_id'=>1, 'reference' =>'ND Trans. Re', 'sign' =>'D'),
        array('description' =>'PG CUOTA CRED 6121060481', 'tx' =>'EGRES', 'company_id'=>1, 'reference' =>'PG CUOTA CRE', 'sign' =>'C'),
        array('description' =>'PG TOTAL CRED 193080024750', 'tx' =>'EGRES', 'company_id'=>1, 'reference' =>'PG TOTAL CRE', 'sign' =>'C'),
        array('description' =>'RECAUDO BANCOS', 'tx' =>'DEPOS', 'company_id'=>1, 'reference' =>'RECAUDO BANC', 'sign' =>'D'),
        array('description' =>'Registro sobregiro al cierre de Abr/2016', 'tx' =>'DINSC', 'company_id'=>1, 'reference' =>'Registro sob', 'sign' =>'D'),
        array('description' =>'Retiro en Cheque', 'tx' =>'CHC', 'company_id'=>1, 'reference' =>'Retiro en Ch', 'sign' =>'C'),
        array('description' =>'Rev.Comp. No lote n° 1042 NC Trans. A Ct', 'tx' =>'NCC', 'company_id'=>1, 'reference' =>'Rev.Comp. No', 'sign' =>'C'),
        array('description' =>'Rev.Comp. No lote n° 1042 ND Trans. Reci', 'tx' =>'DEVC', 'company_id'=>1, 'reference' =>'Rev.Comp. No', 'sign' =>'D'),
        array('description' =>'SERVICIO CELULAR MES MARZO/16 SOPORTE FE', 'tx' =>'EGRES', 'company_id'=>1, 'reference' =>'SERVICIO CEL', 'sign' =>'C'),
        array('description' =>'SERVICIO DE ACUEDUCTO ALCANTARILLADO Y A', 'tx' =>'EGRES', 'company_id'=>1, 'reference' =>'SERVICIO DE ', 'sign' =>'C'),
        array('description' =>'Sobrante envio brinks marzo 10', 'tx' =>'NDC', 'company_id'=>1, 'reference' =>'Sobrante env', 'sign' =>'D'),

            );

        DB::table('local_tx_types')->insert($local_tx);

    }
}
