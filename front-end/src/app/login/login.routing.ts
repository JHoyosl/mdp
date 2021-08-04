import { Routes } from '@angular/router';
import { VerifyComponent } from './verify/verify.component';
import { LoginComponent } from './login/login.component';
import { RecoverPsswComponent } from './recover-pssw/recover-pssw.component';



export const LoginRoutes: Routes = [
    {
      path: '',
      children: [
        { path: 'login',component: LoginComponent},
        { path: 'verify/:token',component: VerifyComponent},
        { path: 'recoverPssw',component: RecoverPsswComponent},
      ]
    }
  ];