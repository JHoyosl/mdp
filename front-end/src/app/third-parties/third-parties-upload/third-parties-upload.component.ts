import { Component, ElementRef, EventEmitter, Input, OnInit, Output, ViewChild } from '@angular/core';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { ThirdPartyAccount, ThirdPartyAccountInfoUpload } from 'src/app/Interfaces/thirdParties.interface';
import * as dayjs from 'dayjs';
import Swal from 'sweetalert2';
import { ThirdPartiesService } from 'src/app/services/third-parties.service';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-third-parties-upload',
  templateUrl: './third-parties-upload.component.html',
  styleUrls: ['./third-parties-upload.component.css']
})
export class ThirdPartiesUploadComponent implements OnInit {

  @Input() account: ThirdPartyAccount;
  
  @Output() successUpload = new EventEmitter<number>();
  @Output() cancelUpload = new EventEmitter<number>();

  @ViewChild('fileInput') fileInput: ElementRef;

  fileName = '';

  uploadForm = new FormGroup({
    startDate: new FormControl('', [Validators.required]),
    endDate: new FormControl('', [Validators.required]),
    file: new FormControl(null, [Validators.required])
  });

  constructor(private thirdPartiesService: ThirdPartiesService, private toastr: ToastrService) { }

  ngOnInit() {
  }
  
  onSubmit(event){
    event.preventDefault();

    if(!this.uploadForm.valid){
      this.toastr.error('Error en el formulario', 'Error');
      return;
    }
    if(!this.uploadForm.get('file').value){
      this.toastr.error('Debe seleccionar un archivo', 'Error');
      return;
    }
    const startDate = dayjs(this.uploadForm.get('startDate').value).format('YYYY-MM-DD');
    const endDate = dayjs(this.uploadForm.get('endDate').value).format('YYYY-MM-DD');

    const uploadAccountingInfo: ThirdPartyAccountInfoUpload = {
      accountId: this.account.id.toString(),
      startDate,
      endDate,
      file: this.uploadForm.get('file').value,
    }



    this.thirdPartiesService.uploadThirdPartiesInfo(uploadAccountingInfo).subscribe(
        (response) => {
      
          this.toastr.success('Cargue exitoso', 'Correcto');
          this.successUpload.emit(0);
          
      }, (err) => {
    
        console.error(err.error);
      });
  }

  openFileUpload(event){
    event.stopPropagation();
    this.fileInput.nativeElement.click();
  }

  onFileChange(event: Event): void {
    const file = (event.target as HTMLInputElement).files[0];
    this.fileName = file.name;
    this.uploadForm.patchValue({ file: file});
    
  }

  cancel(){
    this.uploadForm.reset();
  }
}
