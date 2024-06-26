import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { AccountingUploadInfo } from 'src/app/Interfaces/accounting.interface';
import { AccountingService } from 'src/app/services/accounting.service';
import Swal from 'sweetalert2';
import * as dayjs from 'dayjs'
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-accounting-upload',
  templateUrl: './accounting-upload.component.html',
  styleUrls: ['./accounting-upload.component.css']
})
export class AccountingUploadComponent implements OnInit {

  @Output() successUpload = new EventEmitter<boolean>();

  uploadForm = new FormGroup({
    startDate: new FormControl('', [Validators.required]),
    endDate: new FormControl('', [Validators.required]),
    file: new FormControl(null)
  });

  constructor(private accountingService: AccountingService, 
      private toastr: ToastrService) { }

  ngOnInit() {
  }

  onFileChange(event: Event): void {
    const file = (event.target as HTMLInputElement).files[0];
    this.uploadForm.patchValue({file: file});

  }

  onSubmit(){

    const startDate = dayjs(this.uploadForm.get('startDate').value).format('YYYY-MM-DD');
    const endDate = dayjs(this.uploadForm.get('endDate').value).format('YYYY-MM-DD');

    const uploadAccountingInfo: AccountingUploadInfo = {
      startDate,
      endDate,
      file: this.uploadForm.get('file').value,
    }



    this.accountingService.uploadAccountingInfo(uploadAccountingInfo)
      .subscribe(
        (response) => {
      
          
          this.toastr.success('Cargue exitoso', 'Correcto');
          this.successUpload.emit(true);
          
      }, (err) => {
    
        Swal.fire({
          type: 'error',
          text: err.error,
        });
        console.error(err.error);
      });

  }

}


/** 
 * Convertir formulario een interface
 * enviarlo al servicio
 * ELIMINAR
 * validar los errores
 * reiniciar formulario
 * volver al tab de listado
*/