import { Routes } from '@angular/router';
import { ConciliarMapComponent } from './conciliar-map/conciliar-map.component';
import { ConciliarProcessComponent } from './conciliar-process/conciliar-process.component';
import { TxTypesComponent } from './tx-types/tx-types.component';
import { ConciliarComponent } from './conciliar.component';


export const ConciliarRoutes: Routes = [
    {
      path: '',
          component: ConciliarComponent,
          data: {
            title: 'Conciliación',
            urls: [
              { title: 'Dashboard', url: '/dashboard' },
              { title: 'Conciliación de Cuentas' }
            ]
          }
    },
    {
      path: 'conciliarMap',
      component: ConciliarMapComponent,
      data: {
        title: 'Mapeo Conciliación',
        urls: [
          { title: 'Dashboard', url: '/dashboard' },
          { title: 'Mapeo de Archivos' }
        ]
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