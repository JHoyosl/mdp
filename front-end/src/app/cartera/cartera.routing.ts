import { Routes } from '@angular/router';
import { CapitalComponent } from './capital/capital.component';




export const CarteraRoutes: Routes = [
    {
      path: '',
      children: [
        {
          path: 'capital',
          component: CapitalComponent,
          data: {
            title: 'Capital Cartera',
            urls: [
              { title: 'Cartera', url: '/dashboard' },
              { title: 'Capital Cartera' }
            ]
          }
        },
      ]
    }
  ];