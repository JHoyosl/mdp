import { animate, state, style, transition, trigger } from '@angular/animations';
import { Component, EventEmitter, Input, OnDestroy, OnInit, Output } from '@angular/core';
import { MatTableDataSource } from '@angular/material';
import { ReconciliationResume } from 'src/app/Interfaces/reconciliation.interface';
import { ReconciliationService } from 'src/app/services/reconciliation/reconciliation.service';
import { ReconciliationHelper } from '../../helpers/reconciliation.helpers';
import { SelectionModel } from '@angular/cdk/collections';
import Swal from 'sweetalert2';
import { ToastrService } from 'ngx-toastr';
import { ReconciliationProcessService } from 'src/app/services/reconciliation/reconciliation-process.service';
import { Router } from '@angular/router';
import { tap } from 'rxjs/operators';
import { Subscription } from 'rxjs';

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
export class AccountResumeComponent implements OnInit, OnDestroy {

  @Input() showSelect = false;
  @Input() showButtons = false;

  getAccountSub: Subscription;

  accounts: ReconciliationResume[] = [];
  showIniButton = false;

  columnsToDisplay: string[] = [
    'name',
    'bankAccount',
    'dates',
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
  
  constructor(
    private reconciliationService: ReconciliationService, 
    private toastr: ToastrService,
    private reconciliationProcess :ReconciliationProcessService,
    private router: Router
  ) { 
     this.getAccountSub = this.selection.changed.subscribe(
        el => this.reconciliationProcess.setAccounts(this.selection.selected));

  }

  ngOnInit() {
    if(this.showSelect){
      this.columnsToDisplay = ['select', ...this.columnsToDisplay];
    }
    
    this.getResume();
  }

  getResume(): void {
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });
    this.reconciliationService.getAccountResume().subscribe(
      (response) => {
        Swal.close();
        this.accounts = response;
        this.dataSource.data = this.accounts;
        if(this.accounts.length === 0){
          this.toastr.info('No hay cuentas inicializadas');
        }
      },
      (err) => {
        Swal.close();
        console.error(err);
      }
    );
  }

  getBalanceSum(item: ReconciliationResume): number {
    return ReconciliationHelper.balanceDifference(item);
  }

  toggleAll(event: Event): void {
    this.isAllSelected() ? 
      this.selection.clear() :
      this.dataSource.data.forEach( row => this.selection.select(row));
  }

  isAllSelected(): boolean {
    return this.dataSource.data.length === this.selection.selected.length;
  }

  ngOnDestroy(): void {
    this.getAccountSub.unsubscribe();
  }
  
}
