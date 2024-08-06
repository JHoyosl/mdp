import { Routes } from '@angular/router';
import { ConciliarProcessComponent } from './conciliar-process/conciliar-process.component';
import { TxTypesComponent } from './tx-types/tx-types.component';

import { ReconciliationProcessComponent } from './reconciliation-process/reconciliation-process.component';
import { ReconciliationComponent } from './reconciliation.component';
import { ReconciliationHisotryComponent } from './reconciliation-hisotry/reconciliation-hisotry.component';
import { BeginProcessComponent } from './components/begin-process/begin-process.component';



export const reconciliationRoutes: Routes = [
    {
      path: '',
          component: ReconciliationComponent,
          data: {
            title: 'Conciliación',
            urls: [
              { title: 'Dashboard', url: '/dashboard' },
              { title: 'Conciliación de Cuentas' }
            ]
          }
    },
    {
      path: 'initAcc',
          component: BeginProcessComponent,
          data: {
            title: 'Iniciar Cuenta',
            urls: [
              { title: 'Conciliar', url: '/conciliar' },
              { title: 'Conciliación de Cuentas' }
            ]
          }
    },
    {
      path: 'initAcc/:process',
          component: BeginProcessComponent,
          data: {
            title: 'Iniciar Cuenta',
            urls: [
              { title: 'Conciliar', url: '/conciliar' },
              { title: 'Conciliación de Cuentas' }
            ]
          }
    },
    {
      path: 'process/:process',
      component: ReconciliationProcessComponent,
      data: {
        title: 'Processo Conciliar',
        urls: [
          { title: 'Conciliación', url: '' },
          { title: 'Processo Conciliar' }
        ]
      }
    },
    {
      path: 'process',
      component: ReconciliationProcessComponent,
      data: {
        title: 'Processo Conciliar',
        urls: [
          { title: 'Conciliación', url: '' },
          { title: 'Processo Conciliar' }
        ]
      }
    },
    {
      path: 'history',
      component: ReconciliationHisotryComponent,
      data: {
        title: 'Historial'
      }
    },
    {
      path: 'conciliarProcess',
      component: ConciliarProcessComponent,
      data: {
        title: 'Proceso de Conciliación',
        urls: [
          { title: 'Dashboard', url: '/dashboard' },
          { title: 'Conciliación' }
        ]
      }
    },
    {
      path: 'conciliarTxType',
      component: TxTypesComponent,
      data: {
        title: 'Tipos de Transacción',
        urls: [
          { title: 'Dashboard', url: '/dashboard' },
          { title: 'Tx Type' }
        ]
      }
    },
  ];