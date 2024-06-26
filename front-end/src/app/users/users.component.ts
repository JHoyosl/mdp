import { Component, OnInit, ViewChild } from '@angular/core';
import { ApiRequestService } from '../services/api-request.service';
import { UserModel } from '../models/user.model';
import { NgForm } from '@angular/forms';
import Swal from 'sweetalert2';
import { NgbTabset } from '@ng-bootstrap/ng-bootstrap';
import { HttpParams } from '@angular/common/http';

@Component({
  selector: 'app-users',
  templateUrl: './users.component.html',
  styleUrls: ['./users.component.css']
})
export class UsersComponent implements OnInit {
  
  @ViewChild('tabSet')
  private tabSet:NgbTabset;
  
  usersList = [];
  companiesSelected = [];
  CompaniesList = [];
  usersCompanyAdded = [];
  addCompanyList = [];
  addCompanyUser = new UserModel();
  updateDisabled = true;
  newUser = new UserModel();
  updateUser = new UserModel();

  constructor( private apiRequest:ApiRequestService) { }

  ngOnInit() {

    this.getUserList();
    this.getCompaniesList();
  }
  

  editUser(userForm:any){
    
    this.updateUser.setValues(userForm);
    this.tabSet.activeId = 'updateTab';
  }

  cancelTab(){

    this.tabSet.select('List');
  }

  save( form:NgForm){
    const validEmailRegEx = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    
    if(!validEmailRegEx.test(this.newUser.email)){
      Swal.fire({
          type: 'error',
          text: 'Debe agregar un email válido',
        });
        return;
    }
   
    if( form.invalid ){
      
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
   

    this.apiRequest.storeUser( this.newUser )
      .subscribe( (response) =>{
    
        
        Swal.fire({
          type: 'success',
          text: 'Usuario Agregado',
        });
        this.getUserList();
        this.tabSet.activeId = 'List';
      }, (err)=>{
        
    
        let msnError = '';

        for (var k in err.error.errors) {
            if (err.error.errors.hasOwnProperty(k)) {
              msnError+= err.error.errors[k] = err.error.errors[k]+'\n';
            }
        }
        
        Swal.fire({
          type: 'error',
          text: msnError,
        });
      })
  }

  getCompaniesList(){

    this.apiRequest.getCollection('companies')
      .subscribe( (response) =>{
        
        this.CompaniesList = response;
        
      }, (err)=>{

        console.error(err);
      })
  }

  getUserList(){
    
    this.apiRequest.getCollection('companies/1/users')
      .subscribe( (response) =>{
        
        this.usersList = response;
      }, (err) =>{

        console.error(err);
      })

  }

  newToken(user){
    
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: "assets/images/2.gif",
       
    });
    this.apiRequest.newVerifyToken(user.id)
      .subscribe((response)=>{
        
    
        
      }, (err)=>{
        
    
        console.error(err);
      })
  }

  
  changeSelected(index){
    
    this.companiesSelected[index] = !this.companiesSelected[index];
    
  }

  update( form:NgForm){

    const validEmailRegEx = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    
    if(!validEmailRegEx.test(this.updateUser.email)){
      Swal.fire({
          type: 'error',
          text: 'Debe agregar un email válido',
        });
        return;
    }
   
    if( form.invalid ){
      
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


    const body = new HttpParams()
      .set('id',this.updateUser.id.toString())
      .set('email',this.updateUser.email)
      .set('names',this.updateUser.names)
      .set('type',this.updateUser.type)
      .set('last_names',this.updateUser.last_names)
      .set('current_company',this.updateUser.current_company);

    this.apiRequest.put( body, `users/${this.updateUser.id}` )
      .subscribe( (response) =>{
    
        
        Swal.fire({
          type: 'success',
          text: 'Usuario Agregado',
        });
        this.getUserList();
        this.tabSet.activeId = 'List';
      }, (err)=>{
        
        console.error(err);
    
        let msnError = '';

        for (var k in err.error.errors) {
            if (err.error.errors.hasOwnProperty(k)) {
              msnError+= err.error.errors[k] = err.error.errors[k]+'\n';
            }
        }
        
        Swal.fire({
          type: 'error',
          text: msnError,
        });
      })
  }


  saveAddCompany(){
    
    let updateArray = [];

    for(let i = 0; i < this.companiesSelected.length; i++){

      updateArray.push({'company':this.addCompanyList[i]['id'], 'selected':this.companiesSelected[i]})
    }
    
    let formData = new FormData();
    formData.set('info',JSON.stringify(updateArray));
    formData.set('id',this.addCompanyUser.id.toString());

    this.apiRequest.postForm( formData, `userCompany/companiesToUser`)
      .subscribe( (response)=>{
        
        Swal.fire({
          type: 'success',
          text: 'Asociación exitosa',
        });
        this.getUserList();
        this.tabSet.activeId = 'List';
        
      }, (err)=>{
        
        Swal.fire({
          type: 'error',
          text: err,
        });
        console.error(err);
      })

  }

  addCompany( user:any ){
    this.companiesSelected = [];
    
    this.addCompanyUser.setValues(user);
    this.tabSet.activeId = 'addCompany';
    
    this.apiRequest.getCollection(`usersCompanies/${user.id}`)
      .subscribe( (response)=>{
        this.usersCompanyAdded = response;
        this.apiRequest.getCollection(`usersCompanies`)
          .subscribe( (response)=>{

            for(let i = 0; i < response.length; i++){
              let check = false;
              for(let j = 0; j < this.usersCompanyAdded.length; j++){
                  
                check = this.usersCompanyAdded[j].id == response[i].id?true:false;
                if(check)
                  break;
              }
              this.companiesSelected.push(check);

              this.addCompanyList = response;
            }
            
          }, (err)=>{
    
            console.error(err);
          });
      }, (err)=>{

        console.error(err);
      });

    
    
      


  }

  
}
