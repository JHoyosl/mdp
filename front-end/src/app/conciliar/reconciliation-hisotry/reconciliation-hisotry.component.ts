import { Component, OnInit, ViewChild } from '@angular/core';
import { MatPaginator, MatTableDataSource } from '@angular/material';
import { ReconciliationItem } from 'src/app/Interfaces/reconciliation.interface';
import { ReconciliationService } from 'src/app/services/reconciliation.service';

@Component({
  selector: 'app-reconciliation-hisotry',
  templateUrl: './reconciliation-hisotry.component.html',
  styleUrls: ['./reconciliation-hisotry.component.css']
})
export class ReconciliationHisotryComponent implements OnInit {

  @ViewChild(MatPaginator) paginator: MatPaginator;
  
  private reconciliationItems: ReconciliationItem[];
  
  dataSource = new MatTableDataSource<ReconciliationItem>();
  
  displayedColumns: string[] = [
    'name',
    'bankAccount',
    'externalDebit',
    'externalCredit',
    'localCredit',
    'localDebit',
    'Actions'
  ];
  
  constructor(private reconciliationService: ReconciliationService) { }

  ngOnInit() {
    this.getAccountProcess();
  }

  getAccountProcess(){
    this.reconciliationService.getAccountProcess().subscribe(
      (response) => {
        this.reconciliationItems = response;
        this.dataSource.data = this.reconciliationItems;
      },
      (err) => {
        console.error(err);
      }
    );
  }

}
