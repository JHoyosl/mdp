import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { FullComponent } from './layouts/full/full.component';
import { BlankComponent } from './layouts/blank/blank.component';
import { AuthGuard } from './guards/auth.guard';
import { IsAdminGuard } from "./guards/is-admin.guard";

export const Approutes: Routes = [

  {
    path: '',
    component: BlankComponent,
    children: [
      { path: '', redirectTo: 'login', pathMatch: 'full' },
      {path: '',loadChildren: './login/login.module#LoginModule'},
      // {path: '',loadChildren: './login/verify/:token/verify.module#VerifyModule'},

    ]
  },
  {
    path: '',
    canActivate: [ AuthGuard ],
    component: FullComponent,
    children: [
      { path: '', redirectTo: '/users', pathMatch: 'full' },
      { path: 'dashboard', loadChildren: './dashboard/dashboard.module#DashboardModule' },
      { path: 'banks', loadChildren: './banks/banks.module#BanksModule' },
      { path: 'accounting/thirdParties', loadChildren: './third-parties/third-parties.module#ThirdPartiesModule'},
      { path: 'accounting', loadChildren: './accounting/accounting.module#AccountingModule'},
      { path: 'accounts', loadChildren: './accounts/accounts.module#AccountsModule' },
      { path: 'companies', loadChildren: './companies/companies.module#CompaniesModule' },
      { path: 'users', loadChildren: './users/users.module#UsersModule' },
      { path: 'conciliar', loadChildren: './reconciliation/reconciliation.module#ReconciliationModule' },
      { path: 'cuadres', loadChildren: './cuadres/cuadres.module#CuadresModule' },
      { path: 'cartera', loadChildren: './cartera/cartera.module#CarteraModule' },
      { path: 'security', loadChildren: './security/security.module#SecurityModule' },
      { path: 'configuration', loadChildren: './configuration/configuration.module#ConfigurationModule' },
    ]
  },
  {
    path: '**',
    redirectTo: 'dashboard'
  }
];
