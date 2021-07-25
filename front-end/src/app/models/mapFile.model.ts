export class MapFileModel {
    id: number;
    bank_id: string;
    banks = Array();
    users = Array();
    company_id: number;
    created_by: number;
    description: string;
    header: string;
    type: string = "";
    fileToUpload: File;
    fileRows = Array();
    masterFields = Array();
    mappedFields= Array();
    fileRowsHeader= Array();
    fileRowsFields= Array();
    mapStruct = Array();
    
    
    setUploadValues( Object:any){
        
        console.log(Object);
        this.id = Object.id;
        this.bank_id = Object.bank_id;
        this.banks = Object.banks;
        this.users = Object.users;
        this.users = Object.users;
        this.company_id = Object.company_id;
        this.description = Object.description;
        this.header = Object.header;
        this.type = Object.type;
        this.mappedFields = JSON.parse(Object.map);
        this.fileRows = JSON.parse(Object.base);
        

    }

}

