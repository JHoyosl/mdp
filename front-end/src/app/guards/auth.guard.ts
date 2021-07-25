import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, UrlTree, Router } from '@angular/router';
import { Observable } from 'rxjs';
import { AuthServiceService } from '../services/auth-service.service';

@Injectable({
  providedIn: 'root'
})
export class AuthGuard implements CanActivate {

  constructor( private authService: AuthServiceService, private router: Router ){}

  canActivate(): boolean{

    if(this.authService.isAuthenticated()){

      return true;
    } else {

      this.router.navigate(['/login']);
    }
    console.log("entra a can activate");
    return this.authService.isAuthenticated();

  }

  isAdmin(){
    
    return this.authService.isAdmin();
    
    
  }
  
}
