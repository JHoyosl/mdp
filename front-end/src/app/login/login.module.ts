import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Routes, RouterModule } from '@angular/router';
import { LoginComponent } from "./login/login.component";
import { FormsModule } from '@angular/forms';
import { RecoverPsswComponent } from './recover-pssw/recover-pssw.component';
import { LoginRoutes } from './login.routing';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { VerifyComponent } from './verify/verify.component';



@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    NgbModule,
    RouterModule.forChild(LoginRoutes),
  ],
  declarations: [
    LoginComponent,
    RecoverPsswComponent,
    VerifyComponent
  ],
})
export class LoginModule { }
