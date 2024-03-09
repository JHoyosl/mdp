import { Routes } from "@angular/router";
import { AccountingComponent } from "./accounting.component";


export const AccountingRoutes: Routes = [
    {
      path: '',
      children: [
        {
          path: '',
          component: AccountingComponent,
          data: {
            title: 'Contabilidad',
            urls: [
              { title: 'Contabilidad', url: '/accounting' },
              { title: 'Contabilidad' }
            ]
          }
        },
      ]
    }
  ];