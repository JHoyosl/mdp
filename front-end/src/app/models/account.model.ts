import { Account } from '../Interfaces/account.interface';
import { BankModel } from './bank.model';
import { CompanyModel } from './company.model';
import { ConciliarModel } from './conciliar.model';

export class AccountModel {

    id: string;
    bank_id: string;
    company_id: string;
    acc_type: string;
    bank_account: string;
    local_account: string;
    banks?: BankModel;
    companies?: CompanyModel;
    map_id?: string;
    conciliarInfo?: ConciliarModel;

    constructor() {


    }

    toFormData(): FormData {

        const formData = new FormData();
        formData.append('account_id', this.id);
        formData.append('bank_id', this.bank_id);
        formData.append('company_id', this.company_id);
        formData.append('acc_type', this.acc_type);
        formData.append('bank_account', this.bank_account);
        formData.append('local_account', this.local_account);

        return formData;
    }

    setValues( Object: any) {

        this.id = Object.id ? Object.id : null;
        this.bank_id = Object.bank_id;
        this.company_id = Object.company_id;
        this.acc_type = Object.acc_type;
        this.bank_account = Object.bank_account;
        this.local_account = Object.local_account;
        this.banks = Object['banks'];
        this.companies = Object['companies'] == null ? new CompanyModel() : Object['companies'];
        this.map_id = Object['map_id'];

    }

    static accountToInterface(obj: any): Account {
        return {
            id: obj.id,
            bankId: obj.bank_id,
            companyId: obj.company_id,
            accType: obj.acc_type,
            bankAccount: obj.bank_account,
            localAccount: obj.local_account,
            mapId: obj.map_id,
            deletedAt: obj.deleted_at,
            createdAt: obj.created_at,
            updatedAt: obj.updated_at,
            banks: obj.banks,
        }
    }
}
