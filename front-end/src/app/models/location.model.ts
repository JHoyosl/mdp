
export class LocationModel{
    
    id: string;
    country_id: string;
    state_id: string;
    city_id: string;
    
    constructor(){

        this.country_id = '47';
        this.state_id = 'Selección...';
    }
    
    setValues( Object: any) {
            
        this.id = Object.id;
        this.country_id = Object.country_id;
        this.state_id = Object.state_id;
        this.city_id = Object.city_id;
    }
}
