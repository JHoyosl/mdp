import { HttpEvent, HttpHandler, HttpInterceptor, HttpRequest, HttpResponse } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { LoadingService } from '../services/shared/loading.service';
import { catchError, tap } from 'rxjs/operators';


@Injectable()
export class LoadingInterceptor implements HttpInterceptor {

  constructor(
    private loadingService: LoadingService
  ) {}

  intercept(req: HttpRequest<any>, next: HttpHandler) {
    this.loadingService.show();
    return next.handle(req).pipe(
      tap((event: HttpEvent<any>) => {
        event instanceof HttpResponse && this.loadingService.hide()
      }),
      catchError((response) => {
        this.loadingService.hide();
        return next.handle(req);
      })
    );
    
  }
}