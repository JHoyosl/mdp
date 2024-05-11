import { Component, OnInit } from '@angular/core';
import { zip } from 'rxjs';
import { ExternalTxType, LocalTxType, TxType } from 'src/app/Interfaces/txType.interface';
import { TxTypeService } from 'src/app/services/tx-type/tx-type.service';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-tx-types',
  templateUrl: './tx-types.component.html',
  styleUrls: ['./tx-types.component.css']
})
export class TxTypesComponent implements OnInit {

  selectedIndex = 0;
  sourceFilter: 'accounting' | 'thirdParty' = 'thirdParty';
  eTxTypeList: ExternalTxType[] = [];
  lTxTypelist: LocalTxType[] = [];

  toEdit: ExternalTxType | LocalTxType;

  constructor(private txTypeService: TxTypeService) {  }

  ngOnInit() {
    this.getTxTypeInfo();
  }

  getTxTypeInfo(){
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });
    zip(
      this.txTypeService.indexExternal(),
      this.txTypeService.indexLocal()
    ).subscribe(
      (response)=>{
        this.eTxTypeList = response[0];
        this.lTxTypelist = response[1];
      },
      (err) => console.error(err),
      () => Swal.close()
    )
    
  }

  getLocalTx(){
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });
    this.txTypeService.indexLocal().subscribe(
      (response) => {
        this.lTxTypelist = response;
        Swal.close();
      },
      (err) => {
        console.error(err)
        Swal.close();
      },
    )
  }

  getExternalTx(){
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });
    this.txTypeService.indexExternal().subscribe(
      (response) => {
        this.eTxTypeList = response;
        Swal.close()
      },
      (err) => {
        console.error(err)
        Swal.close();
      },
      
    )
  }
  
  actions(event: {source: 'local' | 'external', status: boolean}){
    if(event.source === 'local' && event.status){
      this.getLocalTx();
    }
    if(event.source === 'external' && event.status){
      this.getExternalTx();
    }
    this.selectedIndex = 0;
  }

  edit(edit:{source: TxType, txType: ExternalTxType | LocalTxType}){
    if(edit.source === 'external'){
      this.toEdit = edit.txType as ExternalTxType;
    }else{
      this.toEdit = edit.txType as LocalTxType;
    }
    this.selectedIndex = 2;
  }

  deleted(event: {source:'local' | 'external', status: boolean}){
    if(event.source == 'local' && event.status){
        this.getLocalTx();
    }

    if(event.source == 'external' && event.status){
        this.getExternalTx();
    }
  }
}
