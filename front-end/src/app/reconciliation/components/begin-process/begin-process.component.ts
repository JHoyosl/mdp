import { Component, Input, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ReconciliationIniUpload, ReconciliationItem, ReonciliationBalance } from 'src/app/Interfaces/reconciliation.interface';
import { ReconciliationService } from 'src/app/services/reconciliation.service';
import * as dayjs from 'dayjs';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-reconciliation-begin-process',
  templateUrl: './begin-process.component.html',
  styleUrls: ['./begin-process.component.css']
})
export class BeginProcessComponent implements OnInit {

  @Input()
  set process(process: string){
    this._process = process;
    // this.getReconciliationAccount();
  }

  accountsResume: ReconciliationItem[] = [];

  uploadFile: FormGroup;
  
  fileName = '';
  _process: string = null;
  
  constructor( 
      private reconciliaitionService: ReconciliationService, 
      private fb: FormBuilder
    ){
      this.uploadFile = this.fb.group({
        date: ['', [Validators.required]],
        file: [null, [Validators.required]]
      }); 
   }

  ngOnInit() {   
    this.getBalanceByProcess();
  }

  getBalanceByProcess(): void {
    this.reconciliaitionService.getProcessById('ETybAMTuH').subscribe(
      (response) => {
        this.accountsResume = response;
      },
      (err) => {
        console.error(err);
      }
    );
  }

  getReconciliationAccount(){
    this.reconciliaitionService.getReconciliationAccount().subscribe(
      (response) => {
        console.log(response);

      },
      (err) => {
        console.error(err);
      }
    );
  }

  onFileChange(event: Event): void {
    console.log(event);
    const file = (event.target as HTMLInputElement).files[0];
    this.fileName = file.name;
    this.uploadFile.patchValue({file: file});
  }

  onSbmit(): void {
    if(this.uploadFile.invalid){
      return;
    }
    const date = dayjs(this.uploadFile.get('date').value).format('YYYY-MM-DD');

    const data: ReconciliationIniUpload = {
      file: this.uploadFile.get('file').value,
      date
    }

    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });

    this.reconciliaitionService.uploadIni(data).subscribe(
      (response) => {
        Swal.close();
        // this.accountsResume = response;
      },
      (err) => {
        Swal.close();
        Swal.fire({
          type: 'error',
          text: err.error,
        });
        console.error(err);
      }
    );
  }

}
