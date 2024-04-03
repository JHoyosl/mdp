import { Component, OnInit } from '@angular/core';
import { MatTableDataSource } from '@angular/material';
import { ReconciliationResume } from 'src/app/Interfaces/reconciliation.interface';
import { ReconciliationService } from 'src/app/services/reconciliation.service';
import { ReconciliationHelper } from '../../helpers/reconciliation.helpers';
import { SelectionModel } from '@angular/cdk/collections';

@Component({
  selector: 'app-account-resume',
  templateUrl: './account-resume.component.html',
  styleUrls: ['./account-resume.component.css']
})
export class AccountResumeComponent implements OnInit {

  accounts: ReconciliationResume[] = [];
  showIniButton = false;

  columnsToDisplay: string[] = [
    'select',
    'type',
    'name',
    'dates',
    'bankAccount',
    'externalDebit',
    'externalCredit',
    'externalBalance',
    'localCredit',
    'localDebit',
    'localBalance',
    'sum',
    'difference',
  ];
  
  dataSource = new MatTableDataSource<ReconciliationResume>(null);
  selection = new SelectionModel<ReconciliationResume>(true, []);
  
  constructor(private reconciliationService: ReconciliationService) { }

  ngOnInit() {
    this.getResume();
  }

  toggleAll(): void {

    this.isAllSelected() ? 
      this.selection.clear() :
      this.dataSource.data.forEach( row => this.selection.select(row));
  }

  getResume(): void {
    this.reconciliationService.getAccountResume().subscribe(
      (response) => {
        console.log(response);
        this.accounts = response;
        this.dataSource.data = this.accounts;
      },
      (err) => {
        console.error(err);
      }
    );
  }

  getBalanceSum(item: ReconciliationResume): number {
    return ReconciliationHelper.balanceDifference(item);
  }



  isAllSelected(): boolean {
    return this.dataSource.data.length === this.selection.selected.length;
  }
}
