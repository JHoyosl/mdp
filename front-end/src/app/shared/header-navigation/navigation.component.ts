import { Component, AfterViewInit, EventEmitter, Output, ViewChild, OnInit } from '@angular/core';
import {
  NgbModal,
  ModalDismissReasons,
  NgbPanelChangeEvent,
  NgbCarouselConfig
} from '@ng-bootstrap/ng-bootstrap';
import { PerfectScrollbarConfigInterface } from 'ngx-perfect-scrollbar';
import { AuthServiceService } from 'src/app/services/auth-service.service';
import { UserModel } from 'src/app/models/user.model';
import { ApiRequestService } from 'src/app/services/api-request.service';
import { CompanyModel } from 'src/app/models/company.model';
import { ToastrService } from 'ngx-toastr';
import { AppState } from 'src/app/app.reducers';
import { Store } from '@ngrx/store';
import * as UserActions from 'src/app/users/actions/users.actions';
declare var $: any;

@Component({
  selector: 'app-navigation',
  templateUrl: './navigation.component.html'
})
export class NavigationComponent implements AfterViewInit {

  @ViewChild('changeCenterDiv')
  private modalHtml:any;

  @Output() toggleSidebar = new EventEmitter<void>();
  
  public config: PerfectScrollbarConfigInterface = {};

  public showSearch = false;
  
  user = new UserModel();
  comnpanies:any[];
  
  slectedCompanyChange = 0;
  psswCompanyChange = "";

  modalInfo = {
    'title':'Titulo Modal',
    'body':'body'
  };
  

  constructor(private modalService: NgbModal, private auth: AuthServiceService, private apiRequest: ApiRequestService,
            private toastr: ToastrService, private store: Store<AppState>) {

      this.auth.getUser()
        .subscribe((response) => {

          if (response['status'] === true) {

            // this.user.setValues(response['data']);
            const user = new UserModel();
            user.setValues(response['data']);
            this.store.dispatch( UserActions.getUserInfoAction( { user } ) );
          }

        }, (err) => {

          console.error(err);
        });
  }

  OnInit() {

    this.store.select('user').subscribe( (user) => {

      this.user.setValues(user);

    });
  }
  // This is for Notifications
  notifications: Object[] = [
    {
      btn: 'btn-danger',
      icon: 'ti-link',
      title: 'Luanch Admin',
      subject: 'Just see the my new admin!',
      time: '9:30 AM'
    },
    {
      btn: 'btn-success',
      icon: 'ti-calendar',
      title: 'Event today',
      subject: 'Just a reminder that you have event',
      time: '9:10 AM'
    },
    {
      btn: 'btn-info',
      icon: 'ti-settings',
      title: 'Settings',
      subject: 'You can customize this template as you want',
      time: '9:08 AM'
    },
    {
      btn: 'btn-primary',
      icon: 'ti-user',
      title: 'Pavan kumar',
      subject: 'Just see the my admin!',
      time: '9:00 AM'
    }
  ];

  // This is for Mymessages
  mymessages: Object[] = [
    {
      useravatar: 'assets/images/users/1.jpg',
      status: 'online',
      from: 'Pavan kumar',
      subject: 'Just see the my admin!',
      time: '9:30 AM'
    },
    {
      useravatar: 'assets/images/users/2.jpg',
      status: 'busy',
      from: 'Sonu Nigam',
      subject: 'I have sung a song! See you at',
      time: '9:10 AM'
    },
    {
      useravatar: 'assets/images/users/2.jpg',
      status: 'away',
      from: 'Arijit Sinh',
      subject: 'I am a singer!',
      time: '9:08 AM'
    },
    {
      useravatar: 'assets/images/users/4.jpg',
      status: 'offline',
      from: 'Pavan kumar',
      subject: 'Just see the my admin!',
      time: '9:00 AM'
    }
  ];
  
  logOut(){
    
    this.auth.logOut();
    
  }
  changeCenter(){
    
    if(this.slectedCompanyChange == 0){
      
      this.toastr.warning('Debe seleccionar una empresa', 'Error');
      
      if(this.psswCompanyChange == ""){
        
        this.toastr.warning('Debe escribir una contraseña', 'Error');
      }
      
      return;
    }else{
      
      let formData = new FormData();

      formData.append('password',this.psswCompanyChange);
      formData.append('center',this.slectedCompanyChange.toString());

      this.apiRequest.postForm(formData,`users/userCompanies`)
        .subscribe((response)=>{
          
          if(response['status']){
  
            window.location.reload();
          }else{

            this.toastr.error('Contraseñ Incorrecta', 'Error');
          }
        },(err)=>{
          console.error(err);
          // this.toastr.error('Contraseñ Incorrecta', 'Error');

        })
    }
  }
  showChangeCenter(){

    let formData = new FormData();
    formData.set('center',"1");
    formData.set('pssw',"pssw");


    this.apiRequest.postForm(formData, `userCompany/getUserCompanies` )
      .subscribe((response)=>{
        this.comnpanies = response['data'];
        
      }, (err)=>{

        console.error(err);
      })


    this.modalInfo.title = 'Cambiar Centro'
    this.modalService.open( this.modalHtml, { centered: true, size: "sm" });
  }


  ngAfterViewInit() {}
}
