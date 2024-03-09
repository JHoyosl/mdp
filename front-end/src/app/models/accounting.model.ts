import { AccountingHeader } from "../Interfaces/accounting.interface";

export class AccountingModel {

    static toInterface(Object: any): AccountingHeader{
        return {
            'id': Object.id,
            'companyId': Object.company_id,
            'uploadedBy': Object.uploaded_by,
            'path': Object.path,
            'fileName': Object.file_name,
            'startDate': Object.start_date,
            'endDate': Object.end_date,
            'status': Object.status,
            'createdAt': Object.created_at,
            'updatedAt': Object.updated_at,
            'deletedAt': Object.deleted_at,
        };
    }
}

