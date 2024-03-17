import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { InputCurrencyComponent } from './input-currency/input-currency.component';
import { IconButtonComponent } from './icon-button/icon-button.component';
import { MatButtonModule, MatIconModule } from '@angular/material';

@NgModule({
  declarations: [InputCurrencyComponent, IconButtonComponent],
  imports: [
    CommonModule,
    MatIconModule,
    MatButtonModule
  ],
  exports: [
    InputCurrencyComponent,
    IconButtonComponent
  ]
})
export class ComponentsModule { }
