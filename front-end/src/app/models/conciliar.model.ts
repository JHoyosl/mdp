

export class ConciliarModel{

    id: string;
    balanceExternal = 0;
    balanceLocal = 0;
    creditExternal = 0;
    creditLocal = 0;
    debitExternal = 0;
    debitLocal = 0;
    antExterno = 0;
    antLocal = 0;
    total =  0;
    saldoExtracto = 0;
    saldoContable = 0;
    cuadre = 0;
    local_account = '';
    external_account = '';
    bank_name = '';
    isDirt = false;

    constructor() {
    }

    getTotal() {

        this.total = Number(this.saldoExtracto) + (Number(this.debitExternal) +
                        Number(this.debitLocal)) - (Number(this.creditExternal) +
                        Number(this.creditLocal));
        this.total.toFixed(2);
        this.isDirt = true;

        this.getCuadre();
    }

    getCuadre(){

        this.cuadre = Number(this.total) - this.saldoContable;


    }

    setValues( Object: any) {

        this.id = Object.id;
        this.balanceExternal = Object.balance_externo;
        this.balanceLocal = Object.balance_local;
        this.creditLocal = Object.credit_local;
        this.creditExternal = Object.credit_externo;
        this.debitExternal = Object.debit_externo;
        this.debitLocal = Object.debit_local;
        this.antExterno = Object.ant_externo;
        this.antLocal = Object.ant_local;
    }
}