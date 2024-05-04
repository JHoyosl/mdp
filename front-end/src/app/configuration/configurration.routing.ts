import { Routes } from "@angular/router";
import { FileMappingComponent } from "./file-mapping/file-mapping.component";

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
    ],
  },
]