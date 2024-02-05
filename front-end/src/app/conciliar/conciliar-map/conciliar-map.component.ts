import { Component, OnInit, ViewChild } from '@angular/core';
import { NgbTabset, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ApiRequestService } from '../../services/api-request.service';
import { environment } from 'src/environments/environment';
import { NgForm } from '@angular/forms';
import Swal from 'sweetalert2';
import { MapFileModel } from "../../models/mapFile.model";
import { HttpParams } from '@angular/common/http';
import { AuthServiceService } from 'src/app/services/auth-service.service';


@Component({
  selector: 'app-conciliar-map',
  templateUrl: './conciliar-map.component.html',
  styleUrls: ['./conciliar-map.component.css']
})
export class ConciliarMapComponent implements OnInit {
  
  @ViewChild('tabSet')
  private tabSet:NgbTabset;
  
  @ViewChild('content3')
  private modalHtml:any;
  
  isAdmin = false;
  updateDisabled = true;
  showBanc = false;
  showBancUpdate = false;
  
  bancsList = [];

  mapFilesList:any;
  newMapFile = new MapFileModel();
  updateMapFile = new MapFileModel();

  
  modalInfo = {
    'title':'Titulo Modal',
    'body':'body'
  };
  
  previewMap = new MapFileModel();
  
  
  url = environment.url;
  constructor( private apiRequest:ApiRequestService, private modalService: NgbModal,  private auth:AuthServiceService ) { 
      
    this.auth.isAdmin()
      .subscribe( (response)=>{
        this.isAdmin = response;
      }, (err)=>{
        
        this.isAdmin = false;
        console.error(err);

      });
  }
  

  ngOnInit() {
    
    this.getBanc();
    this.getMapFiles();

  }
  
  onChangeType(){
    
    this.showBanc = this.newMapFile.type=='conciliar_externo'?true:false;
    this.showBancUpdate = this.updateMapFile.type=='conciliar_externo'?true:false;
    this.newMapFile.fileRows = [];
    this.getMapIndex(this.newMapFile.type);
    this.getMapIndex(this.updateMapFile.type);
    
    

  }
  
  preview( format:any ){
    
    this.previewMap.setUploadValues(format);

    
    this.modalInfo.title = 'Asociación de Campos (Mapeo)'
    this.modalService.open( this.modalHtml, { centered: true, size: 'lg' });


  }

  onFileChange( file: FileList ){
    this.newMapFile.fileToUpload = file.item(0);
    
  }
  
  getMapFiles(){

    this.apiRequest.getCollection(`mapFiles`)
      .subscribe( (response)=>{
        this.mapFilesList = response;
      })
  }

  getMapIndex(type){
    
    if(type == ""){

      return;
    }
    this.apiRequest.getCollection(`mapFiles/getMapIndex/${type}`)
      .subscribe( (response)=>{
        
        this.newMapFile.masterFields = response;
        this.updateMapFile.masterFields = response;
        
        for(let i = 0; i < this.updateMapFile.mappedFields.length; i++){
          
          this.updateMapFile.mappedFields[i]['mapIndex'] = 0;
          
        }
      }, (err)=>{

        console.error(err);
      })
  }

  getBanc(){

    this.apiRequest.getCollection(`banks`)
      .subscribe( (response:any) => {
        
        this.bancsList = response;
        
      });
  }
  
  formMap( form:NgForm){
    
    if(form.invalid){

      Swal.fire({
        type: 'error',
        text: 'Todos los campos son obligatorios',
         
      });
      return;
    }
    
    
    let formData = new FormData();
    formData.append('description',this.newMapFile.description);
    formData.append('type',this.newMapFile.type);
    formData.append('bank_id',this.newMapFile.bank_id);
    formData.append('file', this.newMapFile.fileToUpload);
    formData.append('type', this.newMapFile.type);

    this.apiRequest.uploadFile(formData,'mapFiles/upload')
      .subscribe( (response)=>{
        

        this.getMapIndex(this.newMapFile.type);
        let tmpArray = [];
        for(let i = 0; i < response[1].length; i++){

          tmpArray.push(0);
        }
        
        this.newMapFile.mappedFields = tmpArray;
        this.newMapFile.fileRows = response;
        this.newMapFile.fileRowsHeader = response[0];
        this.newMapFile.fileRowsFields = response[1];
        
      }, (err) =>{
  
        console.error(err.error);
  
      })
  }

  mapFunction( form:NgForm ){
    
    this.newMapFile.mapStruct = [];

    for(let i = 0; i < this.newMapFile.masterFields.length; i++){
      if(this.newMapFile.masterFields[i].type == '1'){
        let exist = false;
        for( let j = 0; j < this.newMapFile.mappedFields.length; j++){
          if(this.newMapFile.mappedFields[j] == this.newMapFile.masterFields[i].id){
            
            exist = true;
          }
        }
        if(!exist){
          
          Swal.fire({
            type: 'error',
            text: `El campo ${this.newMapFile.masterFields[i].description} es Obligatorio`
          });
          
          return;
        } 
      }
    }
    
    if(this.newMapFile.type == 'conciliar_externo' && this.newMapFile.bank_id == ""){
      
      Swal.fire({
        type: 'error',
        text: `Debe seleccionar un banco`
      });
      return;
    }

    if(this.newMapFile.description == ""){
      Swal.fire({
        type: 'error',
        text: `Debe Agregar una descripción`
      });
      return;
    }
    
    let tmpChkArray = [];
    
    for(let i = 0; i < this.newMapFile.mappedFields.length; i++){
      
      let description = '';
      
      for(let j = 0; j < this.newMapFile.masterFields.length; j++){
        
        if(this.newMapFile.masterFields[j].id == this.newMapFile.mappedFields[i]){
          
          description = this.newMapFile.masterFields[j].description;
          break;
        }
      }

      if(this.newMapFile.mappedFields[i] != 0){
        
        
        if(tmpChkArray.includes(this.newMapFile.mappedFields[i])){
          
          
          Swal.fire({
            type: 'error',
            text: `El campo ${description} esta asiganado más de una vez`
          });
          this.newMapFile.mapStruct = [];
          return;
        }
        tmpChkArray.push(this.newMapFile.mappedFields[i]);
      }
      
      
      this.newMapFile.mapStruct.push(
          {
            'fileColumn':i, 
            'mapIndex':this.newMapFile.mappedFields[i],
            'value':description,
            'header':this.newMapFile.fileRows[0][i],
          }
        );  
    }


    let formData = new FormData();
    formData.append('description',this.newMapFile.description);
    formData.append('bank_id',this.newMapFile.bank_id);
    formData.append('type',this.newMapFile.type);
    formData.append('map',JSON.stringify(this.newMapFile.mapStruct));
    formData.append('base',JSON.stringify(this.newMapFile.fileRows));
    
    this.apiRequest.postForm( formData, 'mapFiles/saveMap').
      subscribe( (response)=>{
          
          this.tabSet.activeId = 'List';
          Swal.fire({
            type: 'success',
            text: `Mapeo guardado`
          });
      
          this.newMapFile = new MapFileModel();
          this.getMapFiles();
      }, (err)=>{
          
 

          Swal.fire({
            type: 'error',
            text: JSON.stringify(err)
          });
          
        console.error(err);
      }) 
  }
  
  cancel(){
    this.tabSet.activeId = 'List';
  }

  hasDuplicates(array) {
    return (new Set(array)).size !== array.length;
  }
  
  editMapFunction(){
      
    this.updateMapFile.mapStruct = [];
    this.updateMapFile.mappedFields.forEach( element => {
      const found = this.updateMapFile.masterFields
        .find( master => master.id == element.mapIndex);
      element.value = found ? found.description : '';
        
    });
    for(let i = 0; i < this.updateMapFile.masterFields.length; i++){
      if(this.updateMapFile.masterFields[i].type == '1'){
        let exist = false;
        
        for( let j = 0; j < this.updateMapFile.mappedFields.length; j++){

          if(this.updateMapFile.mappedFields[j]['mapIndex'] == this.updateMapFile.masterFields[i].id){
            
            exist = true;
          }
        }
        if(!exist){
          
          Swal.fire({
            type: 'error',
            text: `El campo ${this.updateMapFile.masterFields[i].description} es Obligatorio`
          });
          
          return;
        } 
      }
    }
    
    if(this.updateMapFile.type == 'conciliar_externo' && this.updateMapFile.bank_id == ""){
      
      Swal.fire({
        type: 'error',
        text: `Debe seleccionar un banco`
      });
      return;
    }

    if(this.updateMapFile.description == ""){
      Swal.fire({
        type: 'error',
        text: `Debe Agregar una descripción`
      });
      return;
    }
    
    let tmpChkArray = [];
    
    for(let i = 0; i < this.updateMapFile.mappedFields.length; i++){

      let description = '';

      for(let j = 0; j < this.updateMapFile.masterFields.length; j++){        
        if(this.updateMapFile.masterFields[j].id == this.updateMapFile.mappedFields[i]['mapIndex']){

          description = this.updateMapFile.masterFields[j].description;
          break;
        }
      }

      if(this.updateMapFile.mappedFields[i]['mapIndex'] != 0){
        
        if(tmpChkArray.includes(this.updateMapFile.mappedFields[i]['mapIndex'])){

          for(let k = 0; k < this.updateMapFile.masterFields.length; k++){

            if(this.updateMapFile.masterFields[k].id == this.updateMapFile.mappedFields[i]['mapIndex']){

              Swal.fire({
                type: 'error',
                text: `El campo ${description} esta asiganado más de una vez`
              });
              this.updateMapFile.mapStruct = [];
              return;
            }
          }
          
        }
        tmpChkArray.push(this.updateMapFile.mappedFields[i]['mapIndex']);
      }
      this.updateMapFile.mapStruct.push(
        {
          'fileColumn':i, 
          'mapIndex':this.updateMapFile.masterFields[i].id,
          'value':description,
          'header':this.updateMapFile.fileRows[0][i],
        }
      );  
      
    }
    
    let body = new HttpParams()
    .set('description', this.updateMapFile.description)
    .set('bank_id', this.updateMapFile.bank_id)
    .set('type', this.updateMapFile.type)
    .set('map', JSON.stringify(this.updateMapFile.mappedFields))
    .set('base', JSON.stringify(this.updateMapFile.fileRows))
    
    this.apiRequest.put(body,`mapFiles/${this.updateMapFile.id}`)
      .subscribe( (response)=>{
        
        this.tabSet.activeId = 'List';
        Swal.fire({
          type: 'success',
          text: `Mapeo guardado`
        });
    
        this.updateMapFile = new MapFileModel();
        this.getMapFiles();
        this.tabSet.activeId = 'List';
        
      }, (err)=>{
        
        this.updateMapFile = new MapFileModel();
        Swal.fire({
          type: 'error',
          text: JSON.stringify(err)
        });
        this.tabSet.activeId = 'List';
        console.error(err);
      })

  }
  editMap( mapFile:any ){
    
    this.tabSet.activeId = 'updateTab';
    
    this.updateMapFile.setUploadValues(mapFile);
    this.getMapIndex(this.updateMapFile.type);
    
    this.showBancUpdate = this.updateMapFile.type=='conciliar_externo'?true:false;

  }
}
