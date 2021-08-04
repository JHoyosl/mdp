import { BankModel } from "./bank.model";

export class LocalTxTypeModel {
    id: number;
    description: string;
    tx: string;
    company_id: number;
    reference: string;
    sign: string;
    companies?: BankModel;


    setValues( Object: any) {
        this.id = Object.id;
        this.description = Object.description;
        this.tx = Object.tx;
        this.company_id = Object.company_id;
        this.reference = Object.reference;
        this.sign = Object.sign;
        this.companies = Object.companies;
       
    }

    toFormData():FormData{

        let formData = new FormData();
        formData.set('id', String(this.id));
        formData.set('description',this.description);
        formData.set('tx',this.tx);
        formData.set('company_id',String(this.company_id));
        formData.set('reference',this.reference);
        formData.set('sign',this.sign);
        
        
        return formData;
    }
}
