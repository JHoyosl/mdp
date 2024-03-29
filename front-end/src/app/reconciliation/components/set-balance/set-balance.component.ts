import { Component, Input, OnInit, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { MatTable, MatTableDataSource } from '@angular/material';
import { ReconciliationItem } from 'src/app/Interfaces/reconciliation.interface';

@Component({
  selector: 'app-set-balance',
  templateUrl: './set-balance.component.html',
  styleUrls: ['./set-balance.component.css']
})
export class SetBalanceComponent implements OnInit {

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
    'difference',
  ];


  constructor(private fb: FormBuilder) {
    
   }

  ngOnInit() {
    console.log(this.accountsResume);
  }

  setForm(items: ReconciliationItem[]){
    
    items.forEach(element => {
      element.difference = this.balanceDifference(element);
      this.balanceForm.addControl(element.localAccount, this.fb.group({
        localBalance: ['', Validators.required],
        externalBalance: ['', Validators.required], 
      }));
    });

    console.log(this.balanceForm);
    
  }

  balanceChange( type: string, item: ReconciliationItem): void {
    
    if(type === 'external'){
      item.externalBalance = this.balanceForm.get(item.localAccount).value.externalBalance;
    }
    if( type === 'local'){
      item.localBalance = this.balanceForm.get(item.localAccount).value.localBalance;
    }
    item.difference = this.balanceDifference(item);

  }
  
  balanceDifference(item: ReconciliationItem): number {

    const difference = Number(item.externalBalance) + 
      Number(item.localDebit) - 
      Number(item.externalCredit) + 
      Number(item.externalDebit) - 
      Number(item.localDebit) - 
      Number(item.localBalance);

    console.log(difference);
    return difference;

  }

  onSubmit($event: Event): void {
    $event.stopPropagation;
    console.log(this.balanceForm);
  }
}

