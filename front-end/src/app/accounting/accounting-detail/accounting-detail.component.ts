import { HttpParams } from '@angular/common/http';
import { Component, Input, OnInit, ViewChild } from '@angular/core';
import { MatPaginator, MatTableDataSource } from "@angular/material";
import { AccountingHeader, AccountingItem } from 'src/app/Interfaces/accounting.interface';
import { AccountingService } from 'src/app/services/accounting.service';
import Swal from 'sweetalert2';

export interface PeriodicElement {
  name: string;
  position: number;
  weight: number;
  symbol: string;
}

const ELEMENT_DATA: PeriodicElement[] = [
  {position: 1, name: 'Hydrogen', weight: 1.0079, symbol: 'H'},
  {position: 2, name: 'Helium', weight: 4.0026, symbol: 'He'},
  {position: 3, name: 'Lithium', weight: 6.941, symbol: 'Li'},
  {position: 4, name: 'Beryllium', weight: 9.0122, symbol: 'Be'},
  {position: 5, name: 'Boron', weight: 10.811, symbol: 'B'},
  {position: 6, name: 'Carbon', weight: 12.0107, symbol: 'C'},
  {position: 7, name: 'Nitrogen', weight: 14.0067, symbol: 'N'},
  {position: 8, name: 'Oxygen', weight: 15.9994, symbol: 'O'},
  {position: 9, name: 'Fluorine', weight: 18.9984, symbol: 'F'},
  {position: 10, name: 'Neon', weight: 20.1797, symbol: 'Ne'},
];

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
          Swal.close();
        },
        (err) => {
          console.error(err);
          Swal.close();
        }
      );
    }
  }
}
