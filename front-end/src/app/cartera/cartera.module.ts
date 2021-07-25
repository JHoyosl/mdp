import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CapitalComponent } from './capital/capital.component';
import { RouterModule, Routes } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { CarteraRoutes } from './cartera.routing';




@NgModule({
  
  imports: [
    CommonModule,
    FormsModule, 
    NgbModule,
    RouterModule.forChild(CarteraRoutes)
  ],
  declarations: [
    CapitalComponent
  ],
})
export class CarteraModule { }
