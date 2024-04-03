import { Component, EventEmitter, Input, OnInit, Output, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { MatTable, MatTableDataSource } from '@angular/material';
import { title } from 'process';

import { ReconciliationBalanceUpload, ReconciliationItem } from 'src/app/Interfaces/reconciliation.interface';
import { ReconciliationService } from 'src/app/services/reconciliation.service';
import Swal from 'sweetalert2';
import { ReconciliationHelper } from '../../helpers/reconciliation.helpers';

@Component({
  selector: 'app-set-balance',
  templateUrl: './set-balance.component.html',
  styleUrls: ['./set-balance.component.css']
})
export class SetBalanceComponent implements OnInit {

  @Output() updateBalance = new EventEmitter
  @Input() 
  set accountsResume(items: ReconciliationItem[]){
    this._reconciliationItems = items;
    this.dataSource.data = items;
    this.setForm(items);
  }
  
  @ViewChild(MatTable) table: MatTable<any>;
  
  _reconciliationItems: ReconciliationItem[];
  balanceForm: FormGroup = new FormGroup({});

  dataSource: MatTableDataSource<ReconciliationItem> = new MatTableDataSource(null);

  columnsToDisplay: string[] = [
    'name',
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


  constructor(private fb: FormBuilder, private reconciliationService: ReconciliationService) {
    
   }

  ngOnInit() {
    console.log(this.accountsResume);
  }

  setForm(items: ReconciliationItem[]){
    
    items.forEach(element => {
      element.difference = ReconciliationHelper.balanceDifference(element);
      this.balanceForm.addControl(element.localAccount, this.fb.group({
        localBalance: [element.localBalance , Validators.required],
        externalBalance: [element.externalBalance, Validators.required], 
      }));
    });    
  }

  getBalanceSum(item: ReconciliationItem): number {
    return ReconciliationHelper.getBalanceSum(item);
  }
  balanceChange( type: string, item: ReconciliationItem): void {
    
    if(type === 'external'){
      item.externalBalance = this.balanceForm.get(item.localAccount).value.externalBalance;
    }
    if( type === 'local'){
      item.localBalance = this.balanceForm.get(item.localAccount).value.localBalance;
    }
    item.difference = ReconciliationHelper.balanceDifference(item);

  }
  
  onSubmit($event: Event): void {
    $event.stopPropagation;
    
    const invalidItems = this._reconciliationItems.reduce<ReconciliationItem[]>((acc, curr) => 
      ReconciliationHelper.balanceDifference(curr) !== 0 ? [...acc, curr ] : acc, 
      []);
    console.log(invalidItems);
    if(invalidItems.length > 0){
      Swal.fire({title: 'Error',text: 'Las diferencias deben ser cero (0)'});
      return;
    }
    
    const process = this._reconciliationItems[0].process;
    
    const data = this._reconciliationItems.map<ReconciliationBalanceUpload>( (item) => {
      return {
        id: item.id,
        localAccount: item.localAccount,
        localBalance: item.localBalance,
        externalBalance: item.externalBalance,
      };
    })
    console.log(data);
    this.reconciliationService.uploadBalance(process, data).subscribe(
      (response) => {
        
        this._reconciliationItems = response;
        this.dataSource.data = response;
        this.setForm(response);
      },
      (err) => {
        console.error(err);
      }
    );
  }

}

