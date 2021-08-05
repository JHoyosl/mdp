import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { CuadresRoutes } from './cuadres.routing';
import { FormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { RouterModule } from '@angular/router';
import { BalanceGeneralComponent } from './balance-general/balance-general.component';

@NgModule({
  imports: [
    CommonModule,
    FormsModule, 
    NgbModule,
    RouterModule.forChild(CuadresRoutes),
    
  ],
  declarations: [
    BalanceGeneralComponent
  ],
  
})
export class CuadresModule { }
