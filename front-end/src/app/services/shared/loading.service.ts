import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class LoadingService {

  showLoadding: BehaviorSubject<boolean> = new BehaviorSubject(false);
  showLoadding$ = this.showLoadding.asObservable();

  count: number = 0;

  constructor() { }

  show(){
    this.count ++;
    if(this.count == 1){
      this.showLoadding.next(true);
    }
  }

  hide(): void{
    this.count --;
    if(this.count == 0){
      this.showLoadding.next(false);
    }
  }

  forceHide(): void{
    this.count = 0;
    this.showLoadding.next(false);
  }
}
