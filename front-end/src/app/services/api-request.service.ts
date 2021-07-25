import { Injectable } from '@angular/core';
import { HttpHeaders, HttpClient, HttpParams } from '@angular/common/http';
import { Router } from '@angular/router';
import { map } from 'rxjs/operators';

import Swal from 'sweetalert2';
import { UserModel } from '../models/user.model';
import { CompanyModel } from '../models/company.model';
import { AuthServiceService } from './auth-service.service';
import { BankModel } from '../models/bank.model';
import { environment } from 'src/environments/environment';
import { AccountModel } from '../models/account.model';

@Injectable({
  providedIn: 'root'
})
export class ApiRequestService {

  private key = environment.appKey;
  private client = environment.clientId;
  private url = environment.url;


  constructor( private http: HttpClient, private router: Router, private auth: AuthServiceService ) {}

  downloadFile( formData: FormData, endPoint: string){

    this.checkKeys();
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    return this.http.post(`${this.url}${endPoint}`, formData, {responseType: 'arraybuffer', headers: headers})

  }

  delete( id: number ){

    this.checkKeys();

    const headers = new HttpHeaders({
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    return this.http.delete(`${this.url}/${id}`, {headers});

  }

  getPostCollection(endPoint: string){

      this.checkKeys();

      const formData = new FormData();
      const headers = new HttpHeaders({
        'Authorization': `Bearer ${localStorage.getItem('access_token')}`
      });

      return this.http.post(`${this.url}${endPoint}`, formData, {headers} )
        .pipe( map( response => {
          return response;

        }));
  }

  checkKeys() {

    const expires_in = parseInt(localStorage.getItem('expires_in'));
    const dateLimit = Date.now() + 10000;

    if ( dateLimit >= expires_in ){

      this.auth.refreshToken();
    }

  }



  getCollection( endpoint: string ){

    this.checkKeys();
    const headers = new HttpHeaders({

      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    return this.http.get( this.url + endpoint , { headers: headers})
      .pipe( map( response => {

        // console.log(response);
        if (!response['status']){

          return response;
        }
        return response['data'];

      },  (err) => {

        console.log(err);

      }));


   }


  put( body: HttpParams, endPoint: string){

    this.checkKeys();
    const headers = new HttpHeaders({
      'Content-Type': 'application/x-www-form-urlencoded',
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    console.log(body);

    return this.http.put(`${this.url}${endPoint}`, body, {headers});

  }

  store(fomrData: FormData, endPoint){

    this.checkKeys();

    const headers = new HttpHeaders({
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    return this.http.post(`${this.url}${endPoint}`, fomrData, {headers} );
  }
   getStatesList( id: number = 47 ){

    this.checkKeys();
    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });


    let apiUrl: string = `${this.url}locations/states/${id}`;

    return this.http.get(apiUrl, { headers: headers})
      .pipe( map( response => {

        return response['data'];
      }));

   }

   getCitiesList( id: number ){

    this.checkKeys();
    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    let apiUrl: string = `${this.url}locations/cities/${id}`;

    return this.http.get(apiUrl, {headers: headers})
      .pipe( map( response => {
        return response['data'];
      }));

   }

   putbank( bankUpdate: BankModel ){
    this.checkKeys();
    const headers = new HttpHeaders({
      'Content-Type': 'application/x-www-form-urlencoded',
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    const body = new HttpParams()
        .set('cod_comp', bankUpdate.cod_comp)
        .set('nit', bankUpdate.nit)
        .set('name', bankUpdate.name)
        .set('portal', bankUpdate.portal)
        .set('currency', bankUpdate.currency);

    return this.http.put(`${this.url}banks/${bankUpdate.id}`, body, {headers});

   }

   putAccount( account: AccountModel ){

    this.checkKeys();
    const headers = new HttpHeaders({
      'Content-Type': 'application/x-www-form-urlencoded',
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    const body = new HttpParams()
        .set('bank_id', account.bank_id)
        .set('company_id', account.company_id)
        .set('acc_type', account.acc_type)
        .set('bank_account', account.bank_account)
        .set('local_account', account.local_account);

    return this.http.put(`${this.url}accounts/${account.id}`, body, {headers});

   }

   storeCompany( company: CompanyModel ){
    this.checkKeys();
    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    return this.http.post(`${this.url}companies`, JSON.stringify(company), {headers} );

   }

   storebank( bank: BankModel ){
    this.checkKeys();
    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });


    return this.http.post(`${this.url}banks`, JSON.stringify(bank), {headers} );

   }
   storeUser( user: UserModel ){

    this.checkKeys();
    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    return this.http.post(`${this.url}users`, JSON.stringify(user), {headers} );

   }

   getUserComapnies2( user_id: string){

    // return this.http.get(`${this.url}userCompanies/${userId}`)
   }

  getUserCompanies( userId: string){


    return this.http.get(`${this.url}users/${userId}/companies`);

  }

  setCurrentCompany( company_id: number, user_id: string){

    this.checkKeys();
    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });


    return this.http.get(`${this.url}/setcompany/${user_id}/${company_id}`, {headers} );

  }

   getAccesToken( userLogin: UserModel){
    this.checkKeys();
    const body = new HttpParams()
        .set('grant_type', 'password')
        .set('client_id', '5')
        .set('client_secret', 'me5U5SPcbjZdaDupYSYHWCachXaR1igXw1zWXMlZ')
        .set('username', userLogin.email)
        .set('password', userLogin.password);

        Swal.fire({
          allowOutsideClick: false,
          type: 'info',
          text: 'Espere por favor'
        });

        Swal.showLoading();

       this.http.post(`${this.url}oauth/token`, body)
          .subscribe( (response) => {

            Swal.close();
            let created_at = Date.now();
            let expres_in = (response['expires_in'] * 1000) + created_at;

            localStorage.setItem('access_token', response['access_token']);
            localStorage.setItem('createed_at', created_at.toString());
            localStorage.setItem('expires_in', expres_in.toString());
            localStorage.setItem('refresh_token', response['refresh_token']);


            this.setCurrentCompany(userLogin.id, userLogin.current_company)
              .subscribe( (response) => {

                this.router.navigate(['/main/companies']);
              }, (err) => {
                console.log(err);
              })


          }, (err) => {

            console.log(err.error.error);

            Swal.fire({
              type: 'info',
              text: err.error.error,
            });

          })
   }


  getUsersList() {

    this.checkKeys();
    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    return this.http.get('${this.url}companies/1/users', {headers});
  }

  newVerifyToken(userId: string){

    this.checkKeys();
    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    return this.http.get(`${this.url}users/newToken/${userId}`, {headers});

  }

  getRoleList() {

    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    return this.http.get(`${this.url}/user/getRoles`, {headers});
  }

  getPermissionList(rolName) {

    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    return this.http.get(`${this.url}/user/getPermission/${rolName}`, {headers});
  }

  getUserRoleList(user){

    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    return this.http.get(`${this.url}/user/getUserRoles/${user.id}`, {headers});
  }

  addRol(rolName: string){

    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    return this.http.get(`${this.url}/user/crearRol/${rolName}`, {headers});
  }

  addPemission(permissionName: string) {

    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    return this.http.get(`${this.url}/user/crearPermission/${permissionName}`, {headers});
  }

  setRolPermission(formData: FormData, endPoint: string) {

    this.checkKeys();
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    return this.http.post(`${this.url}${endPoint}`, formData, {headers} )
      .pipe( map( response => {
        return response;

      }));
  }

  revokeRolPermission(formData: FormData, endPoint: string) {

    this.checkKeys();
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    return this.http.post(`${this.url}${endPoint}`, formData, {headers} )
      .pipe( map( response => {
        return response;

      }));
  }

  postForm( formData: FormData, endPoint: string){

    this.checkKeys();
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    return this.http.post(`${this.url}${endPoint}`, formData, {headers} )
      .pipe( map( response => {
        return response;

      }));

  }

  uploadFile(formData: FormData, endPoint: string){

    this.checkKeys();
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    });

    console.log(formData.get('file'));
    console.log(formData.get('fecha'));

    // if(endPoint == 'conciliar/uploadIniFile'){
    //   return this.http.post(`${this.url}${endPoint}`,formData,{headers});
    // }


    return this.http.post(`${this.url}${endPoint}`, formData, {headers} )
      .pipe( map( response => {
        console.log(response);
        return response['data'];

      }));
  }
}
