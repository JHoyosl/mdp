import { Component, OnInit, ViewChild } from '@angular/core';
import { NgbTabset, NgbAccordion } from '@ng-bootstrap/ng-bootstrap';
import { ApiRequestService } from 'src/app/services/api-request.service';
import Swal from 'sweetalert2';
import { ConciliarModel } from 'src/app/models/conciliar.model';
import { ToastrService } from 'ngx-toastr';
import { AccountModel } from 'src/app/models/account.model';

@Component({
  selector: 'app-conciliar-process',
  templateUrl: './conciliar-process.component.html',
  styleUrls: ['./conciliar-process.component.css']
})
export class ConciliarProcessComponent implements OnInit {


  @ViewChild('tabSet')
  private tabSet: NgbTabset;

  @ViewChild('acc')
  private acc: NgbAccordion;

  constructor(private apiRequest: ApiRequestService, private toastr: ToastrService ) {


  }

  currentUploadInfoAccount: AccountModel;
  isConciliarIni = true;

  procesList = [];
  conciliarAccounts = [];
  conciliarBanks = [];
  fechaCierre = '';

  externalIniArray = [];
  localIniArray = [];
  banksIniInfo = [];
  banksViewIniInfo = [];

  viewItemList = [];

  iniAcounts: ConciliarModel[] = [];

  newFIleUpload: File;

  disableInicial = false;
  hasIniDisplay = true;
  isUploadFile = true;


  ngOnInit() {

    this.isIniConciliar();

    this.getProcessList();

  }



  setViewConciliar() {

    console.log(this.viewItemList);

    console.log(this.iniAcounts);
    let currentBank = this.viewItemList[0].account.banks.name;
    let bankArray = [];

    bankArray.push(this.viewItemList[0]);

    if (this.viewItemList.length === 1) {

      const tmpArray = [];
      tmpArray['name'] = currentBank;
      tmpArray['data'] = bankArray;
      this.banksViewIniInfo.push(tmpArray);
      return;
    }

    for (let i = 1; i < this.viewItemList.length; i++) {

      if (this.viewItemList[i].account.banks.name == currentBank) {

        bankArray.push(this.viewItemList[i]);
        if (this.viewItemList.length == (i + 1)) {

          const tmpArray = [];
          tmpArray['name'] = currentBank;
          tmpArray['data'] = bankArray;

          this.banksViewIniInfo.push(tmpArray);
        }

      } else {

        const tmpArray = [];
        tmpArray['name'] = currentBank;
        tmpArray['data'] = bankArray;

        this.banksViewIniInfo.push(tmpArray);
        currentBank = this.viewItemList[i].account.banks.name;
        bankArray = [];
        bankArray.push(this.viewItemList[i]);

        if (this.viewItemList.length == (i + 1)) {

          const tmpArray = [];
          tmpArray['name'] = currentBank;
          tmpArray['data'] = bankArray;

          this.banksViewIniInfo.push(tmpArray);
        }
      }
    }




    console.log(this.banksViewIniInfo);
    this.tabSet.activeId = 'detalle';

  }

  viewConciliacion(process) {

    this.banksViewIniInfo = [];

    this.apiRequest.getCollection(`headers/getHeaderItems/${process.id}`)
      .subscribe( (response) => {
        this.viewItemList = response;
        this.setViewConciliar();
        console.log(response);
        this.tabSet.activeId = 'detalle';
      }, (err) => {

        console.log(err);
      });
  }

  getProcessList() {

    this.apiRequest.getCollection('headers')
      .subscribe( (response) => {
        this.procesList = response;
      }, (err) => {
        console.error(err);
      })
  }

  isIniConciliar() {
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });


    this.apiRequest.postForm(null, `conciliar/isIniConciliar`)
      .subscribe( (response) => {
        Swal.close();
        if (!response['status']) {

          this.tabSet.activeId = 'inicial';

        } else {

          this.disableInicial = true;
          this.tabSet.activeId = 'conciliar'
          this.getAccounts();
        }

      }, (err) => {
        Swal.close();
        console.log(err);

      });
  }

  groupBankAccount( toOrder: AccountModel[]) {

    if(toOrder.length === 0){
      return;
    }
    const orderBanks = [];
    toOrder
      .map((account) => account.bank_id)
      .reduce((acc, curr) => {
        if(!acc.includes(curr)){
          acc.push(curr);
        }
          return acc;
        }, [])
      .forEach((value) => {
          orderBanks.push(toOrder
            .filter((account)=> account.bank_id == value)
        )});

    return orderBanks;
  }

  getAccounts() {

    this.conciliarAccounts = [];
    this.apiRequest.getPostCollection(`conciliar/getCuentasToConciliar`)
      .subscribe( (response) => {

        console.log(response);
        // return;
        let tmp: any = [];
        tmp = response['data'];

        for (let i = 0; i < tmp.length; i++) {

          const tmpAccount = new AccountModel();
          const tmpConciliar = new ConciliarModel();

          tmpConciliar.setValues(tmp[i]);
          tmpAccount.conciliarInfo = tmpConciliar;

          tmpAccount.setValues(tmp[i].account);
          this.conciliarAccounts.push(tmpAccount);

        }

        this.conciliarBanks = this.groupBankAccount(this.conciliarAccounts);

      }, (err) => {

        console.log(err);

      });
  }

  hasIni() {

    this.apiRequest.getCollection('conciliar')
      .subscribe( (response) => {

        if (response.length === 0) {

          this.hasIniDisplay = false;
          this.tabSet.activeId = 'inicial';
        } else {

          this.hasIniDisplay = true;
        }
        console.log(response);
      }, (err) => {

        console.log(err);
      })


  }

  chooseUploadFile(account: AccountModel) {

    this.currentUploadInfoAccount = account;
    $('#uploadAccountFile').click();
    console.log(account);

  }

  uploadAccountFile(file: FileList) {

    console.log(file);
    console.log(this.currentUploadInfoAccount);
    const formData = this.currentUploadInfoAccount.toFormData();
    formData.append('file', file.item(0));

    console.log(formData.getAll);
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });
    this.apiRequest.uploadFile(formData, 'conciliar/uploadAccountFile')
      .subscribe((response) => {
        Swal.close();
        for ( let i = 0; i < this.conciliarBanks.length; i++) {

          for ( let j = 0; j < this.conciliarBanks[i].data.length; j++) {
            if (this.conciliarBanks[i].data[j].id === response.account_id) {
              this.conciliarBanks[i].data[j].conciliarInfo.balanceExternal = response.balance_externo;
              this.conciliarBanks[i].data[j].conciliarInfo.balanceLocal = response.balance_local;
              this.conciliarBanks[i].data[j].conciliarInfo.creditExternal = response.credit_externo;
              this.conciliarBanks[i].data[j].conciliarInfo.creditLocal = response.credit_local;
              this.conciliarBanks[i].data[j].conciliarInfo.debitExternal = response.debit_externo;
              this.conciliarBanks[i].data[j].conciliarInfo.debitLocal = response.debit_local;
            }

          }
        }
      }, (err) => {
        Swal.close();
        Swal.fire(
          'Error',
          err.error.errors.join(),
          'warning'
        )

        console.log(err);
      })
  }

  orderConsolidadoInfo() {

    console.log(this.iniAcounts);
    let currentBank = this.iniAcounts[0].bank_name;
    let bankArray = [];

    bankArray.push(this.iniAcounts[0]);

    if (this.iniAcounts.length == 1) {

      const tmpArray = [];
      tmpArray['name'] = currentBank;
      tmpArray['data'] = bankArray;
      this.banksIniInfo.push(tmpArray);

      return;
    }

    for (let i = 1; i < this.iniAcounts.length; i++) {

      if (this.iniAcounts[i].bank_name == currentBank) {

        bankArray.push(this.iniAcounts[i]);
        if (this.iniAcounts.length == (i + 1)) {

          const tmpArray = [];
          tmpArray['name'] = currentBank;
          tmpArray['data'] = bankArray;

          this.banksIniInfo.push(tmpArray);
        }

      } else {

        const tmpArray = [];
        tmpArray['name'] = currentBank;
        tmpArray['data'] = bankArray;

        this.banksIniInfo.push(tmpArray);
        currentBank = this.iniAcounts[i].bank_name;
        bankArray = [];
        bankArray.push(this.iniAcounts[i]);

        if (this.iniAcounts.length == (i + 1)) {

          const tmpArray = [];
          tmpArray['name'] = currentBank;
          tmpArray['data'] = bankArray;

          this.banksIniInfo.push(tmpArray);
        }
      }
    }
  }

  calcTotal(account: ConciliarModel) {

    account.getTotal();

    // console.log("calculando");

  }

  uploadContableFile( contableFile: File){

    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });

    const formData = new FormData();
    formData.append('file', contableFile);

    this.apiRequest.uploadFile(formData, 'conciliar/uploadConciliarContable')
      .subscribe( (response) => {
        Swal.close();
        this.getAccounts();
        console.log(response);
      }, (err) => {
        Swal.close();
        console.log(err);
      });

  }

  uploadIni( iniFile: File ) {

    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });

    const formData = new FormData();
    formData.append('file', iniFile);

    this.apiRequest.uploadFile(formData, 'conciliar/uploadIniFile')
      .subscribe( (response) => {

        Swal.close();
        this.isUploadFile = false;
        this.localIniArray = response.local;
        this.externalIniArray = response.external;

        if (this.localIniArray.length !== this.externalIniArray.length) {

          Swal.fire(
            'Error',
            'La cantidad de cuentaas externas e internas no coinciden',
            'warning'
          )
          return;
        }

        for (let i = 0; i < this.externalIniArray.length; i++) {
          const accountMatch = false;
          // console.log(this.externalIniArray);
          for (let j = 0; j < this.localIniArray.length; j++) {
            if (this.externalIniArray[i].local_account === this.localIniArray[j].local_account) {

              const accountConciliar = new ConciliarModel();
              accountConciliar.creditExternal = this.externalIniArray[i].credit;
              accountConciliar.creditLocal = this.localIniArray[j].credit;
              accountConciliar.debitExternal = this.externalIniArray[i].debit;
              accountConciliar.debitLocal = this.localIniArray[j].debit;
              accountConciliar.local_account = this.localIniArray[j].local_account;
              accountConciliar.external_account = this.externalIniArray[i].numero_cuenta;
              accountConciliar.bank_name = this.externalIniArray[i].name;
              accountConciliar.getTotal();

              // console.log(accountConciliar);
              this.iniAcounts.push(accountConciliar);


            }


          }
        }


        this.orderConsolidadoInfo();
      }, (err) => {

        Swal.close();
        Swal.fire(
          'Error',
          err.error.errors.join(),
          'warning'
        );
        console.log(err);

      });
  }

  onFileChange( file: FileList ) {

    if (file.length > 0) {
      this.uploadIni(file.item(0));
    }

  }

  onContableFileChange( file: FileList ) {

    if (file.length > 0) {
      this.uploadContableFile(file.item(0));
    }

  }

  confirmConciliar() {

    const tmpList: ConciliarModel[] = [];
    for (let i = 0; i < this.iniAcounts.length; i++) {

      this.iniAcounts[i].getTotal();

      if (!this.iniAcounts[i].isDirt) {

        tmpList.push(this.iniAcounts[i]);

      }
      // console.log(this.iniAcounts[i]);
      if (Number(this.iniAcounts[i].cuadre.toFixed(2)) != 0) {
        Swal.fire(
          'Error',
          `La cuenta: '${this.iniAcounts[i].bank_name}  - ${this.iniAcounts[i].external_account} no cuadran debidamente`,
          'error'
        )
        return;
      }


    }

    if (tmpList.length > 0) {

      let stringAccounts = '';
      for (let i = 0; i < tmpList.length; i ++) {

        stringAccounts += `${tmpList[i].bank_name} - ${tmpList[i].external_account} <br>`;
      }

      Swal.fire({
        title: '¿Desea Continuar?',
        html: `Las siguientes cuentas están cuadradas pero no fueron ingresados saldos,
                  ¿Desea igual continuar con la conciliación? <br>${stringAccounts}`,
        type: 'warning',
        showCancelButton: true,
        // confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Continuar'
      }).then((result) => {
        if (result.value) {
          this.closeIniConciliar();

        }
      });

    }

    this.closeIniConciliar();
  }

  closeIniConciliar() {

    if (this.fechaCierre === '' || this.fechaCierre == null) {

      Swal.fire(
        'Error',
        `Debe Ingresar una fecha de cierre`,
        'error'
      );

      return;
    }

    const lastDay = new Date(this.fechaCierre['year'], this.fechaCierre['month'], 0);

    const formData = new FormData();
    console.log(JSON.stringify(this.iniAcounts));
    formData.set('info', JSON.stringify(this.iniAcounts));
    formData.set('fecha_cierre', `${this.fechaCierre['year']}/${this.fechaCierre['month']}/${lastDay.getDate()}`);

    this.apiRequest.postForm(formData, `conciliar/closeIniConciliar`)
      .subscribe((response) => {
        this.getProcessList();
        this.toastr.success('Conciliación cerrada correctamente', 'Success!');
        this.getProcessList();
        this.tabSet.activeId = 'List';
      }, (err) => {

        console.log(err);
      })
  }


  cancelIniConciliar() {

    this.isUploadFile = true;
    this.externalIniArray = [];
    this.localIniArray = [];
    this.banksIniInfo = [];
    this.fechaCierre = null;
  }
}
