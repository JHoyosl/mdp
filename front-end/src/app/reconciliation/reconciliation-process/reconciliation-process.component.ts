import { Component, OnDestroy, OnInit } from "@angular/core";
import { ActivatedRoute, Router } from "@angular/router";
import { Observable, Subscription } from "rxjs";
import { map } from "rxjs/operators";
import { ReconciliationItem } from "src/app/Interfaces/reconciliation.interface";
import { ReconciliationProcessService } from "src/app/services/reconciliation/reconciliation-process.service";
import { ReconciliationService } from "src/app/services/reconciliation/reconciliation.service";

@Component({
  selector: "app-reconciliation-process",
  templateUrl: "./reconciliation-process.component.html",
  styleUrls: ["./reconciliation-process.component.css"],
})
export class ReconciliationProcessComponent implements OnInit, OnDestroy {
  stepperIndex = -1;
  isInitial = false;
  isLinear = false;
  process: string = null;
  subscriptions: Subscription[] = [];
  items: ReconciliationItem[] = [];
  step$: Observable<string>;
  constructor(
    private activatedroute: ActivatedRoute,
    private reconciliationService: ReconciliationService,
    private reconciliationProcess: ReconciliationProcessService
  ) {}

  ngOnInit() {
    // if change && process then set step
    this.reconciliationProcess.step$.subscribe((step) => {
      switch (step) {
        case "SET_BALANCE":
          this.stepperIndex = 1;
          break;
        case "MANUAL":
          this.stepperIndex = 2;
          break;
        default:
          this.stepperIndex = 1;
          break;
      }
    });
    const item = this.reconciliationProcess.items$.subscribe((item) => {
      this.items = item;
    });

    const process = this.reconciliationProcess.process$.subscribe(
      (process) => process && this.setStep(process)
    );

    //if process on URL set process
    const params = this.activatedroute.snapshot.params;
    params.process
      ? this.reconciliationProcess.setProcess(params.process)
      : (this.stepperIndex = 0);

    if (this.activatedroute.snapshot.params["process"]) {
      console.error("get process & set accounts");
      return;
    }

    if (
      this.reconciliationProcess.getAccounts &&
      this.reconciliationProcess.getAccounts.length === 0
    ) {
      console.error(
        "if not process id and not account, error and return to accresume"
      );
      return;
    }

    // console.log("if acc > 0 and not process, create one");

    //  this.process = this._activatedroute.snapshot.paramMap.get('id');
    //  this.getCurrentProcess(this.process);
  }

  setCurrentProcess(process: string) {
    // if(process){
    //   this.reconciliationService.getProcessById(process).subscribe(
    //     (response) => {
    //     }
    //   );
    // }
  }

  setStep(process: string) {
    // this.items = this.reconciliationProcess.reconciliationItems;
    if (!this.items) {
      this.reconciliationService.getProcessById(process).subscribe((items) => {
        this.reconciliationProcess.setReconciliationItems(items);
      });
    }
  }

  ngOnDestroy(): void {
    this.subscriptions.forEach((sub) => sub.unsubscribe());
  }
}
