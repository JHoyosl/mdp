import { LoginModel } from "./../models/login.model";
import * as LoginActions from "./../login/actions/login.actions";
import { Injectable } from "@angular/core";
import Swal from "sweetalert2";
import { HttpParams, HttpClient, HttpHeaders } from "@angular/common/http";
import { UserModel } from "../models/user.model";
import { local } from "d3";
import { environment } from "src/environments/environment";
import { Router } from "@angular/router";
import { map } from "rxjs/operators";
import { AppState } from "../app.reducers";
import { Store } from "@ngrx/store";

@Injectable({
  providedIn: "root",
})
export class AuthServiceService {
  private key = environment.appKey;
  private client = environment.clientId;
  private url = environment.url;

  constructor(
    private http: HttpClient,
    private router: Router,
    private store: Store<AppState>
  ) {}

  recoveryPassword(email) {
    return this.http.get(`${environment.url}users/recoveryPssw/${email}`);
  }

  validateUser(user: UserModel) {
    const body = new HttpParams()
      .set("email", user.email)
      .set("password", user.password)
      .set("verification_token", user.token);

    return this.http.post(environment.url + "users/verify", body);
  }

  isTokenExpired(): boolean {
    if (parseInt(localStorage.getItem("expires_in")) > Date.now()) {
      return false;
    }

    return true;
  }

  getUser() {
    const headers = new HttpHeaders({
      "Content-Type": "application/json",
      Authorization: `Bearer ${localStorage.getItem("access_token")}`,
    });

    return this.http.post(environment.url + "users/getUserByToken", null, {
      headers,
    });
  }

  isAdmin() {
    const headers = new HttpHeaders({
      "Content-Type": "application/json",
      Authorization: `Bearer ${localStorage.getItem("access_token")}`,
    });

    return this.http
      .post(environment.url + "users/isAdmin", null, { headers })
      .pipe(
        map((response) => {
          return response["status"];
        })
      );
  }

  isAuthenticated(): boolean {
    if (localStorage.getItem("access_token")) {
      return true;
    }

    return false;
  }

  logOut() {
    localStorage.clear();
    const loginModel = new LoginModel();

    this.store.dispatch(LoginActions.logoutAction({ loginModel }));
    this.router.navigate(["/login"]);
  }

  setUserCompany(company_id) {
    const headers = new HttpHeaders({
      "Content-Type": "application/json",
      Authorization: `Bearer ${localStorage.getItem("access_token")}`,
    });

    this.http
      .get(`${this.url}users/setCurrentCompany/${company_id}`, { headers })
      .subscribe(
        (response) => {
          // this.logOut();
        },
        (err) => {
          console.error(err);
        }
      );

    this.router.navigate(["/starter"]);
  }

  getAccessToken(userLogin: UserModel) {
    const body = new HttpParams()
      .set("grant_type", "password")
      .set("client_id", this.client)
      .set("client_secret", this.key)
      .set("username", userLogin.email)
      .set("password", userLogin.password);

    console.log(body.toString());
    // Swal.fire({
    //   allowOutsideClick: false,
    //   type: "info",
    //   text: "Espere por favor",
    // });

    // Swal.showLoading();

    this.http.post(`${this.url}oauth/token`, body).subscribe(
      (response) => {
        const created_at = Date.now();
        const expires_in = response["expires_in"] * 1000 + created_at;

        localStorage.setItem("access_token", response["access_token"]);
        localStorage.setItem("createed_at", created_at.toString());
        localStorage.setItem("expires_in", expires_in.toString());
        localStorage.setItem("refresh_token", response["refresh_token"]);

        const loginModel = new LoginModel();

        loginModel.isLogin = true;
        loginModel.access_token = response["access_token"];
        loginModel.expires_in = expires_in;
        loginModel.access_token = response["access_token"];
        loginModel.refresh_token = response["refresh_token"];

        this.store.dispatch(LoginActions.loginAction({ loginModel }));

        this.setUserCompany(userLogin.current_company);
      },
      (err) => {
        console.error(err.error.error);

        Swal.fire({
          type: "info",
          text: err.error.error,
        });
      }
    );
  }
  refreshToken() {
    const body = new HttpParams()
      .set("grant_type", "refresh_token")
      .set("client_id", this.client)
      .set("client_secret", this.key)
      .set("refresh_token", localStorage.getItem("refresh_token"));

    this.http.post(`${this.url}oauth/token`, body).subscribe(
      (response) => {
        let created_at = Date.now();
        let expres_in = response["expires_in"] * 1000 + created_at;

        localStorage.setItem("access_token", response["access_token"]);
        localStorage.setItem("createed_at", created_at.toString());
        localStorage.setItem("expires_in", expres_in.toString());
        localStorage.setItem("refresh_token", response["refresh_token"]);

        this.router.navigate(["/login"]);
      },
      (err) => {
        console.error(err);
        localStorage.clear();
        this.router.navigate(["/login"]);
      }
    );
  }
}
