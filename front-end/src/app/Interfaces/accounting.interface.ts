export interface AccountingHeader {
    'id': number;
    'companyId': number;
    'uploadedBy': number;
    'path': string;
    'fileName': string;
    'startDate': string;
    'endDate': string;
    'status': string;
    'createdAt': string;
    'updatedAt': string;
    'deletedAt': string
}

export interface AccountingHeaderResponse {
    data: AccountingHeader[];
    message: string;
    status: boolean
}

export interface AccountingUploadInfo {
    startDate: string,
    endDate: string, 
    file: File
}