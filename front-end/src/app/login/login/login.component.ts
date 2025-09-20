import { AppState } from "./../../app.reducers";
import { Component, OnInit } from "@angular/core";
import { Router } from "@angular/router";
import { ApiRequestService } from "../../services/api-request.service";
import { UserModel } from "../../models/user.model";
import { NgForm } from "@angular/forms";
import { AuthServiceService } from "../../services/auth-service.service";
import Swal from "sweetalert2";
import { Store } from "@ngrx/store";

@Component({
  selector: "app-login",
  templateUrl: "./login.component.html",
  styleUrls: ["./login.component.css"],
})
export class LoginComponent implements OnInit {
  companyList = [];
  user: UserModel;

  currentEmail = "";
  loginError = false;

  constructor(
    private auth: AuthServiceService,
    private router: Router,
    private apiRequest: ApiRequestService,
    private store: Store<AppState>
  ) {
    if (this.auth.isAuthenticated()) {
      this.router.navigate(["/login"]);
    }
  }

  ngOnInit() {
    this.user = new UserModel();

    this.user.email = "";
    // this.user.password = '123456'
  }

  getCompanies(event: any) {
    if (this.user.email.length === 0) {
      return;
    }
    if (this.currentEmail === this.user.email) {
      return;
    }
    this.apiRequest
      .getCollection(`users/getCompaniesByEmail/${this.user.email}`)
      .subscribe(
        (response) => {
          this.currentEmail = this.user.email;
          if (!(typeof response.status === "undefined")) {
            return;
          }

          this.companyList = response;
          if (this.companyList.length > 0) {
            this.user.id = this.companyList[0].pivot.user_id;
          }
        },
        (err) => {
          console.error(err);
          if (err.error.message === "No query results for model [AppUser]") {
            this.companyList = [];
          }
        }
      );
  }

  onSubmit(form: NgForm) {
    const formData = new FormData();

    if (form.valid) {
      let checker: any;

      this.auth.getAccessToken(this.user);
    }
  }
}
