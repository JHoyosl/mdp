import { Routes } from "@angular/router";
import { FileMappingComponent } from "./file-mapping/file-mapping.component";
import { TxTypesComponent } from "./masters/tx-types/tx-types.component";

export const ConfigurationRoutes : Routes = [
  {
    path: '',
    children: [
      {
        path: 'file-mapping',
        component: FileMappingComponent,
        data: {
          title: '',
        }
      },
      {
        path: 'masters/tx-type',
        component: TxTypesComponent,
        data: {
          title: ''
        }
      }
    ],
  },
]