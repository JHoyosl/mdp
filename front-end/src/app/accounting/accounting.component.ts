import { Component, OnInit, ViewChild } from '@angular/core';
import { NgbTabset } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-accounting',
  templateUrl: './accounting.component.html',
  styleUrls: ['./accounting.component.css']
})
export class AccountingComponent implements OnInit {

  @ViewChild('tabSet')
  private tabSet:NgbTabset;
  
  constructor() { }

  ngOnInit() {
  }

  successUpload(event: boolean){
    if(event){
      this.tabSet.select('List');
    }
  }
  
}
