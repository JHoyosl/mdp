import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import {MatPaginator, MatTableDataSource} from '@angular/material';
import { ToastrService } from 'ngx-toastr';
import { AccountingHeader } from 'src/app/Interfaces/accounting.interface';
import { AccountingService } from 'src/app/services/accounting.service';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-accounting-info',
  templateUrl: './accounting-info.component.html',
  styleUrls: ['./accounting-info.component.css']
})
export class AccountingInfoComponent implements OnInit {

  @Output() selectedAccountingHeader = new EventEmitter<AccountingHeader>();

  public accountingHeaders: AccountingHeader[];

  constructor(
    private accountingService: AccountingService, 
    private toastr: ToastrService) { }

  ngOnInit() {
    this.getAccountingHeaders();
  }

  getAccountingHeaders(): void {

    this.accountingService.index().subscribe(
      (response) => {
        this.accountingHeaders = response;
    
      }
    );
  }

  headerDetail(accountingHeader: AccountingHeader): void {
    this.selectedAccountingHeader.emit(accountingHeader);
  }

  removeValidation(accountingHeader: AccountingHeader): void{
    Swal.fire({
      title: 'Confirmación',
      text: `¿Desea eliminar el cargue para las fechas ${accountingHeader.startDate} - ${accountingHeader.endDate}?`,
      showConfirmButton: true,
      confirmButtonAriaLabel: 'Eliminar',
      confirmButtonText: 'Eliminar',
      confirmButtonColor: '#d33',
      showCancelButton: true,
      cancelButtonAriaLabel: 'Cancelar',
      cancelButtonText: 'Cancelar',
      cancelButtonColor: '#3085d6'
    }).then((result) => {
      if(result.value){
        this.removeHeader(accountingHeader);
      }
    });
  }

  removeHeader(accountingHeader: AccountingHeader): void{

    this.accountingService.deleteLastHeader(accountingHeader)
      .subscribe(
        () => {
          this.toastr.success('Mensaje', `Cargue eliminado`);
          this.getAccountingHeaders();
        },
        (err) => {
          console.error(err);
        }
      );
  }
}
