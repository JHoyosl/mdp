import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { BehaviorSubject, Observable } from 'rxjs';
import { ReconciliationItem, ReconciliationResume } from 'src/app/Interfaces/reconciliation.interface';

@Injectable({
  providedIn: 'root'
})
export class ReconciliationProcessService {

  private accounts: ReconciliationResume[] = [];

  private items = new BehaviorSubject<ReconciliationItem[]>(null);
  items$: Observable<ReconciliationItem[]> = this.items.asObservable();

  private process = new BehaviorSubject<string>('');
  process$: Observable<string> = this.process.asObservable();
  
  private step = new BehaviorSubject<string>('');
  step$: Observable<string> = this.step.asObservable();

  constructor(private router: Router) { }

  setProcess(process:string){
    const url = this.router.createUrlTree(['conciliar/process/',process]);
    this.router.navigateByUrl(url);
    this.process.next(process);
  }

  setAccounts(accounts: ReconciliationResume[]){
    this.accounts = accounts;
  }

  setReconciliationItems(items: ReconciliationItem[]){
    this.items.next(items);
    this.step.next(items[0].step);
  }

  // get reconciliationItems(): ReconciliationItem[]{
  //   return this.items;
    
  // }

  //todo: improve this
  get getAccounts(): ReconciliationResume[]{
    return this.accounts;
  }

  
}
