import { CurrencyPipe, DecimalPipe } from '@angular/common';
import { Directive, ElementRef, HostListener, Input, OnInit, Renderer2 } from '@angular/core';

@Directive({
  selector: '[appInputCurrency]'
})
export class InputCurrencyDirective implements OnInit
{
  
  currencyValue: string = '';
  currencyChars = new RegExp('[\$,]', 'g'); // we're going to remove commas and dots

  constructor(public el: ElementRef, public renderer: Renderer2, private decimalPipe: DecimalPipe, private currencyPipe: CurrencyPipe) {}

  ngOnInit() {
    this.format(this.el.nativeElement.value); // format any initial values
  }


  @HostListener('blur', ["$event.target.value"]) 
  onBlur(e: string) {
    this.format(e);
  };

  @HostListener('focus', ["$event.target.value"]) 
  onFocus(e: string) {
    this.unFormat(e);
  };

  unFormat(val:string){
    const numberUnFormat = val.replace(this.currencyChars, '');
    this.renderer.setProperty(this.el.nativeElement, 'value', numberUnFormat);
  }

  format(val:string) {
    // 1. test for non-number characters and replace/remove them
    const numberFormat = val.replace(this.currencyChars, '');
    // 2. replace the input value with formatted numbers
    this.renderer.setProperty(this.el.nativeElement, 'value', this.currencyPipe.transform(numberFormat));
  }

  get value(): number {

    return this.el.nativeElement.value;
  }
}
