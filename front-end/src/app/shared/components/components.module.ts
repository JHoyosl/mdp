import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { InputCurrencyComponent } from './input-currency/input-currency.component';

@NgModule({
  declarations: [InputCurrencyComponent],
  imports: [
    CommonModule,
  ],
  exports: [
    InputCurrencyComponent
  ]
})
export class ComponentsModule { }
