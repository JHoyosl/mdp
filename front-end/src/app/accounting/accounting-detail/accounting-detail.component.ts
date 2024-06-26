import { HttpParams } from '@angular/common/http';
import { Component, Input, OnInit, ViewChild } from '@angular/core';
import { MatPaginator, MatTableDataSource } from "@angular/material";
import { AccountingHeader, AccountingItem } from 'src/app/Interfaces/accounting.interface';
import { AccountingService } from 'src/app/services/accounting.service';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-accounting-detail',
  templateUrl: './accounting-detail.component.html',
  styleUrls: ['./accounting-detail.component.css']
})
export class AccountingDetailComponent implements OnInit {

  @Input() accountingHeader: AccountingHeader;
  
  @ViewChild(MatPaginator) paginator: MatPaginator;
  
  dataSource = new MatTableDataSource<AccountingItem>();
  
  displayedColumns: string[] = [
      'fecha_movimiento', 
      'descripcion', 
      'referencia_1', 
      'valor_debito', 
      'valor_credito', 
      'valor_debito_credito'
    ];
    
  constructor( private  accountingService: AccountingService) { }

  ngOnInit() {
    this.dataSource.paginator = this.paginator;
    this.getItems();
  }

  getItems(): void {
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',
    });
    if(this.accountingHeader){
      this.accountingService.getAccountingItems(this.accountingHeader).subscribe(
        (response) => {
          this.dataSource.data = response;
      
        },
        (err) => {
          console.error(err);
      
        }
      );
    }
  }
}
