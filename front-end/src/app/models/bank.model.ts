import { Bank } from '../Interfaces/bank.interface';

export class BankModel{
    
    id: string;
    cod_comp: string;
    name: string;
    nit: string;
    currency: string;
    portal: string;

    constructor(){
        
     
    }
    
    static toInterface( obj: any ): Bank{
        return {
            'id': obj.id,
            'codComp': obj.cod_comp,
            'name': obj.name,
            'nit': obj.nit,
            'currency': obj.currency,
            'portal': obj.portal,
            'deletedAt': obj.deleted_at,
            'createdAt': obj.created_at,
            'updatedAt': obj.updated_at,
        }
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