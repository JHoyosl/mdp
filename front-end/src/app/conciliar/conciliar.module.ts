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


@NgModule({
  declarations: [
    ConciliarMapComponent,
    ConciliarProcessComponent,
    TxTypesComponent,
  ],
  imports: [
    CommonModule,
    FormsModule,
    NgbModule,
    RouterModule.forChild(ConciliarRoutes),
    FileUploadModule
  ]
})
export class ConciliarModule { }

