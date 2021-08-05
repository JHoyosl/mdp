
export class BankModel{
    
    id: string;
    cod_comp: string;
    name: string;
    nit: string;
    currency: string;
    portal: string;

    constructor(){
        
     
    }
    
    setValues( Object: any) {
        

        this.id = Object.id?Object.id:null;
        this.cod_comp = Object.cod_comp;
        this.name = Object.name;
        this.nit = Object.nit;
        this.currency = Object.currency;
        this.portal = Object.portal;
       
    }
}    