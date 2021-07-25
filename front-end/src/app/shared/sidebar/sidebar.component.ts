import { Component, AfterViewInit, OnInit, ɵConsole } from '@angular/core';
import { ROUTES } from './menu-items';
import { RouteInfo } from './sidebar.metadata';
import { Router, ActivatedRoute } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ApiRequestService } from "../../services/api-request.service";
import { CompanyModel } from 'src/app/models/company.model';
declare var $: any;

@Component({
  selector: 'app-sidebar',
  templateUrl: './sidebar.component.html'
})
export class SidebarComponent implements OnInit {
  
  company = new CompanyModel();
  logoPath = "";
  showMenu = '';
  showSubMenu = '';
  public sidebarnavItems: any[];
  // this is for the open close
  addExpandClass(element: any) {
    if (element === this.showMenu) {
      this.showMenu = '0';
    } else {
      this.showMenu = element;
    }
  }
  addActiveClass(element: any) {
    if (element === this.showSubMenu) {
      this.showSubMenu = '0';
    } else {
      this.showSubMenu = element;
    }
  }

  constructor(
    private modalService: NgbModal,
    private router: Router,
    private route: ActivatedRoute,
    private apiRequest:ApiRequestService
  ) {}

  // End open close
  ngOnInit() {
    this.company.name = "SIN ASOCIAR";
    this.sidebarnavItems = ROUTES.filter(sidebarnavItem => sidebarnavItem);
    this.getMenúInfo();
    
  }

  getMenúInfo(){
    
    

    this.apiRequest.postForm(new FormData,`companies/getCompanyInfo`)
      .subscribe((response)=>{
        
        this.company.setValues(response['data']);
        this.logoPath = `http://mdp.globalsys.co/images/${this.company.id}/logo.jpg`;
        console.log(this.company);
        console.log(response);
      },(err)=>{

        console.log(err);
      })
  }
}
