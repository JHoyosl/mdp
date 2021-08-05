import { Component, OnInit, ViewChild, TemplateRef } from '@angular/core';
import { AccountModel } from '../models/account.model';
import { ApiRequestService } from '../services/api-request.service';
import { NgbTabset, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { NgForm } from '@angular/forms';
import Swal from 'sweetalert2';
import { MapFileModel } from '../models/mapFile.model';
import { ToastrService } from 'ngx-toastr';
import { CompanyModel } from '../models/company.model';



@Component({
  selector: 'app-accounts',
  templateUrl: './accounts.component.html',
  styleUrls: ['./accounts.component.css']
})
export class AccountsComponent implements OnInit {

  @ViewChild('tabSet')
  private tabSet: NgbTabset;

  @ViewChild('content3')
  private modalHtml: any;

  modalInfo = {
    'title': 'Titulo Modal',
    'body': 'body'
  };
  previewMap = new MapFileModel();

  localSelected: '';
  externalSelected: '';

  showMap = [];
  formatExternolist = [];
  formatInternolist = [];
  selectedExterno: string;
  selectedLocal: string;

  setFormatAccount = new AccountModel();
  updateDisabled = true;
  newAccount = new AccountModel();
  updateAccount = new AccountModel();
  company = new CompanyModel();

  accountsList = [];
  banksList = [];

  constructor( private apiRequest: ApiRequestService,
      private modalService: NgbModal,
      private toastr: ToastrService
      ) {


  }

  ngOnInit() {

    this.getAccounts();
    this.getbank();
    this.tabSet.activeId = 'List';
  }


  externoMapSelected( ) {

    const formData = new FormData();
    formData.set('acc_id', this.setFormatAccount.id);
    formData.set('map_id', this.setFormatAccount.map_id);

    this.apiRequest.postForm( formData, 'accounts/setMap')
      .subscribe( (response) => {

        this.toastr.success('Formato asociado', 'Correcto');
      }, (err) => {

        this.toastr.error('Se presentó un error', 'Error');
        console.log(err);
      });

  }

  getCompany(company_id) {


    this.apiRequest.getCollection('');
  }

  localMapSelected( format:any ) {

    console.log(format);
    console.log(this.setFormatAccount);

    const formData = new FormData();
    formData.set('company_id', this.setFormatAccount.companies.id);
    formData.set('map_id', format.id);

    this.apiRequest.postForm( formData, 'companies/setMap')
      .subscribe( (response) => {
        console.log(response);
        this.toastr.success('Formato asociado', 'Correcto');
      }, (err) => {

        this.toastr.error('Se presentó un error', 'Error');
        console.log(err);
      });

  }

  editAccount( account: AccountModel ) {

    this.updateAccount.setValues(account);

    this.tabSet.activeId = 'updateTab';

  }

  update( form: NgForm ) {

    if (form.invalid) {

      Swal.fire({
        type: 'error',
        text: 'Todos los campos son obligatorios',

      });
      return;
    }

    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });

    this.apiRequest.putAccount( this.updateAccount )
    .subscribe( (response) => {


      Swal.fire({
        type: 'success',
        text: 'Actulización exitosa'
      });
      this.getAccounts();
      this.tabSet.activeId = 'List';
    }, (err) => {

      console.log(err);
      Swal.close();
      Swal.fire({
        title: 'Procesando',
        imageUrl: 'assets/images/2.gif',

      });
    });

  }


  preview( format: any ) {

    this.previewMap.setUploadValues(format);


    this.modalInfo.title = 'Asociación de Campos (Mapeo)';
    this.modalService.open( this.modalHtml, { centered: true, size: 'lg' });


  }

  getFormats(bank_id: string, company_id: string ) {
    this.formatExternolist = [];
    this.formatInternolist = [];
    this.apiRequest.getCollection( `mapFiles/formatsExterno/${bank_id}`)
      .subscribe( (response) => {

        console.log(response);
        this.formatExternolist = response;
      }, (err) => {

        console.log(err);
      });

      this.apiRequest.getCollection( `mapFiles/formatsLocal/${company_id}`)
      .subscribe( (response) => {
        this.formatInternolist = response;
      }, (err) => {

        console.log(err);
      });
  }

  setFromatShow(account) {

    console.log(account);
    this.setFormatAccount.setValues(account);
    // this.selectedLocal = this.setFormatAccount.map_id;
    console.log(this.setFormatAccount);
    this.tabSet.activeId = 'format';
    this.getFormats(account.bank_id, account.company_id);

  }


  storeAccount( form: NgForm ) {

    if (form.invalid) {

      Swal.fire({
        type: 'error',
        text: 'Todos los campos son obligatorios',

      });
      return;
    }

    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });

    const formData = this.newAccount.toFormData();



    this.apiRequest.store(formData, `accounts`)
      .subscribe( (response) => {

        Swal.close();
        this.getAccounts();
        this.tabSet.activeId = 'List';
        this.newAccount = new AccountModel();

      }, (err) => {

        console.log(err);
        Swal.fire({
          title: 'Procesando',
          imageUrl: 'assets/images/2.gif',

        });
      });
  }

  getAccounts() {

    this.apiRequest.getCollection(`accounts`)
      .subscribe( (response: any) => {

        this.accountsList = response;
      });
  }

  getbank() {

    this.apiRequest.getCollection(`banks`)
      .subscribe( (response: any) => {

        this.banksList = response;
      });
  }
}
