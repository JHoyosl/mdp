import { Component, HostListener, Input, OnInit } from '@angular/core';
import { CurrencyPipe } from "@angular/common";

@Component({
  selector: 'app-input-currency',
  templateUrl: './input-currency.component.html',
  styleUrls: ['./input-currency.component.css']
})
export class InputCurrencyComponent implements OnInit {

  @Input() decimalSeparator: string = '.';
  @Input() maxDecimal: number = 2;

  
  _value: string;
  _transformValue: string;
  _numberValue: number;


  constructor(private currencyPipe: CurrencyPipe) { }


  ngOnInit() {
    this._value = '0';
  }

  keypress(event: KeyboardEvent ): void {
    event.preventDefault();
    if(this._value === '0' && event.key !== '0'){
      this._value = event.key;
      return;
    }
    if(event.key === this.decimalSeparator || !isNaN(Number(event.key))){
      this._value += event.key;
      this._numberValue = Number(this._value);
      this._transformValue = this.currencyPipe.transform(this._numberValue);
    }
    
  }



  get value(){
    return this.currencyPipe.transform(this._value);
  }


}
