import { Component, OnInit, ViewChild } from '@angular/core';
import { BankModel } from '../models/bank.model';
import { ApiRequestService } from '../services/api-request.service';
import { NgbTabset } from '@ng-bootstrap/ng-bootstrap';
import { NgForm } from '@angular/forms';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-banks',
  templateUrl: './banks.component.html',
  styleUrls: ['./banks.component.css']
})
export class BanksComponent implements OnInit {
  
  updateDisabled = true;
  bankList = [];
  bank = new BankModel();
  bankUpdate = new BankModel();


  @ViewChild('tabSet')
  private tabSet:NgbTabset;

  constructor( private apiRequest:ApiRequestService) { }

  ngOnInit() {

    this.getbanks();
  }
  
  editBank(bank: any ){
    
    this.bankUpdate = new BankModel();
    this.bankUpdate.setValues(bank);
    console.log(this.bankUpdate);

    this.tabSet.activeId  = 'updateTab';

  }
  
  removeBank(bank){
    Swal.fire({
      title: 'Are you sure?',
      text: "It will permanently deleted !",
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, delete it!'
    }).then( ()=>{
        
      this.apiRequest.delete( bank.id )
        .subscribe((response)=>{
          
          console.log(response);
        },(err)=>{

          console.log(err);
        })
    })
    
  }

  update(form: NgForm){
    
    if(form.invalid){
      Swal.fire({
        type: 'error',
        text: 'Todos los campos son obligatorios',
      });
      return;
    }
    
    this.apiRequest.putbank( this.bankUpdate )
      .subscribe( response => {

        console.log(response);
        this.getbanks();
        this.tabSet.activeId  = 'List';
        Swal.fire({
          type: 'success',
          text: 'Registro actualizado',
        });
      }, (err)=>{
        console.log(err.error);
        
      });
    

  }

  getbanks(){

    this.apiRequest.getCollection('banks')
      .subscribe( (response:any) => {
        
        console.log(response);
        this.bankList = response;
        
      });
  }

 
  
  save( form: NgForm ){
    
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
    this.apiRequest.storebank( this.bank )
      .subscribe( response => {
        
        Swal.close();
        this.getbanks();
        this.tabSet.activeId = 'List';
        
      }, (err)=>{
        
        Swal.close();
        let errorMesage = "";
        for (var key in err.error.errors) {
          errorMesage+= `${err.error.errors[key]}, \n`; // "User john is #234"
        }
        if(errorMesage != ""){
          Swal.fire({
            type: 'error',
            text: errorMesage,
          });
        }        
      });

  }


}
