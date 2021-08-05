import { Component, OnInit, ViewChild, ɵConsole } from '@angular/core';
import { ExternalTxTypeModel } from 'src/app/models/externalTxType.model';
import { ApiRequestService } from 'src/app/services/api-request.service';
import { NgbTabset } from '@ng-bootstrap/ng-bootstrap';
import { NgForm } from '@angular/forms';
import Swal from 'sweetalert2';
import { HttpParams } from '@angular/common/http';
import { LocalTxTypeModel } from 'src/app/models/localTxType.model';
import { AuthServiceService } from 'src/app/services/auth-service.service';

@Component({
  selector: 'app-tx-types',
  templateUrl: './tx-types.component.html',
  styleUrls: ['./tx-types.component.css']
})
export class TxTypesComponent implements OnInit {
  
  @ViewChild('tabSet')
  private tabSet:NgbTabset;

  localSelected:string;
  showLocal = false;
  showExternal = false;
  showEditLoca = false;

  isAdmin = false;
  updateDisabled = true;
  banksList = [];
  externalTxTypesList = [];
  localTxTypesList = [];
  externalTxTypeEdit = new ExternalTxTypeModel();
  externalTxTypeNew = new ExternalTxTypeModel();
  localTxTypeNew = new LocalTxTypeModel();
  localTxTypeEdit = new LocalTxTypeModel();
  
  constructor( private apiRequest:ApiRequestService, private auth:AuthServiceService) { 

    this.auth.isAdmin()
      .subscribe( (response)=>{

        console.log(response);
        this.isAdmin = response;
      }, (err)=>{
        
        this.isAdmin = false;
        console.log(err);

      });
  }

  ngOnInit() {

    this.getExternalTxTypes();
    this.getLocalTxTypes();
    this.getbank();
    
  }
   
  changeTypeSelected(){
    
    switch(this.localSelected){

      case 'local':
        
        this.showExternal = false;  
        this.showLocal = true;
      break;

      case 'externo':
        
        this.showLocal = false;
        this.showExternal = true;
      break;

      default:
          this.showLocal = false;
          this.showExternal = false;
      break;
    }

  }

  getLocalTxTypes(){
    
    this.apiRequest.getCollection('localTxType')
      .subscribe( (response)=>{
        
        
        this.localTxTypesList = response;  
        

      }, (err)=>{

        console.log(err);
      })
  }

  getExternalTxTypes(){
    
    this.apiRequest.getCollection('externalTxType')
      .subscribe( (response)=>{
        this.externalTxTypesList = response;  

      }, (err)=>{

        console.log(err);
      })

  }
  editLocalTx( txType:any ){
    
    this.localTxTypeEdit.setValues(txType);
    this.tabSet.activeId = 'updateTab';
    this.showEditLoca = true;

  }

  editExternalTx( txType:any ){
    
    this.externalTxTypeEdit.setValues(txType);
    this.tabSet.activeId = 'updateTab';
    
    this.showEditLoca = false;

  }
  storeLocalTxType( form:NgForm ){
    
    if(form.invalid){
      
      Swal.fire({
        type: 'error',
        text: 'Todos los campos son obligatorios',
         
      });
      return;
    }

    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: "assets/images/2.gif",
       
    });

    let formData = this.localTxTypeNew.toFormData();

    this.apiRequest.store(formData, `localTxType`)
      .subscribe( (response)=>{
        
        Swal.close();
        this.getLocalTxTypes();
        this.tabSet.activeId = 'localList';
        this.localTxTypeNew = new LocalTxTypeModel();
        
      }, (err)=>{

        console.log(err);
        Swal.fire({
          title: 'Procesando',
          imageUrl: "assets/images/2.gif",
           
        });
      })
    
  }
  
  storeExternalTxType( form:NgForm ){
    
    if(form.invalid){
      
      Swal.fire({
        type: 'error',
        text: 'Todos los campos son obligatorios',
         
      });
      return;
    }

    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: "assets/images/2.gif",
       
    });

    let formData = this.externalTxTypeNew.toFormData();

    this.apiRequest.store(formData, `externalTxType`)
      .subscribe( (response)=>{
        
        
        Swal.close();
        this.getExternalTxTypes();
        this.tabSet.activeId = 'List';
        this.externalTxTypeNew = new ExternalTxTypeModel();
        
      }, (err)=>{

        console.log(err);
        Swal.fire({
          title: 'Procesando',
          imageUrl: "assets/images/2.gif",
           
        });
      })
    
  }

  updateExternal(form:NgForm ){
    
    if(form.invalid){
      
      Swal.fire({
        type: 'error',
        text: 'Todos los campos son obligatorios',
         
      });
      return;
    }
    
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: "assets/images/2.gif",
       
    });
    

    let body = this.externalTxTypeEdit.toParams();

    this.apiRequest.put( body, `externalTxType/${this.externalTxTypeEdit.id}`)
    .subscribe( (response)=>{
      
      
      Swal.fire({
        type: 'success',
        text: 'Actulización exitosa'
      });
      
      this.getExternalTxTypes();
      this.externalTxTypeEdit = new ExternalTxTypeModel();
      this.tabSet.activeId = 'externalList';
    }, (err)=>{
      
      console.log(err);
      Swal.close();
      Swal.fire({
        title: 'Procesando',
        imageUrl: "assets/images/2.gif",
         
      });
    })
  }
  
  updateLocal(form:NgForm ){
    
    if(form.invalid){
      
      Swal.fire({
        type: 'error',
        text: 'Todos los campos son obligatorios',
         
      });
      return;
    }
    
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: "assets/images/2.gif",
       
    });
    
    let body = new HttpParams()
      .set('description',this.localTxTypeEdit.description)
      .set('tx',this.localTxTypeEdit.tx)
      .set('company_id', String(this.localTxTypeEdit.company_id))
      .set('reference',this.localTxTypeEdit.reference)
      .set('sign',this.localTxTypeEdit.sign);

    this.apiRequest.put( body, `localTxType/${this.localTxTypeEdit.id}`)
    .subscribe( (response)=>{
      
      Swal.fire({
        type: 'success',
        text: 'Actulización exitosa'
      });
      
      this.getLocalTxTypes();
      this.localTxTypeEdit = new LocalTxTypeModel();
      this.tabSet.activeId = 'localList';
    }, (err)=>{
      
      console.log(err);
      Swal.close();
      Swal.fire({
        title: 'Procesando',
        imageUrl: "assets/images/2.gif",
         
      });
    })
  }

  getbank(){

    this.apiRequest.getCollection(`banks`)
      .subscribe( (response:any) => {
        
        this.banksList = response;        
        
      });
  }

  cancel(){
    
    this.externalTxTypeEdit = new ExternalTxTypeModel();
    this.externalTxTypeNew = new ExternalTxTypeModel();
    this.tabSet.activeId = 'externalList';
  }
}
