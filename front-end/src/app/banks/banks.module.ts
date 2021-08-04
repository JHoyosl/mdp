import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { BanksComponent } from './banks.component';
import { Routes, RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

const routes: Routes = [
  {
    path: '',
    component: BanksComponent
  },
];

@NgModule({
  declarations: [
    BanksComponent,
  ],
  imports: [
    CommonModule,
    FormsModule, 
    NgbModule,
    RouterModule.forChild(routes)
  ]
})
export class BanksModule { }
