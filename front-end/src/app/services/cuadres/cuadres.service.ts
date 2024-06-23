import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { BalanceList } from 'src/app/Interfaces/cuadres.interface';

@Injectable({
  providedIn: 'root'
})
export class CuadresService {

  selectedDate = new BehaviorSubject<string>(null);
  selectedDate$: Observable<string> = this.selectedDate.asObservable();
  
  balanceList = new BehaviorSubject<BalanceList[]>(null);
  balanceList$: Observable<BalanceList[]> = this.balanceList.asObservable();
  
  //balanceList: BalanceList[] | null = null;

  constructor() { }

  setSelectedDate(date: string){
    this.selectedDate.next(date);
  }

  setBalanceList(balanceList: BalanceList[]){
    this.balanceList.next(balanceList);
  }
}
