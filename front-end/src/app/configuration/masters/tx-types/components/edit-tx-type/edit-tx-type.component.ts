import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { ToastrService } from 'ngx-toastr';
import { Bank } from 'src/app/Interfaces/bank.interface';
import { ExternalTxType, LocalTxType, TxType, UpdateExternalTxRequest, UpdateLocalTxRequest } from 'src/app/Interfaces/txType.interface';
import { BankRequestsService } from 'src/app/services/bank/bank-requests.service';
import { TxTypeService } from 'src/app/services/tx-type/tx-type.service';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-edit-tx-type',
  templateUrl: './edit-tx-type.component.html',
  styleUrls: ['./edit-tx-type.component.css']
})
export class EditTxTypeComponent implements OnInit {

  @Input() 
  set txType(val: ExternalTxType | LocalTxType){
    this._txType = val;
    this.isExternal = ('bank' in this._txType);
    this.setForm(this._txType);
  }
  @Output() edited = new EventEmitter<{source: TxType, status: boolean}>();
  @Output() canceled = new EventEmitter<{source: 'local' | 'external', status: boolean}>()

  _txType: ExternalTxType | LocalTxType;
  isExternal: boolean;

  formTxType = new FormGroup({});
  bankList: Bank[] = [];

  constructor(
    private fb: FormBuilder,
    private txTypeService: TxTypeService,
    private bankRequestsService: BankRequestsService,
    private toastr: ToastrService
  ) { }

  ngOnInit() {
    this.getBankList();
  }

  getBankList(){
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });
    this.bankRequestsService.index().subscribe(
      (response) => {
        const sortByName = response.sort((a,b) => a.name < b.name ? -1 : 1) 
        this.bankList = sortByName
      },
      (err) => console.error(err),
      () => Swal.close()
    );
  }

  setForm(txType: ExternalTxType | LocalTxType){
    this.formTxType = this.fb.group({
      source: [
        {
          value: this.isExternal ? 'external' : 'local', 
          disabled: true
        }, Validators.required],
      description: [txType.description, Validators.required],
      tx: [txType.tx, Validators.required],
      reference: [txType.reference, Validators.required],
      sign: [txType.sign, Validators.required],
      type: [txType.type, Validators.required]
    });

    if(this.isExternal){
      const tmpTxType = txType as ExternalTxType;
      this.formTxType.addControl('bank', new FormControl(tmpTxType.bank.id, Validators.required))
    }
  }

  submitForm(){
    if(this.formTxType.invalid){
      return;
    }

    const txTypeInfo: UpdateExternalTxRequest | UpdateLocalTxRequest = this.isExternal
      ? this.getExternalForm()
      : this.getLocalForm();

    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });
    if(this.isExternal){
      this.txTypeService.updateExternal(this._txType.id, txTypeInfo as UpdateExternalTxRequest).subscribe(
        (_) => {
          this.edited.emit({source: 'external', status: true});
          this.toastr.success('Registro Editado');
        },
        (err) => {
          console.error(err);
          this.toastr.error(err.error);
          this.edited.emit({source: 'external', status: false});
        },
        () => Swal.close()
      );
    }else{
      this.txTypeService.updateLocal(this._txType.id, txTypeInfo as UpdateLocalTxRequest).subscribe(
        (_) => {
          this.edited.emit({source: 'local', status: true});
          this.toastr.success('Registro Editado');
        },
        (err) => {
          console.error(err);
          this.toastr.error(err.error);
          this.edited.emit({source: 'local', status: false});
        },
        () => Swal.close()
      )
    }
  }

  getExternalForm(): UpdateExternalTxRequest{
    return {
      description: this.formTxType.get('description').value,
      tx: this.formTxType.get('tx').value,
      reference: this.formTxType.get('reference').value,
      sign: this.formTxType.get('sign').value,
      bankId: this.formTxType.get('bank').value,
      type: this.formTxType.get('type').value,
    }
  }

  getLocalForm(): UpdateLocalTxRequest{
    return {
      description: this.formTxType.get('description').value,
      tx: this.formTxType.get('tx').value,
      reference: this.formTxType.get('reference').value,
      sign: this.formTxType.get('sign').value,
      type: this.formTxType.get('type').value,
    }
  }

  cancel(){
    this.canceled.emit({source: 'external', status: false});
  }
}
