import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { SecurityRoutes } from './security.routing';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { RolesComponent } from './roles/roles.component';

@NgModule({
  declarations: [RolesComponent],
  imports: [
    CommonModule,
    RouterModule.forChild(SecurityRoutes),
    FormsModule,
    NgbModule,
  ]
})
export class SecurityModule { }
