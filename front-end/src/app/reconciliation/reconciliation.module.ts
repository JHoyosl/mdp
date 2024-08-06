import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Routes, RouterModule } from '@angular/router';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { ConciliarProcessComponent } from './conciliar-process/conciliar-process.component';
import { reconciliationRoutes } from "./reconciliation.routing";
import { FileUploadModule } from 'ng2-file-upload/ng2-file-upload';
import { TxTypesComponent } from './tx-types/tx-types.component';
import { AccountBalanceTableComponent } from './conciliar-process/components/account-balance-table/account-balance-table.component';
import { DirectivesModule } from '../shared/directives/directives.module';
import { ComponentsModule } from '../shared/components/components.module';
import { AccountResumeComponent } from './components/account-resume/account-resume.component';
import { MatButtonModule, MatCardModule, MatCheckboxModule, MatDatepickerModule, MatFormFieldModule, MatIconModule, MatInputModule, MatNativeDateModule, MatPaginatorModule, MatStepperModule, MatTableModule, MatTabsModule } from '@angular/material';
import { ReconciliationComponent } from './reconciliation.component';
import { ReconciliationHisotryComponent } from './reconciliation-hisotry/reconciliation-hisotry.component';
import { ReconciliationProcessComponent } from './reconciliation-process/reconciliation-process.component';
import { BeginProcessComponent } from './components/begin-process/begin-process.component';
import { SetBalanceComponent } from './components/set-balance/set-balance.component';
import { BalanceAccountsComponent } from './components/balance-accounts/balance-accounts.component';
import { ReconciliationBalanceComponent } from './components/reconciliation-balance/reconciliation-balance.component';


@NgModule({
  declarations: [
    ConciliarProcessComponent,
    TxTypesComponent,
    AccountBalanceTableComponent,
    AccountResumeComponent,
    ReconciliationComponent,
    ReconciliationHisotryComponent,
    ReconciliationProcessComponent,
    BeginProcessComponent,
    SetBalanceComponent,
    BalanceAccountsComponent,
    ReconciliationBalanceComponent,
  ],
  imports: [
    CommonModule,
    FormsModule,
    NgbModule,
    ReactiveFormsModule,
    RouterModule.forChild(reconciliationRoutes),
    FileUploadModule,
    DirectivesModule,
    ComponentsModule,
    MatTabsModule,
    MatTableModule,
    MatPaginatorModule,
    MatStepperModule,
    MatCardModule,
    MatDatepickerModule,
    MatNativeDateModule,
    MatFormFieldModule,
    MatInputModule,
    MatIconModule,
    MatButtonModule,
    MatCheckboxModule,

  ]
})
export class ReconciliationModule { }

