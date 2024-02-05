import { Component, OnInit, ViewChild, AfterViewChecked } from '@angular/core';

import { NgbTabChangeEvent, NgbTabset } from '@ng-bootstrap/ng-bootstrap';
import { CompanyModel } from '../models/company.model';
import { NgForm } from '@angular/forms';
import { ApiRequestService } from '../services/api-request.service';
import { HttpHeaders, HttpParams } from "@angular/common/http";
import Swal from 'sweetalert2';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-companies',
  templateUrl: './companies.component.html',
  styleUrls: ['./companies.component.css']
})
export class CompaniesComponent implements OnInit {
  


  @ViewChild('tabSet')
  private tabSet:NgbTabset;
  
  updateDisabled = true;
  company = new CompanyModel();
  companyUpdate: CompanyModel;
  companies: any[] = [];
  

  statesList: any[] = [];
  citiesList: any[] = [];

  constructor( private apiRequest: ApiRequestService,
    private toastr: ToastrService ) { 
    
    this.apiRequest.getStatesList()
        .subscribe( respone => { this.statesList = respone; });
    
      this.getCompanies();
    
      
  }

  ngOnInit() {
    
     
  }
  
  cancel(){

    this.tabSet.activeId = 'List';
    this.companyUpdate = new CompanyModel();
  }

  getCompanies(){

    this.apiRequest.getCollection('companies')
      .subscribe( (response:any) => {
        
        
        this.companies = response;
  
      });
  }
  editCompany(company: any ){
    
    
    this.companyUpdate = new CompanyModel();
    this.companyUpdate.setValues(company);
    console.log(this.companyUpdate);
    this.apiRequest.getCitiesList( parseInt(this.companyUpdate.state_id) )

        .subscribe( response =>{
          
          this.citiesList = response;
          this.companyUpdate.setValues(company);
          this.tabSet.activeId  = 'updateTab';
          
        })

  }

  stateChange( type: String ){
    
    
    let changeCompany = new CompanyModel();
    
    if(type == "update"){
      
      changeCompany = this.companyUpdate;
    }else{

      changeCompany = this.company;  
    }
    
    if(changeCompany.state_id != ''){
      
      let stateId = parseInt(changeCompany.state_id);
      this.apiRequest.getCitiesList( stateId )
        .subscribe( response =>{
          
          this.citiesList = response;
        })
    }
    
  }
  
  update(form: NgForm){
    
    if(form.invalid){
      Swal.fire({
        type: 'error',
        text: 'Todos los campos son obligatorios',
      });
      return;
    }
    
    const body = new HttpParams()
        .set('nit', this.companyUpdate.nit)
        .set('name', this.companyUpdate.name)
        .set('sector', this.companyUpdate.sector)
        .set('address', this.companyUpdate.address)
        .set('phone', this.companyUpdate.phone)
        
        .set('country_id', this.companyUpdate.country_id)
        .set('state_id', this.companyUpdate.state_id)
        .set('city_id', this.companyUpdate.city_id)
        .set('location_id', this.companyUpdate.location_id);
    
    

    this.apiRequest.put( body, `companies/${this.companyUpdate.id}`)
      .subscribe( (response)=>{

          // 
          this.toastr.success('Formato asociado', 'Correcto');
          this.getCompanies();
          this.tabSet.activeId  = 'List';
          
      }, (err)=>{
        
        this.toastr.error('Error', 'Correcto');
        this.tabSet.activeId  = 'List';
        
        console.error(err);
      })

      
    // this.apiRequest.putCompany(this.companyUpdate )
    //   .subscribe( response => {

    //     
    //     this.getCompanies();
    //     this.tabSet.activeId  = 'List';
    //     Swal.fire({
    //       type: 'success',
    //       text: 'Registro actualizado',
    //     });
    //   }, (err)=>{
    //     console.error(err.error);
        
    //   });
    

  }

  save( form: NgForm ){
    
    if(form.invalid){
      Swal.fire({
        type: 'error',
        text: 'Todos los campos son obligatorios',
      });
      return;
    }
    
    this.apiRequest.storeCompany( this.company )
      .subscribe( response => {

        this.tabSet.activeId = 'List';
        this.getCompanies();
        Swal.fire({
          type: 'success',
          text: `Empresa creada`
        });

      }, (err)=>{
        console.error(err.error.errors);
        
        if(err.error.errors){
          Swal.fire({
            type: 'error',
            text: err.error.errors,
          });
        }else{

          this.getCompanies();
        }
        
      });

  }

  currentJustify = 'start';

  currentOrientation = 'horizontal';
  public beforeChange($event: NgbTabChangeEvent) {
    if ($event.nextId === 'tab-preventchange2') {
      $event.preventDefault();
    }
  }

}
