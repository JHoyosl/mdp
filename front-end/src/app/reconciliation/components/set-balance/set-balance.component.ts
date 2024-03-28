import { Component, Input, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { MatTableDataSource } from '@angular/material';
import { ReconciliationItem } from 'src/app/Interfaces/reconciliation.interface';

@Component({
  selector: 'app-set-balance',
  templateUrl: './set-balance.component.html',
  styleUrls: ['./set-balance.component.css']
})
export class SetBalanceComponent implements OnInit {

  @Input() 
  set accountsResume(items: ReconciliationItem[]){
    this.dataSource.data = items;
    this.setForm(items);
  }
  
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
    'localBalance'
  ];


  constructor(private fb: FormBuilder) {
    
   }

  ngOnInit() {
    console.log(this.accountsResume);
  }

  setForm(items: ReconciliationItem[]){
    
    items.forEach(element => {
      this.balanceForm.addControl(element.localAccount, this.fb.group({
        localBalance: ['', Validators.required],
        externalBalance: ['', Validators.required], 
      }));
    });

    console.log(this.balanceForm);
    
  }
}

