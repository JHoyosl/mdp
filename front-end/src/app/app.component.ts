import { AppState } from './app.reducers';
import { Component, OnDestroy } from '@angular/core';
import { Router, NavigationStart, NavigationEnd, NavigationCancel, NavigationError } from '@angular/router';
import { SlimLoadingBarService } from 'ng2-slim-loading-bar';
import { Store } from '@ngrx/store';
import { LoadingService } from './services/shared/loading.service';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.css']
})
export class AppComponent implements OnDestroy {
    private sub: any;

    constructor(
        private loaddingService: LoadingService,
        private slimLoader: SlimLoadingBarService, 
        private router: Router, private store: Store<AppState>
    ) {
        // Listen the navigation events to start or complete the slim bar loading
        this.sub = this.router.events.subscribe(event => {
            if (event instanceof NavigationStart) {
                this.slimLoader.start();
            } else if (event instanceof NavigationEnd ||
                event instanceof NavigationCancel ||
                event instanceof NavigationError) {
                this.slimLoader.complete();
            }
        }, (error: any) => {
            this.slimLoader.complete();
        });

        this.loaddingService.showLoadding$.subscribe(
            (response) => {
                if(response){
                    Swal.fire({
                        title: 'Procesando',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        imageUrl: 'assets/images/2.gif',
                  
                      });
                }else{
                    Swal.close();
                }
            }
        )
    }

    ngOnDestroy(): any {
        this.sub.unsubscribe();
    }
}
