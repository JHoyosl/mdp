import { Routes } from "@angular/router";
import { ThirdPartiesComponent } from "./third-parties.component";


export const ThirdPartiesRoutes: Routes = [
    {
      path: '',
      children: [
        {
          path: '',
          component: ThirdPartiesComponent,
          data: {
              title: 'Terceros',
              urls: [
                  {
                    title: 'Terceros', url: ''
                  }
              ],
          }
        }
      ],
    }
];