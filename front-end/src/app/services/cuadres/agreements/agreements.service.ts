import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, Subject } from 'rxjs';
import { AgreementsHeader } from 'src/app/Interfaces/cuadres.interface';

@Injectable({
  providedIn: 'root'
})
export class AgreementsService {

  agreementsList = new BehaviorSubject<AgreementsHeader[]>(null);
  agreementsList$: Observable<AgreementsHeader[]> = this.agreementsList.asObservable();

  selectedDate = new BehaviorSubject<string>(null);
  selectedDate$ = this.selectedDate.asObservable();

  selectedIndex = new Subject<number>();
  selectedIndex$ = this.selectedIndex.asObservable();

  selectedResult = new BehaviorSubject<string>(null);
  selectedResult$ = this.selectedResult.asObservable();

  constructor() { }

  setAgreementList(list:AgreementsHeader[]):void{
    this.agreementsList.next(list);
  }

  setSelectedDate(date: string){
    this.selectedDate.next(date);
  }

  setSelectedIndex(index: number){
    this.selectedIndex.next(index);
  }

  setSelectedResult(date: string){
    this.selectedResult.next(date);
  } 
}
