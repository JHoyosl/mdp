import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import {MatTabsModule} from '@angular/material/tabs';
import { ThirdPartiesRoutes } from './third-parties.routing';
import { ThirdPartiesComponent } from './third-parties.component';
import { ThirdPartiesInfoComponent } from './third-parties-info/third-parties-info.component';
import { ThirdPartiesUploadComponent } from './third-parties-upload/third-parties-upload.component';
import { ThirdPartiesDetailsComponent } from './third-parties-details/third-parties-details.component';

@NgModule({
  declarations: [ThirdPartiesComponent, ThirdPartiesInfoComponent, ThirdPartiesUploadComponent, ThirdPartiesDetailsComponent],
  imports: [
    CommonModule,
    RouterModule.forChild( ThirdPartiesRoutes ),
    MatTabsModule
  ]
})
export class ThirdPartiesModule { }
