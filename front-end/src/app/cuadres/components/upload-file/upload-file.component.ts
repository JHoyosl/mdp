import { AfterViewInit, ChangeDetectorRef, Component, ElementRef, Inject, NgZone, OnInit, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material';
import { ToastrService } from 'ngx-toastr';
import { UploadCuadreDialog, uploadCuadreRequest } from 'src/app/Interfaces/cuadres.interface';
import * as dayjs from 'dayjs';
import Swal from 'sweetalert2';
import { CuadresRequestsService } from 'src/app/services/cuadres/cuadres-requests.service';
import { GenericError } from 'src/app/Interfaces/shared.interfaces';

@Component({
  selector: 'app-upload-file',
  templateUrl: './upload-file.component.html',
  styleUrls: ['./upload-file.component.css']
})
export class UploadFileComponent implements OnInit {

  @ViewChild('fileInput') fileInput: ElementRef;
  formUpload: FormGroup;
  title = 'no title';
  fileName = '';

  constructor(
    private cuadreRequestsService: CuadresRequestsService,
    private toastr: ToastrService,
    private fb: FormBuilder,
    public dialogRef: MatDialogRef<UploadFileComponent>, 
    @Inject(MAT_DIALOG_DATA) public data: UploadCuadreDialog
  ) { 
    
    this.formUpload = this.fb.group({
      date: ['',[Validators.required]],
      file: [null,[Validators.required]]
    });
  }
 
  ngOnInit() {

  }

  onSubmit(){
    if(!this.formUpload.valid){
      this.toastr.error('Error en el formulario', 'Error');
      return;
    }
    if(!this.formUpload.get('file').value){
      this.toastr.error('Debe seleccionar un archivo', 'Error');
      return;
    }
    const date = dayjs(this.formUpload.get('date').value).format('YYYY-MM-DD');

    const data: uploadCuadreRequest = {
      date,
      file: this.formUpload.get('file').value,
      source: 'balance',
    }

    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });


    this.cuadreRequestsService.uploadCuadreInfo(data).subscribe(
      (response) => {
        Swal.close();
        this.toastr.success('Cargue exitoso', 'Correcto');
        this.close('success');
    }, (err) => {
      Swal.close();
      const error = err.error as GenericError;
      if(error.message === 'The given data was invalid.'){
        if(error['date'] !== 'undefined'){
          this.toastr.error(
            `Ya existe un balance para la fecha: ${data.date}`,
            'Invalido'
          )
        }
      }
      console.error(err.error);
    });
  }

  close(response: string = ''){
    this.formUpload.reset();
    this.dialogRef.close(response);
  }
  onFileChange(event){
    const file = (event.target as HTMLInputElement).files[0];
    this.fileName = file.name;
    this.formUpload.patchValue({ file: file});
  }

  openFileUpload(event){
    event.stopPropagation();
    this.fileInput.nativeElement.click();
  }
}
