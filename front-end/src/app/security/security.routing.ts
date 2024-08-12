// import { SecurityComponent } from './security/security.component';
import { Routes } from '@angular/router';
import { RolesComponent } from './roles/roles.component';



export const SecurityRoutes: Routes = [
    {
      path: '',
      children: [
        {
          path: 'roles',
          component: RolesComponent,
          data: {
            title: 'Roles',
            urls: [
              { title: 'Dashboard', url: '/dashboard' },
              { title: 'Mapeo de Archivos' }
            ]
          }
        },
      ]
    }
  ];