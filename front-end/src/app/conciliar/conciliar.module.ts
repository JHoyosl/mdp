import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Routes, RouterModule } from '@angular/router';
import { ConciliarMapComponent } from './conciliar-map/conciliar-map.component';
import { FormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { ConciliarProcessComponent } from './conciliar-process/conciliar-process.component';
import { ConciliarRoutes } from "./conciliar.routing";
import { FileUploadModule } from 'ng2-file-upload/ng2-file-upload';
import { TxTypesComponent } from './tx-types/tx-types.component';
import { AccountBalanceTableComponent } from './conciliar-process/components/account-balance-table/account-balance-table.component';
import { DirectivesModule } from '../shared/directives/directives.module';
import { ComponentsModule } from '../shared/components/components.module';
import { AccountResumeComponent } from './components/account-resume/account-resume.component';
import { MatPaginatorModule, MatTableModule, MatTabsModule } from '@angular/material';
import { ConciliarComponent } from './conciliar.component';
import { InitializeAccountComponent } from './initialize-account/initialize-account.component';
import { ReconciliationHisotryComponent } from './reconciliation-hisotry/reconciliation-hisotry.component';



@NgModule({
  declarations: [
    ConciliarMapComponent,
    ConciliarProcessComponent,
    TxTypesComponent,
    AccountBalanceTableComponent,
    AccountResumeComponent,
    ConciliarComponent,
    InitializeAccountComponent,
    ReconciliationHisotryComponent,
  ],
  imports: [
    CommonModule,
    FormsModule,
    NgbModule,
    RouterModule.forChild(ConciliarRoutes),
    FileUploadModule,
    DirectivesModule,
    ComponentsModule,
    MatTabsModule,
    MatTableModule,
    MatPaginatorModule

  ]
})
export class ConciliarModule { }

