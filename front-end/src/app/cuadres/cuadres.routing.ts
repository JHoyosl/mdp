import { Routes } from '@angular/router';
import { BalanceGeneralComponent } from './balance-general/balance-general.component';




export const CuadresRoutes: Routes = [
    {
      path: '',
      children: [
        {
          path: 'balanceGeneral',
          component: BalanceGeneralComponent,
          data: {
            title: 'Cuadres Balance General',
            urls: [
              { title: 'Dashboard', url: '/dashboard' },
              { title: 'Cuadres Balance General' }
            ]
          }
        },
      ]
    }
  ];