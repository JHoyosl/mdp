import { Component, OnInit, ViewChild } from '@angular/core';
import { ApiRequestService } from 'src/app/services/api-request.service';
import { ToastrService } from 'ngx-toastr';
import { NgbTabset } from '@ng-bootstrap/ng-bootstrap';
import Swal from 'sweetalert2';
import { BalanceConvenioModel } from 'src/app/models/balanceConvenio.model';
import { MatDialog } from '@angular/material';
import { UploadFileComponent } from '../components/upload-file/upload-file.component';

@Component({
  selector: 'app-balance-general',
  templateUrl: './balance-general.component.html',
  styleUrls: ['./balance-general.component.css']
})
export class BalanceGeneralComponent implements OnInit {

  @ViewChild('tabSet')
  private tabSet: NgbTabset;

  balanceList: any = [];
  balanceDate = '';
  showUpload = false;
  showConvenios = false;
  currentBalance:any;
  convenios = new BalanceConvenioModel();

  response = '';

  constructor(
    public dialog: MatDialog,
    private apiRequest: ApiRequestService, 
    private toastr: ToastrService
  ) { }

  ngOnInit() {
    this.tabSet.activeId = 'Proceso';
    this.getBalanceList();
    this.dateChange();
  }

  vieBalanceDetalle(item) {

    this.balanceDate = item.fecha.split(' ')[0];
    this.dateChange();
    this.tabSet.activeId = 'Proceso';


  }

  openDialog(): void {
    console.log("hola");
    const dialogRef = this.dialog.open(UploadFileComponent, {
      width: '250px'
    });

    dialogRef.afterClosed().subscribe(result => {
      console.log('The dialog was closed');
      this.response = result;
    });
  }

  getBalanceList() {

    this.apiRequest.getCollection(`balanceOperativo`)
      .subscribe((response)=>{
        this.balanceList = response;
        if(this.balanceList.length > 0) {

          let tmpFecha = this.balanceList[0].fecha;

          this.balanceDate = tmpFecha.split(' ')[0];
          this.dateChange();
        }
      }, (err)=>{

        console.error(err);
      })

    

  }

  downloadResultado(){

    const formData = new FormData();
    formData.set('fecha',this.balanceDate);

    this.apiRequest.downloadFile(formData, 'balanceGeneral/downloadConvenioResultado')
      .subscribe((response) => {
        const myBlob = new Blob([response], {type: 'application/vnd.oasis.opendocument.spreadsheet'});
        const downloadUrl = URL.createObjectURL(myBlob);
        const a = document.createElement('a');

        a.href = downloadUrl;
        a.download = `Cuadre_Balance_${this.balanceDate}.xlsx`;
        a.click();
      }, (err) => {

        console.error(err);
      });
  }


  downloadConvenio() {

    if (this.balanceDate === '') {

      this.toastr.warning('Debe seleccionar una fecha','Información');
      return;
    }

    const formData = new FormData();
    formData.set('fecha',this.balanceDate);

    this.apiRequest.downloadFile(formData, 'balanceGeneral/downloadConvenio')
      .subscribe((response) => {

        const myBlob = new Blob([response], {type: 'application/vnd.oasis.opendocument.spreadsheet'});
        const downloadUrl = URL.createObjectURL(myBlob);
        const a = document.createElement('a');

        a.href = downloadUrl;
        a.download = this.convenios.convenioHeader.file_name;
        a.click();

      }, (err) => {

        console.error(err);
      });
  }

  downloadBalance() {
    
    if(this.balanceDate == '') {

      this.toastr.warning('Debe seleccionar una fecha','Información');
      return;
    }

    let formData = new FormData();
    formData.set('fecha',this.balanceDate);

    this.apiRequest.downloadFile(formData,'balanceGeneral/downloadBalance')
      .subscribe((response)=>{
        let myBlob = new Blob([response], {type: 'application/vnd.oasis.opendocument.spreadsheet'});
        let downloadUrl = URL.createObjectURL(myBlob);
        let a = document.createElement('a');
        
        a.href = downloadUrl;
        a.download = this.convenios.balanceHeader.file_name;
        a.click();
        
      }, (err)=>{

        console.error(err);
      })
  }

  dateChange() {
    
    this.convenios = new BalanceConvenioModel();

    let formData = new FormData();

    formData.set('fecha',this.balanceDate);

    this.apiRequest.postForm(formData,'balanceGeneral/getBalance')
      .subscribe((response)=>{

        if(response['status']) {

          this.showUpload = false;
          this.showConvenios = true;
          this.convenios.setValues(response['data']);

        } else {

          this.showUpload = true;
          this.showConvenios = false;
          this.toastr.info('No existe un balance para esta fecha','Información');

        }
      }, (err)=>{

        console.error(err);
      })
    
  }
  
  convenioChange( file:FileList ) {

      if(file.length > 0) {
        
        Swal.fire({
          title: 'Procesando',
          allowOutsideClick: false,
          showConfirmButton: false,
          imageUrl: 'assets/images/2.gif',
           
        });
        
        let formData = new FormData();
        formData.append('file', file.item(0));
        formData.append('fecha', this.balanceDate);
    
        this.apiRequest.uploadFile(formData, 'balanceGeneral/uploadConvenios')
          .subscribe( (response) => {
            
            this.dateChange();
            Swal.close();
            
            
          }, (err) => {
            console.error(err);
            Swal.close();
            Swal.fire(
              'Error',
              err.error.errors.join(),
              'warning'
            )
          })
        
    }
  }

  onFileChange( file: FileList ) {
  
    if(file.length > 0) {
      this.uploadBalance(file.item(0));
    }
    
  }

  uploadBalance( balanceFile:File ) {
    
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',
       
    });
      
    let formData = new FormData();
    formData.append('file', balanceFile);
    formData.append('fecha', this.balanceDate);

    this.apiRequest.uploadFile(formData,'balanceGeneral/uploadBalance')
      .subscribe( (response)=>{
        
        Swal.close();
        this.showUpload = false;
        this.showConvenios = true;
        // this.dateChange();
        this.getBalanceList();
        
      }, (err) =>{
        
        Swal.close();
        Swal.fire(
          'Error',
          err.error.errors.join(),
          'warning'
        )
        console.error(err);
  
      })
  }
}
