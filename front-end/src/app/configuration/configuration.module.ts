import { CUSTOM_ELEMENTS_SCHEMA, NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ConfigurationRoutes } from './configurration.routing';
import { MatButtonModule, MatCardModule, MatFormFieldModule, MatIconModule, MatInputModule, MatPaginatorModule, MatSelectModule, MatTableModule, MatTabsModule } from '@angular/material';
import { FileMappingComponent } from './file-mapping/file-mapping.component';
import { ComponentsModule } from '../shared/components/components.module';
import { AddMappingComponent } from './file-mapping/components/add-mapping/add-mapping.component';
import { ListMappingComponent } from './file-mapping/components/list-mapping/list-mapping.component';
import { ReactiveFormsModule } from '@angular/forms';


@NgModule({
  declarations: [FileMappingComponent, AddMappingComponent, ListMappingComponent],
  imports: [
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
  ],
  schemas: [CUSTOM_ELEMENTS_SCHEMA],
})
export class ConfigurationModule { }
