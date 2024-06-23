import { CUSTOM_ELEMENTS_SCHEMA, NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ConfigurationRoutes } from './configurration.routing';
import { MatButtonModule, MatCardModule, MatFormFieldModule, MatIconModule, MatInputModule, MatPaginatorModule, MatSelectModule, MatSortModule, MatTableModule, MatTabsModule } from '@angular/material';
import { FileMappingComponent } from './file-mapping/file-mapping.component';
import { ComponentsModule } from '../shared/components/components.module';
import { AddMappingComponent } from './file-mapping/components/add-mapping/add-mapping.component';
import { ListMappingComponent } from './file-mapping/components/list-mapping/list-mapping.component';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { DetailMappingComponent } from './file-mapping/components/detail-mapping/detail-mapping.component';
import { EditMappingComponent } from './file-mapping/components/edit-mapping/edit-mapping.component';
import { TxTypesComponent } from './masters/tx-types/tx-types.component';
import { ListExternalTxTypeComponent } from './masters/tx-types/components/list-external-tx-type/list-external-tx-type.component';
import { AddTxTypeComponent } from './masters/tx-types/components/add-tx-type/add-tx-type.component';
import { ListLocalTxTypeComponent } from './masters/tx-types/components/list-local-tx-type/list-local-tx-type.component';
import { EditTxTypeComponent } from './masters/tx-types/components/edit-tx-type/edit-tx-type.component';
import { OperationalMasterComponent } from './masters/operational-master/operational-master.component';
import { OperaionalListComponent } from './masters/operational-master/operaional-list/operaional-list.component';




@NgModule({
  declarations: [
    FileMappingComponent, 
    AddMappingComponent, 
    ListMappingComponent, 
    DetailMappingComponent, 
    EditMappingComponent, TxTypesComponent, ListExternalTxTypeComponent, AddTxTypeComponent, ListLocalTxTypeComponent, EditTxTypeComponent, OperationalMasterComponent, OperaionalListComponent
  ],
  imports: [
    FormsModule,
    CommonModule,
    ReactiveFormsModule,
    ComponentsModule,
    RouterModule.forChild(ConfigurationRoutes),
    MatTabsModule,
    MatButtonModule,
    MatPaginatorModule,
    MatTableModule,
    MatIconModule,
    MatSelectModule,
    MatFormFieldModule,
    MatInputModule,
    MatCardModule,
    MatSortModule
  ],
  schemas: [CUSTOM_ELEMENTS_SCHEMA],
})
export class ConfigurationModule { }
