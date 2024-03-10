import { Component, OnInit, ViewChild } from '@angular/core';
import { NgbTabset } from '@ng-bootstrap/ng-bootstrap';
import { AccountingHeader } from '../Interfaces/accounting.interface';

@Component({
  selector: 'app-accounting',
  templateUrl: './accounting.component.html',
  styleUrls: ['./accounting.component.css']
})
export class AccountingComponent implements OnInit {

  @ViewChild('tabSet')
  private tabSet:NgbTabset;
  
  selectedAccountingHeader: AccountingHeader = null;
  constructor() { }

  ngOnInit() {
  }

  successUpload(event: boolean){
    if(event){
      this.tabSet.select('List');
    }
  }
  
  updateSelectedHeader(accountingHeader: AccountingHeader): void {
    this.tabSet.select('detail');
    this.selectedAccountingHeader = accountingHeader;
  }
}
