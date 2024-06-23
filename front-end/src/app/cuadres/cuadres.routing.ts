import { Routes } from '@angular/router';
import { BalanceGeneralComponent } from './balance-general/balance-general.component';
import { CuadresComponent } from './cuadres.component';
import { ThirdPartiesComponent } from './third-parties/third-parties.component';




export const CuadresRoutes: Routes = [
    {
      path: '',
      children: [
        {
          path: 'balanceGeneral',
          component: CuadresComponent,
          data: {
            title: 'Cuadres Operativos',
          }
        },
        {
          path: 'thirdParties',
          component: ThirdPartiesComponent,
          data: {
            title: 'Cuadres Terceros',
          }
        },
        {
          path: 'balanceGeneralOld',
          component: BalanceGeneralComponent,
          data: {
            title: 'Cuadres Operativos',
          }
        },
      ]
    }
  ];