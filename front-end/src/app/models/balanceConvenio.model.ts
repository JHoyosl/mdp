

export class BalanceConvenioModel{
    
    balanceHeader:any = [];
    balanceItems:any = [];
    convenioHeader:any = [];
    convenioItems:any = [];
    indices:any = [];
    nautralezaOperativa:any = [];
    nautralezaContable:any = [];
    
    constructor(){
        
     
    }


    setValues( Object: any) {
        
        console.log(Object);
        this.balanceHeader = Object.balance.header;
        this.balanceItems = Object.balance.items;
        this.convenioHeader = Object.convenios.header;
        this.convenioItems = Object.convenios.items;
        this.indices = Object.cuentasArray;
        this.nautralezaContable = Object.nautralezaContable;
        this.nautralezaOperativa = Object.nautralezaOperativa;
        
        let tmpArray:any = [];
        console.log(Object);
        for(let i = 0; i < this.convenioItems.length; i++){
            let found = false;
            for(let j = 0; j < this.indices.length; j++){
                //se busca que el indice esté en los convenios del archivo
                //En caso que no esté se agrega al arreglo de indices
                if(this.convenioItems[i]['numcon'] == this.indices[j]['linea']){
                    
                    this.indices[j]['operativo'] = this.convenioItems[i]['sum_salcuo'];
                    found = true;
                    break;
                }
                
            }
            
            if(!found){

                tmpArray.push(
                       {'linea':this.convenioItems[i]['numcon'],
                        'nombre':'',
                        'cuenta':''}
                    )
            }

        }
        
        // tmpArray.forEach(convenio => {
            
        //     this.indices.push(convenio);
        // });
        
        this.indices.forEach((indice,index) => {
            
            for (let i = 0; i < this.balanceItems.length; i++){

                if(this.indices[index]['cuenta'] == this.balanceItems[i]['cuenta']){

                    this.indices[index]['contable'] = this.balanceItems[i]['saldo_actual'];
                    this.indices[index]['diferencia'] = this.indices[index]['operativo'] - this.balanceItems[i]['saldo_actual'];
                }
            }
        });


    }
}    