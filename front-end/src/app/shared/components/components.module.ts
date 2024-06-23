import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { InputCurrencyComponent } from './input-currency/input-currency.component';
import { IconButtonComponent } from './icon-button/icon-button.component';
import { MatButtonModule, MatIconModule, MatTooltipModule } from '@angular/material';
import { ConfirmDialogComponent } from './confirm-dialog/confirm-dialog.component';

@NgModule({
  declarations: [InputCurrencyComponent, IconButtonComponent, ConfirmDialogComponent],
  imports: [
    CommonModule,
    MatIconModule,
    MatButtonModule,
    MatTooltipModule
  ],
  exports: [
    InputCurrencyComponent,
    IconButtonComponent,
    ConfirmDialogComponent
  ]
})
export class ComponentsModule { }
