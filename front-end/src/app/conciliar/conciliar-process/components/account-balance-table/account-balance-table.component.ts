import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { ToastrService } from 'ngx-toastr';
import { InputCurrencyComponent } from "src/app/shared/components/input-currency/input-currency.component";

import { AccountModel } from 'src/app/models/account.model';
import { ApiRequestService } from 'src/app/services/api-request.service';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-account-balance-table',
  templateUrl: './account-balance-table.component.html',
  styleUrls: ['./account-balance-table.component.css']
})
export class AccountBalanceTableComponent implements OnInit {

  @Input() account: AccountModel;
  @Output() updateInfo = new EventEmitter<boolean>();

  bankValue: number = 0;
  localValue: number = 0;
  bankDifference: number = 0;
  localDifference: number = 0;

  currencyChars = new RegExp('[\$,]', 'g'); // we're going to remove commas and dots
  
  constructor(private apiRequest: ApiRequestService, private toastr: ToastrService ) { }

  ngOnInit() {
    this.updateBankDifference();
    this.updateLocalDifference();
  }


  uploadAccountFile(file: File[]){

    const formData = this.account.toFormData();
    formData.append('file', file[0]);

    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });
    this.apiRequest.uploadFile(formData, 'conciliar/uploadAccountFile')
      .subscribe((response) => {
        Swal.close();
        this.toastr.success('Archivo cargado', 'Success!');
        this.updateInfo.emit(true);

      }, (err) => {
        Swal.close();
        Swal.fire(
          'Error',
          err.error.errors.join(),
          'warning'
        )

        console.error(err);
      })
  }

  updateBankDifference(){
    this.bankValue = Number(this.bankValue.toString().replace(this.currencyChars, ''));
    console.log(this.account.conciliarInfo);
    const calcValue = Number(this.account.conciliarInfo.antExterno) + Number(this.account.conciliarInfo.creditExternal) - Number(this.account.conciliarInfo.debitExternal);
    console.log(calcValue);
    this.bankDifference = calcValue - Number(this.bankValue);

  }

  updateLocalDifference(){
    this.localValue = Number(this.localValue.toString().replace(this.currencyChars, ''));
    const calcValue = Number(this.account.conciliarInfo.antLocal) + Number(this.account.conciliarInfo.debitLocal) - Number(this.account.conciliarInfo.creditLocal);
    this.localDifference = calcValue - this.localValue;
  }

  // ACTIONS
  closeAccount(){
    if( this.bankDifference !== 0 ){
      Swal.fire("Error", "La diferencia en bancos debe ser 0", "error");
      return;
    }
    if( this.localDifference !== 0 ){
      Swal.fire("Error", "La diferencia en libros debe ser 0", "error");
      return;
    }
  }
}
