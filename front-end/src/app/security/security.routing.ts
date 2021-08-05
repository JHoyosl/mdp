import { SecurityComponent } from './security/security.component';
import { Routes } from '@angular/router';



export const SecurityRoutes: Routes = [
    {
      path: '',
      children: [
        {
          path: 'config',
          component: SecurityComponent,
          data: {
            title: 'SEGURIDAD',
            urls: [
              { title: 'Dashboard', url: '/dashboard' },
              { title: 'Mapeo de Archivos' }
            ]
          }
        },
      ]
    }
  ];