import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, UrlTree, Router } from '@angular/router';
import { Observable } from 'rxjs';
import { AuthServiceService } from '../services/auth-service.service';

@Injectable({
  providedIn: 'root'
})
export class IsAdminGuard implements CanActivate {

  
  constructor( private authService: AuthServiceService, private router: Router ){}

  canActivate(): boolean{
    
    return true;

    // if(this.authService.isAdmin()){

    //   return true;
    // }else{

    //   return false;
    // }
    
    
  }
}
