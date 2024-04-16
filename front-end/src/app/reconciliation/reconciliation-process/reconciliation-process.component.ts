import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { ReconciliationProcessService } from 'src/app/services/reconciliation/reconciliation-process.service';
import { ReconciliationService } from 'src/app/services/reconciliation/reconciliation.service';

@Component({
  selector: 'app-reconciliation-process',
  templateUrl: './reconciliation-process.component.html',
  styleUrls: ['./reconciliation-process.component.css']
})
export class ReconciliationProcessComponent implements OnInit {

  isInitial = false;
  isLinear = false;
  process: string = null;
  constructor( 
    private activatedroute: ActivatedRoute,
    private reconciliationService: ReconciliationService,
    private reconciliationProcess: ReconciliationProcessService
  ) { 
    
  }

  ngOnInit() {
    if(this.activatedroute.snapshot.params['process']){
      console.log('get process & set accounts');
      return;
    }
    console.log(this.reconciliationProcess.getAccounts);
    if(this.reconciliationProcess.getAccounts && this.reconciliationProcess.getAccounts.length === 0){
      console.log('if not process id and not account, error and return to accresume');
      return;
    }
    
    console.log('if acc > 0 and not process, create one');

  //  this.process = this._activatedroute.snapshot.paramMap.get('id');
  //  this.getCurrentProcess(this.process);
  }

  getCurrentProcess(process){
    // if(process){
    //   this.reconciliationService.getProcessById(process).subscribe(
    //     (response) => {
    //       console.log(response);
    //     }
    //   );
    // }
  }

}
