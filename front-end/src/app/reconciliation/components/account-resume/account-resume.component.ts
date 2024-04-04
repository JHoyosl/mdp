import { animate, state, style, transition, trigger } from '@angular/animations';
import { Component, OnInit } from '@angular/core';
import { MatTableDataSource } from '@angular/material';
import { ReconciliationResume } from 'src/app/Interfaces/reconciliation.interface';
import { ReconciliationService } from 'src/app/services/reconciliation.service';
import { ReconciliationHelper } from '../../helpers/reconciliation.helpers';
import { SelectionModel } from '@angular/cdk/collections';

@Component({
  selector: 'app-account-resume',
  templateUrl: './account-resume.component.html',
  styleUrls: ['./account-resume.component.css'],
  animations: [
    trigger('detailExpand', [
      state('collapsed', style({height: '0px', minHeight: '0'})),
      state('expanded', style({height: '*'})),
      transition('expanded <=> collapsed', animate('225ms cubic-bezier(0.4, 0.0, 0.2, 1)')),
    ]),
  ],
})
export class AccountResumeComponent implements OnInit {

  accounts: ReconciliationResume[] = [];
  showIniButton = false;

  columnsToDisplay: string[] = [
    'select',
    'dates',
    'name',
    'bankAccount',
    'process',
    'type',
    'step',
    'status',
  ];

  subColumnsToDisplay: string[] = [
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

  toggleAll(): void {
    this.isAllSelected() ? 
      this.selection.clear() :
      this.dataSource.data.forEach( row => this.selection.select(row));
  }

  isAllSelected(): boolean {
    return this.dataSource.data.length === this.selection.selected.length;
  }
}
