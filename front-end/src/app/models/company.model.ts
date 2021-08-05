
export class CompanyModel{
    
    id: string;
    name: string;
    nit: string;
    phone: string;
    sector: string;
    address: string;
    location_id: string;
    country_id: string;
    state_id: string;
    city_id: string;
    map_id: string;
    
    constructor(){
        
        this.country_id = '47';
       
    }
    
    setValues( Object: any) {
    
    
        this.id = Object.id;
        this.name = Object.name;
        this.nit = Object.nit;
        this.phone = Object.phone;
        this.sector = Object.sector;
        this.address = Object.address;
        this.map_id = Object.map_id;
        this.location_id = Object.location_id;
        this.country_id = Object.locations[0].country_id;
        this.state_id = Object.locations[0].state_id;
        this.city_id = Object.locations[0].city_id;
    }
}    