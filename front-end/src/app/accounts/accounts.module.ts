import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { RouterModule, Routes } from '@angular/router';
import { AccountsComponent } from './accounts.component';


const routes: Routes = [
  {
    path: '',
    component: AccountsComponent
  },
];

@NgModule({
  declarations: [
    AccountsComponent
  ],
  imports: [
    CommonModule,
    FormsModule, 
    NgbModule,
    RouterModule.forChild(routes)
  ]
})
export class AccountsModule { }
