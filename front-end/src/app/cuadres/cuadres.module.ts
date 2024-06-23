import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { CuadresRoutes } from './cuadres.routing';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { RouterModule } from '@angular/router';
import { BalanceGeneralComponent } from './balance-general/balance-general.component';
import { AuxiliarContableComponent } from './auxiliar-contable/auxiliar-contable.component';
import { MatButtonModule, MatDatepickerModule, MatDialogModule, MatFormFieldModule, MatIconModule, MatInputModule, MatNativeDateModule, MatPaginatorModule, MatSelectModule, MatTableModule, MatTabsModule } from '@angular/material';
import { BalanceGeneralListComponent } from './balance-general/balance-general-list/balance-general-list.component';
import { ComponentsModule } from '../shared/components/components.module';
import { UploadFileComponent } from './components/upload-file/upload-file.component';
import { CuadresComponent } from './cuadres.component';
import { BalanceGeneralResultComponent } from './balance-general/balance-general-result/balance-general-result.component';
import { AgreementsComponent } from './third-parties/agreements/agreements.component';
import { ConfirmDialogComponent } from '../shared/components/confirm-dialog/confirm-dialog.component';
import { ThirdPartiesComponent } from './third-parties/third-parties.component';
import { AgreementsListComponent } from './third-parties/agreements/agreements-list/agreements-list.component';

@NgModule({
  declarations: [
    BalanceGeneralComponent,
    AuxiliarContableComponent,
    BalanceGeneralListComponent,
    UploadFileComponent,
    CuadresComponent,
    BalanceGeneralResultComponent,
    AgreementsComponent,
    ThirdPartiesComponent,
    AgreementsListComponent,
    
  ],
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatDatepickerModule,
    FormsModule, 
    NgbModule,
    RouterModule.forChild(CuadresRoutes),
    MatTabsModule,
    MatPaginatorModule,
    MatTableModule,
    MatTableModule,
    MatIconModule,
    MatDialogModule,
    MatFormFieldModule,
    MatInputModule,
    ComponentsModule,
    MatButtonModule,
    MatNativeDateModule,
    MatSelectModule,
  ],
  entryComponents: [
    UploadFileComponent,
    ConfirmDialogComponent,
    AgreementsComponent
  ]
})
export class CuadresModule { }