import { Component, OnInit } from '@angular/core';
import { ReconciliationModel } from 'src/app/models/reconciliation.model';
import { ReconciliationService } from 'src/app/services/reconciliation.service';

@Component({
  selector: 'app-initialize-account',
  templateUrl: './initialize-account.component.html',
  styleUrls: ['./initialize-account.component.css']
})
export class InitializeAccountComponent implements OnInit {

  constructor(private reconciliationService: ReconciliationService) { }

  ngOnInit() {

  }

  
}
