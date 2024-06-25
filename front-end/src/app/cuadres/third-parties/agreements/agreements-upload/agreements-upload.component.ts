import { Component, ElementRef, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ToastrService } from 'ngx-toastr';
import { Subject } from 'rxjs';
import { takeUntil } from 'rxjs/operators';
import { AgreementsService } from 'src/app/services/cuadres/agreements/agreements.service';
import * as dayjs from 'dayjs';
import { AgreementsRequestUpload } from 'src/app/Interfaces/cuadres.interface';
import Swal from 'sweetalert2';
import { AgreementsRequestService } from 'src/app/services/cuadres/agreements/agreements-request.service';

@Component({
  selector: 'app-agreements-upload',
  templateUrl: './agreements-upload.component.html',
  styleUrls: ['./agreements-upload.component.css']
})
export class AgreementsUploadComponent implements OnInit, OnDestroy {

  // @ViewChild('fileInput') fileInput: ElementRef;
  fileName = '';
  uploadForm: FormGroup;

  destroyed$: Subject<boolean> = new Subject();
  constructor(
    private toastr: ToastrService,
    private agreementsRequestService: AgreementsRequestService,
    private agreementsService: AgreementsService,
    private fb: FormBuilder,
  ) { }

  ngOnInit() {
    this.setForm();
    this.agreementsService.selectedDate$
    .pipe(takeUntil(this.destroyed$))
    .subscribe(
      (date) => {
        this.uploadForm.patchValue({date: `${date}T00:00:00`})
      }
    );
  }

  setForm(): void {
    this.uploadForm = this.fb.group({
      date: [{value: null, disabled: true}, [Validators.required]],
      file: [null, [Validators.required]]
    });
  }

  onFileChange(event: Event){
    const file = (event.target as HTMLInputElement).files[0];
    this.fileName = file.name;
    this.uploadForm.patchValue({file});
  }
  
  onSubmit(){
    if(!this.uploadForm.valid){
      this.toastr.error('Error en el formulario', 'Error');
      return;
    }
    if(!this.uploadForm.get('file').value){
      this.toastr.error('Debe seleccionar un archivo', 'Error');
      return;
    }
    
    const date = dayjs(this.uploadForm.get('date').value).format('YYYY-MM-DD');
    const uploadAgreement: AgreementsRequestUpload = {
      date,
      file: this.uploadForm.get('file').value,
    }

    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });

    this.agreementsRequestService.uploadAgreements(uploadAgreement).subscribe(
      (response) => {
        this.toastr.success('Cargue completado');
        this.uploadForm.reset();
        this.agreementsService.setSelectedIndex(0);
        this.agreementsRequestService.getAgreementsIndex().subscribe(
          (response) => {
            this.agreementsService.setAgreementList(response);
            Swal.close();
          }
        )
      },
      (err) => {
        console.error(err);
        Swal.close();
      },
    )
  }

  cancel(){
    this.uploadForm.reset();
    this.agreementsService.setSelectedIndex(0);
  }


  ngOnDestroy(): void {
    this.destroyed$.next(true);
    this.destroyed$.complete();
  }
}
