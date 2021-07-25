import { BankModel } from "./bank.model";
import { HttpParams } from "@angular/common/http";

export class ExternalTxTypeModel {
    id: number;
    description: string;
    tx: string;
    bank_id: number;
    reference: string;
    type: string;
    sign: string;
    banks?: BankModel;

    setValues( Object: any) {
        this.id = Object.id;
        this.description = Object.description;
        this.tx = Object.tx;
        this.bank_id = Object.bank_id;
        this.reference = Object.reference;
        this.type = Object.type;
        this.sign = Object.sign;
        this.banks = Object.banks;
       
    }

    toFormData():FormData{

        let formData = new FormData();
        formData.set('id', String(this.id));
        formData.set('description',this.description);
        formData.set('bank_id',String(this.bank_id));
        formData.set('tx',this.tx);
        formData.set('reference',this.reference);
        formData.set('type',this.type);
        formData.set('sign',this.sign);
        
        
        return formData;
    }

    toParams():HttpParams{
        
        let body = new HttpParams()
          .set('description',this.description)
          .set('tx',this.tx)
          .set('bank_id', String(this.bank_id))
          .set('reference',this.reference)
          .set('type',this.type)
          .set('sign',this.sign);

      return body;
        
    }
}
