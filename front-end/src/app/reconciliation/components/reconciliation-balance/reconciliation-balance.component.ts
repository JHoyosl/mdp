import { animate, state, style, transition, trigger } from '@angular/animations';
import { Component, Input, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { DetailInfo, DetailedBalance, ReconciliationBalanceUpload, ReconciliationItem } from 'src/app/Interfaces/reconciliation.interface';
import { ReconciliationService } from 'src/app/services/reconciliation/reconciliation.service';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-reconciliation-balance',
  templateUrl: './reconciliation-balance.component.html',
  styleUrls: ['./reconciliation-balance.component.css'],
  animations: [
    trigger('detailExpand', [
      state('collapsed', style({height: '0px', minHeight: '0', display: 'none'})),
      state('expanded', style({height: '*'})),
      transition('expanded <=> collapsed', animate('225ms cubic-bezier(0.4, 0.0, 0.2, 1)')),
    ]),
  ],
})
export class ReconciliationBalanceComponent implements OnInit {

  @Input()
  set reconciliationItem(items: ReconciliationItem[]){
    this._reconciliationItems = items;
    if(items){
      this.dataSource = this.setDataSource(items);
      this.setForm(this.dataSource);
    }
  }
  
  _reconciliationItems: ReconciliationItem[];
  dataSource: DetailedBalance[] = [];
  formBalance: FormGroup = new FormGroup({});


  columns = [
    'bank',
    'accounts',
    'startDates',
    'endDates',
    'externalBalanceInput',
    'localBalanceInput',
  ];

  detailColumns = [
    'type',
    'prevBalance',
    'credit',
    'debit',
    'balanceSum',
    'newBalance',
  ];

  constructor(private  fb: FormBuilder, private reconciliationService: ReconciliationService) { }

  ngOnInit() {

  }

  setDataSource(items: ReconciliationItem[]): DetailedBalance[] {
    return items.map((info) => {
      return {
        itemId: info.id,
        bank: info.name,
        localAccount: info.localAccount,
        externalAccount: info.bankAccount,
        startDate: info.startDate,
        endDate: info.endDate,
        detailInfo: [
          {
            type: 'Externo',
            prevBalance: info.prevExternalBalance,
            credit: info.externalCredit,
            debit: info.externalDebit,
            balanceSum: this.sumBalance('external', info),
            newBalance: 0,
            difference: this.sumBalance('external', info),
          },
          {
            type: 'Local',
            prevBalance: info.prevLocalBalance,
            credit: info.localCredit,
            debit: info.localDebit,
            balanceSum: this.sumBalance('local', info),
            newBalance: 0,
            difference: this.sumBalance('local', info),
          }
        ]
      }
    });
  }

  onSubmit(){
    
    const invalidItems = this.dataSource.map((item) => 
      item.detailInfo.reduce( (acc, curr) => 
        curr.difference !== 0 ? [...acc, curr.difference] : acc ,[])
    ).filter(el => el.length > 0);
    
    if(invalidItems.length > 0 ){
      Swal.fire({title: 'Error',text: 'Las diferencias deben ser cero (0)'});
      return;
    }
    if(this.formBalance.invalid){
      return;
    }
    const process = this._reconciliationItems[0].process;
    const data = this.dataSource.map<ReconciliationBalanceUpload>((item) =>{
      return {
        id: item.itemId,
        localAccount: item.localAccount,
        externalBalance: this.getBalance('Externo', item.detailInfo),
        localBalance: this.getBalance('Local', item.detailInfo),
      }
    });
    
    this.reconciliationService.uploadBalance(process, data).subscribe(
      (response) => {
    
        // this.updateBalance.emit(true);
      },
      (err) => {
    
        console.error(err);
      }
    );

  }

  getBalance(type: string, detailInfo: DetailInfo[]){
    return detailInfo.find((detail) => detail.type === type).newBalance;
      
  }

  setForm(items: DetailedBalance[]){

    items.forEach((element: DetailedBalance) => {
      this.formBalance.addControl(element.localAccount, this.fb.group({
        localBalance: ['', [Validators.required]],
        externalBalance: ['', [Validators.required]],
      }))
    })
  }
  sumBalance(type: string, item: ReconciliationItem){
    if(type === 'external'){
      return Number(item.prevExternalBalance) + Number(item.externalCredit) - Number(item.externalDebit);
    }else{
      return Number(item.prevLocalBalance) + Number(item.localDebit) - Number(item.localCredit);
    }
  }

  difference(element: DetailInfo): number{
    const diff = Number(element.newBalance) - Number(element.prevBalance);
    return diff;
  }

  balanceChange(type:string, element: DetailedBalance){
    const { localBalance, externalBalance } = this.formBalance.get(element.localAccount).value;
    const value = type === 'Externo' ? externalBalance : localBalance;

    element.detailInfo.forEach((detail) => {
      if(detail.type === type){
        const diff = Number(detail.balanceSum) - Number(value);
        detail.newBalance = value;
        detail.difference = Number(diff.toFixed(2));
      }
    })
  }

}

