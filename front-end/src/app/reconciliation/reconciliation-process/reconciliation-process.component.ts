import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { ReconciliationService } from 'src/app/services/reconciliation.service';

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
    private _activatedroute: ActivatedRoute,
    private reconciliationService: ReconciliationService
  ) { }

  ngOnInit() {
   this.process = this._activatedroute.snapshot.paramMap.get('id');
   this.getCurrentProcess(this.process);
  }

  getCurrentProcess(process){
    if(process){
      this.reconciliationService.getProcessById(process).subscribe(
        (response) => {
          console.log(response);
        }
      );
    }
  }

}
