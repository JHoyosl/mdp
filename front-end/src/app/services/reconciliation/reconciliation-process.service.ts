import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { ReconciliationResume } from 'src/app/Interfaces/reconciliation.interface';

@Injectable({
  providedIn: 'root'
})
export class ReconciliationProcessService {

  private accounts: ReconciliationResume[] = [];

  private process = new BehaviorSubject<string>('');
  process$: Observable<string> = this.process.asObservable();
  

  constructor(private httpClient: HttpClient) { }

  setProcess(process:string){
    this.process.next(process);
  }

  setAccounts(accounts: ReconciliationResume[]){
    this.accounts = accounts;
  }

  get getAccounts(): ReconciliationResume[]{
    return this.accounts;
  }

  
}
