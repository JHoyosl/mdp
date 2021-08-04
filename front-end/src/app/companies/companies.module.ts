import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Routes, RouterModule } from '@angular/router';
import { CompaniesComponent } from './companies.component';
import { FormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

const routes: Routes = [
  {
    path: '',
    component: CompaniesComponent
  },
];

@NgModule({
  imports: [
    CommonModule,
    FormsModule, 
    NgbModule,
    RouterModule.forChild(routes)
  ],
  declarations: [
    CompaniesComponent,
  ],
})
export class CompaniesModule { }