import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ToastrService } from 'ngx-toastr';
import { ReconciliationResume } from 'src/app/Interfaces/reconciliation.interface';
import { ReconciliationProcessService } from 'src/app/services/reconciliation/reconciliation-process.service';
import { ReconciliationService } from 'src/app/services/reconciliation/reconciliation.service';
import Swal from 'sweetalert2';
import * as dayjs from 'dayjs';
import { Router } from '@angular/router';

@Component({
  selector: 'app-balance-accounts',
  templateUrl: './balance-accounts.component.html',
  styleUrls: ['./balance-accounts.component.css']
})
export class BalanceAccountsComponent implements OnInit {

  dateForm: FormGroup;
  constructor(
    private reconciliationProcessService: ReconciliationProcessService,
    private reconciliationService: ReconciliationService,
    private fb: FormBuilder,
    private toastr: ToastrService,
    private router: Router
  ) { }

  ngOnInit() {
    this.dateForm = this.fb.group({
      date: ['', [Validators.required]]
    })
  }

  onSubmitDate(event: Event){
    event.stopPropagation();

    if(!this.dateForm.valid){
      this.toastr.error('Error', 'Fecha Inválida');
      return;
    }
    if(this.reconciliationProcessService.getAccounts.length === 0){
      this.toastr.error('Error', 'Debe Seleccionar una cuenta');
      return;
    }
    const ids = this.reconciliationProcessService.getAccounts.map((element) => element.accountId);
    const date = dayjs(this.dateForm.get('date').value).format('YYYY-MM-DD');
    
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });

    this.reconciliationService.startNewProcess(date, ids).subscribe(
      (response => {
        this.reconciliationProcessService.setReconciliationItems(response);
        this.reconciliationProcessService.setProcess(response[0].process);
        Swal.close();
      }),
      (err => {
        console.error(err);
        Swal.close();
      }) 
    );
  }


  // Requests
  

}
