import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { ToastrService } from 'ngx-toastr';
import { Bank } from 'src/app/Interfaces/bank.interface';
import { CreateExternalTxRequest, CreateLocalTxRequest } from 'src/app/Interfaces/txType.interface';
import { BankRequestsService } from 'src/app/services/bank/bank-requests.service';
import { TxTypeService } from 'src/app/services/tx-type/tx-type.service';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-add-tx-type',
  templateUrl: './add-tx-type.component.html',
  styleUrls: ['./add-tx-type.component.css']
})
export class AddTxTypeComponent implements OnInit {
  
  @Output() created = new EventEmitter<{source: 'local' | 'external', status: boolean}>();

  formTxType = new FormGroup({});
  bankList: Bank[] = [];
  constructor(
    private fb: FormBuilder, 
    private bankRequestsService: BankRequestsService,
    private txTypeService: TxTypeService,
    private toastr: ToastrService
  ) { }

  ngOnInit() {
    this.getBankList();
    this.setForm();
  }

  

  getBankList(){

    this.bankRequestsService.index().subscribe(
      (response) => {
        const sortByName = response.sort((a,b) => a.name < b.name ? -1 : 1) 
        this.bankList = sortByName
      },
      (err) => console.error(err),
      () => Swal.close()
    );
  }

  setForm(){
    this.formTxType = this.fb.group({
      source: ['', Validators.required],
      description: ['', Validators.required],
      tx: ['', Validators.required],
      reference: ['', Validators.required],
      sign: ['', Validators.required],
      type: ['', Validators.required]
    });

    this.formTxType.get('source').valueChanges.subscribe(
      (value) => {
        if(value === 'external'){
          this.formTxType.addControl('bank', new FormControl('', Validators.required));
        }else{
          this.formTxType.removeControl('bank');
        }
      }
    );
  }

  submitForm(){
    if(!this.formTxType.valid){
      return;
    }
    let request: CreateExternalTxRequest | CreateLocalTxRequest;


    //external submit
    if(this.formSoruce === 'external' ){
      request = this.getExternalForm();

      this.txTypeService.storeExternal(request as CreateExternalTxRequest).subscribe(
        (_) => {
          this.created.emit({source: 'local', status: true});
        },
        (err) => {
          console.error(err);
          this.toastr.error(err.error);
        },
        () => Swal.close(),
      );
    }
    //local submit
    if(this.formSoruce === 'internal' ){
      request = this.getLocalForm();

      this.txTypeService.storeLocal(request as CreateLocalTxRequest).subscribe(
        (response) => {
          this.created.emit({source: 'local', status: true})
        },
        (err) => {
          console.error(err);
          this.toastr.error(err.error);
        },
        () => Swal.close()
      );
    }    
  }

  getLocalForm(): CreateLocalTxRequest {
    return {
      description: this.formTxType.get('description').value,
      tx: this.formTxType.get('tx').value,
      reference: this.formTxType.get('reference').value,
      sign: this.formTxType.get('sign').value,
      type: this.formTxType.get('type').value,
    }
  }

  getExternalForm(): CreateExternalTxRequest {
    return {
      description: this.formTxType.get('description').value,
      tx: this.formTxType.get('tx').value,
      reference: this.formTxType.get('reference').value,
      sign: this.formTxType.get('sign').value,
      bankId: this.formTxType.get('bank').value,
      type: this.formTxType.get('type').value,
    }
  }

  cancel(){
    this.created.emit({source: 'local', status: false})
  }
  //GETTERS
  get formSoruce(){
    return this.formTxType.get('source').value;
  }
}
