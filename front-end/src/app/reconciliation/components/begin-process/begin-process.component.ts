import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ReconciliationIniUpload, ReconciliationItem, ReonciliationBalance } from 'src/app/Interfaces/reconciliation.interface';
import { ReconciliationService } from 'src/app/services/reconciliation.service';
import * as dayjs from 'dayjs';
import Swal from 'sweetalert2';
import { Location } from '@angular/common';
import { ActivatedRoute, Router } from '@angular/router';

@Component({
  selector: 'app-reconciliation-begin-process',
  templateUrl: './begin-process.component.html',
  styleUrls: ['./begin-process.component.css']
})
export class BeginProcessComponent implements OnInit {

  @Output() balanceSet = new EventEmitter<boolean>();
  @Output() balanceType = new EventEmitter<string>();


  accountsResume: ReconciliationItem[] = [];

  uploadFile: FormGroup;
  
  fileName = '';
  _process: string = null;
  
  constructor( 
      private reconciliaitionService: ReconciliationService, 
      private fb: FormBuilder,
      private location: Location,
      private router: Router,
      private activatedRouter: ActivatedRoute
    ){
      this.uploadFile = this.fb.group({
        date: ['', [Validators.required]],
        file: [null, [Validators.required]]
      }); 
   }

  ngOnInit() {   
    this._process = this.activatedRouter.snapshot.params['process'];
    if(this._process){
      this.getBalanceByProcess(this._process);
    }
  }

  getBalanceByProcess(process: string): void {
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });
    this.reconciliaitionService.getProcessById(process).subscribe(
      (response) => {
        Swal.close();
        this.accountsResume = response;
      },
      (err) => {
        Swal.close();
        console.error(err);
      }
    );
  }

  updateBalance(event: boolean){
    if(event){
      this.getBalanceByProcess(this._process);
    }
  }
  cancelBalance(){
    this.router.navigate([`/conciliar/history`]);
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
        this.location.go(`/conciliar/process/${response[0].process}`);
        this.accountsResume = response;
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
