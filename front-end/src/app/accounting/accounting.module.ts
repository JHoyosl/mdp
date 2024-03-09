import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AccountingComponent } from '../accounting/accounting.component';
import { RouterModule } from '@angular/router';
import { AccountingRoutes } from './accounting.routing';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { AccountingInfoComponent } from './accounting-info/accounting-info.component';
import { AccountingUploadComponent } from './accounting-upload/accounting-upload.component';
import {MatDatepickerModule} from '@angular/material/datepicker';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatNativeDateModule } from '@angular/material/core';
import { AccountingDetailComponent } from './accounting-detail/accounting-detail.component';
import {MatTableModule} from '@angular/material/table';

@NgModule({
  declarations: [AccountingComponent, AccountingInfoComponent, AccountingUploadComponent, AccountingDetailComponent],
  imports: [
    CommonModule,
    FormsModule, 
    NgbModule,
    RouterModule.forChild(AccountingRoutes),
    MatDatepickerModule,
    MatFormFieldModule,
    MatNativeDateModule,
    ReactiveFormsModule,
    MatTableModule
    
  ]
})
export class AccountingModule { }
